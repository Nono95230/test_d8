<?php

namespace Drupal\test_d8\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormBuilderInterface;

use Drupal\node\NodeInterface;

class TestDrupal8QcmController extends ControllerBase {

  //protected $session;
  protected $configTestD8;
  protected $request;
  protected $currentTime;
  protected $formBuilder;
  protected $formBuilderNamespace;

	// Pass the dependency to the object constructor
	public function __construct(Request $request, TimeInterface $currentTime, ConfigFactory $configFactory, FormBuilderInterface $formBuilder){
    //$this->session              = $session->get('test_d8');
    $this->request              = $request->request;
    $this->currentTime          = $currentTime->getCurrentTime();
    $this->configTestD8         = $configFactory->getEditable("test_d8.settings");
    $this->formBuilder          = $formBuilder;
    $this->formBuilderNamespace = $this->configTestD8->get('namespace.qcm_form');
	}

	// Uses Symfony's ContainerInterface to declare dependency to be passed to constructor
	public static function create(ContainerInterface $container){
		return new static(
      //$container->get('user.private_tempstore'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('datetime.time'),
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

    //if ($this->session->get('session_questions')){
      //$session_questions = $this->session->get('session_questions');
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
      //$this->session->set('session_questions', $session_questions);
      $cookie['session_questions'] = $session_questions;
      setcookie('testD8', serialize($cookie), time()+3600*24*365);
    }
    return [];
  }

  public function updateTimer() {
    /*if ($this->session->get('qcm_timer')){
      $this->session->set('qcm_timer', $this->currentTime);
    }*/
    //kint ($_COOKIE['testD8']);
    //exit;
    //kint($_COOKIE);exit;
		if (isset($_COOKIE['testD8'])){
      $cookie = unserialize($_COOKIE['testD8']);
      $cookie['qcm_timer'] = $this->currentTime;
		  setcookie('testD8', serialize($cookie), time()+3600);
		}
		return [];
	}

}
