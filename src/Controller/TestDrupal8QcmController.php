<?php

namespace Drupal\test_d8\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\node\NodeInterface;

class TestDrupal8QcmController extends ControllerBase {

  protected $configTestD8;
  protected $request;
  protected $formBuilder;
  protected $formBuilderNamespace;

	public function __construct(Request $request, ConfigFactory $configFactory, FormBuilderInterface $formBuilder){
    $this->request              = $request->request;
    $this->configTestD8         = $configFactory->getEditable("test_d8.settings");
    $this->formBuilder          = $formBuilder;
    $this->formBuilderNamespace = $this->configTestD8->get('namespace.qcm_form');
	}

	public static function create(ContainerInterface $container){
		return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('config.factory'),
      $container->get('form_builder')
		);
	}

	public function content(NodeInterface $node = null){
    return ['form' => $this->formBuilder->getForm($this->formBuilderNamespace, $node)];
	}

  public function updateSession() {
  	$qid = $this->request->get('qid');
  	$answer = $this->request->get('answer');

    if (isset($_COOKIE['testD8'])){
      $cookie = unserialize($_COOKIE['testD8']);
      $session_questions = $cookie['session_questions'];
      foreach ($session_questions as $key => $value){
      	if ($value['id'] == $qid){
      		$session_questions[$key]['answer_user'] = ($value['answer_valid'] == $answer ? true : false);
      		$session_questions[$key]['answer_num'] = $answer;
      		break;
      	}
      }
      $cookie['session_questions'] = $session_questions;
      setcookie('testD8', serialize($cookie), time()+3600*24*365);
    }
    return [];
  }

  // set the remaining time of the current test
  public function updateTimer() {
		if (isset($_COOKIE['testD8'])){
      $cookie = unserialize($_COOKIE['testD8']);
      $timeLeft = \Drupal::request()->request->get('timeLeft');
      $cookie['qcm_timer'] = $timeLeft;
		  setcookie('testD8', serialize($cookie), time()+3600);
		}
		return [];
	}

}
