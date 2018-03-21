<?php

namespace Drupal\test_d8\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a chart block.
 *
 * @Block(
 *   id = "test_d8_chart",
 *   admin_label = @Translation("Test D8 results chart"),
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

    $build['#theme'] = 'test_per_user_chart';

    $data = [];
    $tabs = [];
    foreach ($themes as $theme_id => $theme_name){
      $scores = $this->getScoresByTheme($theme_id);
      $num = count($scores);

      // tableau de données transmises au JS
      $chartLabels = [];
      $chartData = [];
      foreach ($scores as $obj){
        $chartLabels[] = [date('j M Y', $obj->date_start), date('H\hi', $obj->date_start)];
        $chartData[] = (int)$obj->score;
      }
      $data[] = [
        'id' => $theme_id,
        'name' => $theme_name,
        'num' => $num,
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
      ];

      // données transmises à twig (jquery ui tabs)
      $tabs[$theme_id] = [
        'name' => $theme_name,
        'num' => $num,
        //'score' => $num,
      ];
    }

    $build['#attached']['drupalSettings']['TestD8']['chart']['data'] = $data;
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
