<?php

/**
 * @file
 * Contains \Drupal\test_d8\Form\TestD8Form
 */

namespace Drupal\test_d8\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Implements an test_d8 form
 */

class TestD8Form extends FormBase {

    /**
     *  {@inheritdoc}
     */
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

    /**
     *  {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = null){

        // get all questions
        $field_questions = $node->get('field_questions')->getValue();
        $ids = [];
        foreach ($field_questions as $d){
            $ids[] = $d['target_id'];
        }
        $paragraphs = Paragraph::loadMultiple($ids);

        $tmp_list = [];
        foreach ($paragraphs as $id => $para){
            $tmp_list[] = [
                'id' => $id,
                'question' => $this->paragraphGetValue($para, 'field_question'),
                'p1' => $this->paragraphGetValue($para, 'field_proposition_1'),
                'p2' => $this->paragraphGetValue($para, 'field_proposition_2'),
                'p3' => $this->paragraphGetValue($para, 'field_proposition_3'),
                'p4' => $this->paragraphGetValue($para, 'field_proposition_4'),
                'reponse' => $this->paragraphGetValue($para, 'field_reponse')
            ];
        }

        // randomize + limit to 40
        $config = $this->config('test_d8.settings');
        shuffle($tmp_list);
        $questions_list = array_slice(
            $tmp_list,
            0,
            $config->get('number_of_questions')
        );

        $session = \Drupal::service('user.private_tempstore')->get('test_d8');
        $time = \Drupal::time()->getCurrentTime();

        if ($session->get('test_d8_session') && ($session->get('test_d8_session') == $node->id())){
            $session_questions = $session->get('session_questions');
            $questions_list = $session->get('questions_list');
            $date_start = $session->get('date_start');
            $timer = $session->get('qcm_timer');
        } else {
            // storing q/a
            $session_questions = [];
            foreach ($questions_list as $d){
                $session_questions[] = [
                    'id' => $d['id'],
                    'answer_valid' => $d['reponse'],
                    'answer_user' => null,
                    'answer_num' => null,
                ];
            }

            $session->set('test_d8_session', $node->id());
            $session->set('session_questions', $session_questions);
            $session->set('questions_list', $questions_list);
            $session->set('date_start', $time);
            $session->set('qcm_timer', $time);
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
        foreach ($questions_list as $data){
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
        foreach ($questions_list as $data){
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

    /**
     *  {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state){}

    /**
     *  {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state){

        $form_data = $form_state->getValues();

        $session = \Drupal::service('user.private_tempstore')->get('test_d8');

        $session_questions = $session->get('session_questions');
        $date_start = $session->get('date_start');
        $timer = $session->get('qcm_timer');

        // determining the score
        $score = 0;
        $total = 0;
        foreach ($form_data as $field => $answer){
            if ('propositions' == substr($field, 0, 12)){
                ++$total;
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

        // insert in DB
        $time = \Drupal::time()->getCurrentTime();
        $uid = \Drupal::currentUser()->id();
        $nid = \Drupal::routeMatch()->getParameter('node')->id();
        $result = \Drupal::database()->insert('test_d8_test_result')->fields([
            'uid' => $uid,
            'nid' => $nid,
            'date_start' => $date_start,
            'date_end' => $time,
            'questions_status' => serialize($session_questions), // q/a to recall in case of interrupted test
            'score' => $score,
            'timer' => $time, // works with questions_status for countdown recording
        ])->execute();

        // destroy session
        $session->delete('test_d8_session');
        $session->delete('session_questions');
        $session->delete('questions_list');
        $session->delete('date_start');
        $session->delete('qcm_timer');

        drupal_set_message('Le test est terminé. Votre score est de '.$score.'/'.$total, 'status');
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

    public function paragraphGetValue($object, $fieldname){
        return  $object->get($fieldname)->getValue()[0]['value'];
    }


}
