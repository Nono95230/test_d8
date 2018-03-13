<?php

namespace Drupal\test_d8\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Entity\EntityTypeManagerInterface;
//use Drupal\Core\Routing\RouteMatchInterface;
//use Drupal\Core\Url;

class TestD8Controller extends ControllerBase {

	protected $entityTM;
	protected $currentRoute;
	protected $DS = DIRECTORY_SEPARATOR;
	protected $entity = 'node';
	protected $nodeType = 'test';


	public function __construct(EntityTypeManagerInterface $entityTypeManager){
		$this->entityTM = $entityTypeManager;
	}

	public static function create(ContainerInterface $container){
		return new static(
			$container->get("entity_type.manager")
		);
	}

	public function content(){
		// destroy eventual remaining session
		$session = \Drupal::service('user.private_tempstore')->get('test_d8');
		$session->delete('session_questions');
		$session->delete('questions_list');
		$session->delete('date_start');
		$session->delete('timer');

		$node = $this->entityTM->getStorage($this->entity);
		$ids = \Drupal::entityQuery($this->entity)
					->condition('type', $this->nodeType)
					->execute();

		$nodeTestCollection = $node->loadMultiple($ids);

		$langCodeId = \Drupal::languageManager()->getCurrentLanguage()->getId();
		$url = $this->DS.$langCodeId.$this->DS.'test-d8'.$this->DS;

		$config = \Drupal::config('test_d8.settings');
		$themeTermsList = [];
		foreach ($nodeTestCollection as $nodeTest){
			$themeTermsList[] = [
				'url' => $url.$nodeTest->id(),
				'name' => $nodeTest->getTitle(),
				'number_of_questions' => $config->get('number_of_questions'),
				'time_to_complete_test' => $config->get('time_to_complete_test'),
			];

		}

		return [
			'#theme' => 'theme_list',
			'#data' => $themeTermsList,
		];
	}

}
