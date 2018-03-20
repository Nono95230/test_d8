<?php

/**
 * @file
 * Contains \Drupal\test_d8\Form\TestDrupal8QcmForm
 */

namespace Drupal\test_d8\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Implements an test_d8 form
 */

class TestDrupal8QcmForm extends FormBase {


    protected $numberQuestions;


    public function __construct(){
        $this->numberQuestions = $this->config('test_d8.settings')->get('number_of_questions');
    }


    public function getFormId(){
        return 'testd8_form';
    }


    /**
    * Returns a page title.
    */
    public function getTitle(NodeInterface $node = null) {
        return $this->t('Test Drupal 8 : @name', array(
          '@name' => $node->getTitle()
        ));
    }


    public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = null){


        $nodeId = $node->id();
        $sessionTestD8 = \Drupal::service('user.private_tempstore')->get('test_d8');

        // isset Test D8 Session
        $findSessionTestD8 = ( $sessionTestD8->get('test_d8_session') == $nodeId ) ? true : false ;

        if ($findSessionTestD8){
            // get the 40 questions in the session
            $questionsQcmList = $sessionTestD8->get('questions_list');
        } else {

            // get all questions id
            $questionIds = $this->getAllQuestionsId($node);

            // load all questions
            $questions = Paragraph::loadMultiple($questionIds);

            // get only 40 questions randomly formated
            $questionsQcmList = $this->getCurrentQcmQuestions($questions, $this->numberQuestions);

            // storing q/a
            $sessionQuestionsData = $this->getSessionQuestionsData($questionsQcmList);

            $time = \Drupal::time()->getCurrentTime();
            // Set the session Test D8
            $this->setSessionTestD8($sessionTestD8, $nodeId, $sessionQuestionsData, $questionsQcmList, $time);
        }

        // nav mini-cercles
        $form['navisual'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['clearfix'],
                'id' => 'test_d8-navisual',
            ],
        ];
        $i = 0;
        foreach ($questionsQcmList as $data){
            $form['navisual']['circle'.$i] = [
                '#type' => 'html_tag',
                '#tag' => 'span',
                '#attributes' => [
                    'class' => ['test_d8-navisual-item'],
                    'data-qid' => $data['id'],
                    'data-pos' => $i,
                ],
                '#value' => ($i + 1),
            ];
            $i++;
        }

        // Q&A
        $i = 0;
        foreach ($questionsQcmList as $data){
            ++$i;

            $form['propositions'.$data['id']] = [
                '#type'     => 'radios',
                '#title'    => $this->t('Question @num', array('@num' => $i)),
                '#markup'   => '<div class="test_d8-question-text">'.$data['question'].'</div>',
                '#options'  => [
                    'p1' => $data['p1'],
                    'p2' => $data['p2'],
                    'p3' => $data['p3'],
                    'p4' => $data['p4'],
                ],
                '#prefix' => '<div class="test_d8-question'. ($i > 1 ? ' test_d8-hidden' : '') .'" id="test_d8-question'.$data['id'].'">',
                '#suffix' =>'</div>',
            ];
        }

        // nav
        $form['previous'] = [
            '#type' => 'button',
            '#value' => '◀',
            '#title' => $this->t('Previous question'),
            '#attributes' => ['title' => $this->t('Previous question')],
            '#id' => 'test_d8-question-prev',
            '#prefix' => '<div id="test_d8-nav">',
        ];
        $form['current_question'] = [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#prefix' => '<span id="test_d8-question-curr">',
            '#suffix' => '</span>',
            '#value' => null,
        ];
        $form['next'] = [
            '#type' => 'button',
            '#value' => '▶',
            '#title' => $this->t('Next question'),
            '#attributes' => ['title' => $this->t('Next question')],
            '#id' => 'test_d8-question-next',
            '#suffix' => '</div>',
        ];
        $form['validation'] = [
            '#type' => 'submit',
            '#value' => t('Validate test'),
            '#id' => 'test_d8-submit',
        ];

        return $form;
    }


    public function getAllQuestionsId($node){

        $field_questions = $node->get('field_questions')->getValue();
        $ids = [];
        foreach ($field_questions as $d){
            $ids[] = $d['target_id'];
        }
        return $ids;
        
    }


    public function getCurrentQcmQuestions($questions, $numberQuestions){

        $tmpList = [];
        foreach ($questions as $id => $para){
            $tmpList[] = [
                'id' => $id,
                'question' => $this->paragraphGetValue($para, 'field_question'),
                'p1' => $this->paragraphGetValue($para, 'field_proposition_1'),
                'p2' => $this->paragraphGetValue($para, 'field_proposition_2'),
                'p3' => $this->paragraphGetValue($para, 'field_proposition_3'),
                'p4' => $this->paragraphGetValue($para, 'field_proposition_4'),
                'reponse' => $this->paragraphGetValue($para, 'field_reponse')
            ];
        }

        shuffle($tmpList);

        $questionsList = array_slice(
            $tmpList,
            0,
            $numberQuestions
        );

        return $questionsList;
        
    }


    public function paragraphGetValue($object, $fieldname){
        return  $object->get($fieldname)->getValue()[0]['value'];
    }


    public function getSessionQuestionsData($questionsList){

        $sessionQuestions = [];
        foreach ($questionsList as $d){
            $sessionQuestions[] = [
                'id' => $d['id'],
                'answer_valid' => $d['reponse'],
                'answer_user' => null,
                'answer_num' => null,
            ];
        }
        return $sessionQuestions;
    }


    public function setSessionTestD8($session, $nodeId, $sessionQuestionsData, $questionsQcmList, $time){

        $session->set('test_d8_session', $nodeId);
        $session->set('session_questions', $sessionQuestionsData);
        $session->set('questions_list', $questionsQcmList);
        $session->set('date_start', $time);
        $session->set('qcm_timer', $time);

        return $session;
    }


    public function validateForm(array &$form, FormStateInterface $form_state){}


    public function submitForm(array &$form, FormStateInterface $form_state){

        $form_data          = $form_state->getValues();
        $session            = \Drupal::service('user.private_tempstore')->get('test_d8');
        $session_questions  = $session->get('session_questions');
        $date_start         = $session->get('date_start');
        $time               = \Drupal::time()->getCurrentTime();
        $uid                = \Drupal::currentUser()->id();
        $nid                = \Drupal::routeMatch()->getParameter('node')->id();


        $scoreResult        = $this->getScoreResult($form_data, $session_questions);

        // Insert in the DB
        $this->setData($uid, $nid, $date_start, $time, $session_questions, $scoreResult);

        // Destroy session
        $this->deleteSessionTestDrupal8($session);

        // Display the message
        $message = 'Le test est terminé. Votre score est de ' . $scoreResult . '/' . $this->numberQuestions;
        \Drupal::messenger()->addStatus($message);

        // Redirection on the dashboard page
        $form_state->setRedirect(
            'user.dashboard.tests',
            array(
                'user' => $uid
                ),
            array(
                'query' => array(
                    'tab' => $nid
                )
            )
        );

    }


    public function getScoreResult($form_data, $session_questions){
        // determining the score
        $score = 0;
        foreach ($form_data as $field => $answer){
            if ('propositions' == substr($field, 0, 12)){
                $id = substr($field, 12);
                $answer = substr($answer, 1);

                foreach ($session_questions as $d){
                    if ($d['id'] == $id){
                        if ($d['answer_valid'] == $answer){
                            ++$score;
                        }
                        break;
                    }
                }
            }
        }
        return $score;

    }


    public function setData($uid, $nid, $date, $time, $session, $result){

        return \Drupal::database()->insert('test_d8_test_result')->fields([
            'uid' => $uid,
            'nid' => $nid,
            'date_start' => $date,
            'date_end' => $time,
            'questions_status' => serialize($session), // q/a to recall in case of interrupted test
            'score' => $result,
            'timer' => $time
        ])->execute();

    }


    public function deleteSessionTestDrupal8($session){

        $session->delete('test_d8_session');
        $session->delete('session_questions');
        $session->delete('questions_list');
        $session->delete('date_start');
        $session->delete('qcm_timer');
        return $session;

    }


}
