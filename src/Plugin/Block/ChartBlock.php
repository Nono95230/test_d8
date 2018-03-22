<?php

namespace Drupal\test_d8\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a chart block.
 *
 * @Block(
 *   id = "chart_tests_drupal8",
 *   admin_label = @Translation("Chart Tests Drupal 8"),
 * )
 */
class ChartBlock extends BlockBase {

  /**
  * {@inheritdoc}
  */
  public function build() {
    $themes = $this->getThemes();

    $config = \Drupal::config('test_d8.settings');
    $number_of_questions = $config->get('number_of_questions');

    $build['#theme'] = 'chart_tests_drupal8';

    $data = [];
    $tabs = [];
  $is_there_any_test = false;
    foreach ($themes as $theme_id => $theme_name){
      $scores = $this->getScoresByTheme($theme_id);
      $numTest = count($scores);
    if ($numTest){
      $is_there_any_test = true;
    }

      // tableau de données transmises au JS
      $chartLabels = [];
      $chartData = [];
    $scoreSum = 0;
      foreach ($scores as $obj){
        $chartLabels[] = [date('j M Y', $obj->date_start), date('H\hi', $obj->date_start)];
        $chartData[] = (int)$obj->score;
    $scoreSum += (int)$obj->score;
      }
    
      $percent = ($numTest < 1 ? 0 : intval($scoreSum / ($numTest * $number_of_questions) * 100));
    
      $data[] = [
        'id' => $theme_id,
        'name' => $theme_name,
        'num_test' => $numTest,
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
        'percent' => $percent,
      ];

      // données transmises à twig (jquery ui tabs)
      $tabs[$theme_id] = [
        'name' => $theme_name,
        'num_test' => $numTest,
        'number_of_questions' => $number_of_questions,
        'score_sum' => $scoreSum,
        'average' => ($numTest < 1 ? 0 : ($scoreSum / $numTest)),
        'percent' => $percent,
      ];
      
    }

    $build['#attached']['drupalSettings']['TestD8']['chart']['data'] = $data;
    $build['#data']['anytest'] = $is_there_any_test;
    $build['#data']['tabs'] = $tabs;

    $build['#attached']['drupalSettings']['TestD8']['chart']['number_of_questions'] = $number_of_questions;

    return $build;
  }

  public function getThemes() {
    $node = \Drupal::entityTypeManager()->getStorage('node');
    $ids = \Drupal::entityQuery('node')->condition('type', 'test')->execute();
    $allThemes = $node->loadMultiple($ids);

    $themes = [];
    foreach($allThemes as $d){
      $themes[$d->id()] = $d->getTitle();
    }
    ksort($themes);
    return $themes;
  }

  // retourne tous les tests (filtrés par thème) de l'utilisateur courant
  public function getScoresByTheme($theme_id){
    return \Drupal::database()->select('test_d8_test_result', 'd8')
      ->fields('d8', ['score', 'date_start'])
      ->condition('uid', $this->getCurrentUserID())
      ->condition('nid', $theme_id)
      ->orderBy('date_end', 'ASC')
      ->execute()
      ->fetchAll();
  }

  public function getCurrentUserID() {
    return \Drupal::currentUser()->id();
  }

}
