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

	public function getDashboard(){

	  $allTestsResult = $this->getData();

		$options = array();
	 	foreach ($allTestsResult as $testResult){
			$options[] = array(
        $this->getNodeTitle($testResult->nid),
				format_date($testResult->date_end, '', 'l j F Y - H:i'),
        format_date($testResult->date_end - $testResult->date_start, '', 'i:s'),
        $testResult->score
			);
		}

		$output["table"] = array(
			'#theme' => 'table',
			'#header' => [$this->t('Thème'), $this->t('Date'), $this->t('Temps passé'), $this->t('Score')],
			'#cache' => ['disabled' => TRUE],
			'#rows' => $options,
			'#empty' => $this->t('Aucun test pour le moment. <a href="@url">Passer un test</a>', [
				'@url' => Url::fromRoute('view.test_drupal8.page_1')->toString()
			])
		);

	  if (!empty($options)){
	      $output["table"]['#caption'] = $this->t("Vous avez effectué @number test@plural.", array(
	        "@number" => count($allTestsResult),
	        "@plural" => $this->frenchPlural(count($allTestsResult))
	      )
	    );
	  }

		return array($output);
	}

	public function getCurrentUserID(){
		return \Drupal::currentUser()->id();
	}

	public function frenchPlural($int){
		return ($int > 1) ? 's' : '';
	}

	// retourne tous les tests de l'utilisateur courant
	public function getData(){
		return $this->database->select('test_d8_test_result','tdtr')
			->fields('tdtr', ['trid', 'nid', 'date_start', 'date_end','score'])
			->condition('uid',  $this->getCurrentUserID())
			->orderBy('date_end', 'DESC')
			->execute()
			->fetchAll();
	}

  public function getNodeTitle($nid){
    return $this->entityTMNode->load($nid)->getTitle();
  }

}
