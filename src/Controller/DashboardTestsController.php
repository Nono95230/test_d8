<?php

namespace Drupal\test_d8\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\DependencyInjection\ContainerInterface;


use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;


class DashboardTestsController extends ControllerBase {

	protected $database;
	protected $currentTime;
	protected $entityTMNode;


	public function __construct(Connection $database, Time $time, EntityTypeManagerInterface $entityTypeManager){
		$this->database 			= $database;
		$this->currentTime 		= $time->getCurrentTime();
		$this->entityTMNode 	= $entityTypeManager->getStorage('node');
	}


	public static function create(ContainerInterface $container){
		return new static(
			$container->get("database"),
			$container->get('datetime.time'),
			$container->get("entity_type.manager")
		);
	}

	public function getDashboard() {

	  	$allTestsResult = $this->getData();
	  	//$link = Url::fromRoute('test_d8.test_d8')->toString();
	    //$emptyMessage = $this->t("Vous n'avez passé aucun test jusqu'à présent ! <a href='$link'>Passer un test</a>");
	    $emptyMessage = $this->t("Vous n'avez passé aucun test jusqu'à présent !");
	    $message = $this->t("Vous avez effectué %number test@plural !", array(
	        "%number" => count($allTestsResult),
	        "@plural" => $this->frenchPlural(count($allTestsResult))
	      )
	    );

		$header = array( $this->t('Thème'), $this->t('Date'), $this->t('Temps passé'), $this->t('Score') );
		$options = array();

	 	foreach($allTestsResult as $testResult){
			$options[] = array(
        $this->getNodeTitle($testResult->nid),
				format_date($testResult->date_end, '', 'l j F Y - H:i'),
        format_date($testResult->date_end - $testResult->date_start, '', 'i:s'),
        $testResult->score
			);
		}


		$output["table"] = array(
			'#theme' => 'table',
			'#header' => $header,
			'#cache' => ['disabled' => TRUE],
			'#rows' => $options,
			'#empty' => $emptyMessage
		);

	    if ( !empty($options) ) {
	      $output["table"]['#caption'] = $message;
	    }

		return array($output);

	}


	public function getCurrentUserID() {
		return \Drupal::currentUser()->id();
	}


	public function frenchPlural($int){
		return ($int > 1) ? 's' : '';
	}



	public function getData() {

		$query = $this->database->select('test_d8_test_result','tdtr')
				->fields('tdtr', ['trid', 'nid', 'date_start', 'date_end','score'])
				->condition( 'uid',  $this->getCurrentUserID() )
				->orderBy('date_end', 'DESC')
				->execute();

		return $query->fetchAll();

	}

  public function getNodeTitle($nid){
    return $this->entityTMNode->load($nid)->getTitle();
  }

}
