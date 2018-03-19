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

	  	$resultsTests = $this->getData();
	  	$countATR = count($resultsTests);// count resultsTests
	    $emptyMessage = $this->t(
	    	"Vous n'avez passé aucun test jusqu'à présent... \nC'est l'occasion de <a href='@link'>passer votre premier test</a> !",
	    	array(
	    		'@link'   => Url::fromRoute('view.test_drupal8.page_1')->toString()
	    	)
	    );
	    $message = $this->t(
	    	"Vous avez effectué %number test@plural ! \nPasser en un de plus <a href='@link'>ici</a>",
	    	array(
		        "%number" => count($resultsTests),
		        "@plural" => $this->frenchPlural(count($resultsTests)),
	    		'@link'   => Url::fromRoute('view.test_drupal8.page_1')->toString()

		    )
	    );

		$options = array();
		$header = array(
			$this->t('Thème'),
			$this->t('Date'),
			$this->t('Temps passé'),
			$this->t('Résultat')
		);

	 	foreach($resultsTests as $currentTest){
			$options[] = array(
		        $this->getNodeTitle($currentTest->nid),
				$this->getFormatedDate($currentTest->date_end),
		        $this->getPassedTime($currentTest->date_end, $currentTest->date_start),
		        $currentTest->score
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

	public function getFormatedDate($date){
		$dateDay = format_date($date, '', 'l j F Y');
		$dateHour = format_date($date, '', 'H:i');

		$formatedDate = 'Le '.$dateDay.' à '.$dateHour;

		return $formatedDate;
	}

	public function getPassedTime($dateEnd, $dateStart){

		$operation = $dateEnd - $dateStart;

		$passedTime = format_date(
			$operation,
			'',
			'i:s'
		);

		return $passedTime;
	}

}
