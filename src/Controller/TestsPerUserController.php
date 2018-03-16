<?php

namespace Drupal\test_d8\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;


class TestsPerUserController extends ControllerBase {

  protected $entityTM;

  public function __construct(EntityTypeManagerInterface $entityTypeManager){
    $this->entityTM = $entityTypeManager;
  }

  public static function create(ContainerInterface $container){
    return new static(
      $container->get("entity_type.manager")
    );
  }

  public function content() {
    return [
      [
        '#theme' => 'test_per_user',
        '#data' => $this->getTests(),
        '#type' => 'remote',
      ],
      ['#type' => 'pager']
    ];
  }

  public function getThemes() {
    $node = $this->entityTM->getStorage('node');
    $ids = \Drupal::entityQuery('node')->condition('type', 'test')->execute();
    $allThemes = $node->loadMultiple($ids);

    $themes = [];
    foreach($allThemes as $d){
      $themes[$d->id()] = $d->getTitle();
    }
    return $themes;
  }

  public function getTests() {
    $config = \Drupal::config('test_d8.settings');
    $num = $config->get('num_tests_per_page');
    $number_of_questions = $config->get('number_of_questions');

    $query = \Drupal::database()->select('test_d8_test_result', 'd8');
    $query->fields('d8', ['nid', 'date_start', 'date_end', 'questions_status', 'score'])
      ->condition('uid', $this->getCurrentUserID())
      ->orderBy('date_end', 'DESC');
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit($num);
    $result = $pager->execute()->fetchAll();

    $themes = $this->getThemes();
    //$themes = self::getThemes();

    $output = [];
    $total_score = $total_num_questions = $num_tests = 0;
    foreach ($result as $row){
      ++$num_tests;
      $num_questions = count(unserialize($row->questions_status));
      $total_score += $row->score;
      $total_num_questions += $num_questions;

      /*$output[] = [
        'theme' => $themes[$row->nid],
        'date_start' => $row->date_start,
        'date_end' => $row->date_end,
        'time_test' => date('i s', $row->date_end - $row->date_start),
        'score' => $row->score,
        'num_questions' => $num_questions,
      ];*/
      $output[] = [
        '#theme' => 'table',
        'date_start' => $row->date_start,
        'date_end' => $row->date_end,
        'time_test' => date('i s', $row->date_end - $row->date_start),
        'score' => $row->score,
        'num_questions' => $num_questions,
      ];
    }
    /*$gcd = gmp_intval(gmp_gcd((string)$total_score, (string)$total_num_questions));
    $reducted_ratio = ($total_score / $gcd).' / '.($total_num_questions / $gcd);*/

    return $output;
  }

  public function getCurrentUserID() {
    return \Drupal::currentUser()->id();
  }

}
