<?php

namespace Drupal\test_d8\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestD8FormController extends ControllerBase {

	protected $session;

	// Pass the dependency to the object constructor
	public function __construct(PrivateTempStoreFactory $session){
		$this->session = $session->get('test_d8');
	}

	// Uses Symfony's ContainerInterface to declare dependency to be passed to constructor
	public static function create(ContainerInterface $container){
		return new static(
      $container->get('user.private_tempstore')
		);
	}

	public function content(NodeInterface $node = null){
    return ['form' => \Drupal::formBuilder()->getForm('Drupal\test_d8\Form\TestD8Form', $node)];
	}

  public function updateSession() {
  	$qid = \Drupal::request()->request->get('qid');
  	$answer = \Drupal::request()->request->get('answer');

    if ($this->session->get('session_questions')){
      $session_questions = $this->session->get('session_questions');
      foreach ($session_questions as $key => $value){
      	if ($value['id'] == $qid){
      		$session_questions[$key]['answer_user'] = ($value['answer_valid'] == $answer ? true : false);
      		$session_questions[$key]['answer_num'] = $answer;
      		break;
      	}
      }
      $this->session->set('session_questions', $session_questions);
      //$this->session->set('qcm_timer', \Drupal::time()->getCurrentTime());
    }

    return [/*'#markup' => 'test_d8'*/];
  }

  public function updateTimer() {
		if ($this->session->get('qcm_timer')){
			$this->session->set('qcm_timer', \Drupal::time()->getCurrentTime());
		}
		return [];
	}

}
