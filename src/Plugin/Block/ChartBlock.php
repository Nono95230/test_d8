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
    //$build = ['#markup' => '<h1>pipo</h1>'];

    $themes = $this->getThemes();

    $config = \Drupal::config('test_d8.settings');
    $number_of_questions = $config->get('number_of_questions');

    $build['#theme'] = 'test_per_user_chart';
    //$build['#data']['tabs'] = $themes;

    $scores = [];
    $tabs = [];
    $i = 0;
    foreach ($themes as $theme_id => $theme_name){
      $data = $this->getScoresByTheme($theme_id);
      $scores[$i][$theme_id] = [];
      //$scores[$i][$theme_id]['name'] = $theme_name;
      $tabs[$theme_id] = [
        'num' => count($data),
        'name' => $theme_name,
      ];
      foreach ($data as $obj){
        $scores[$i][$theme_id][] = [date('j M Y', $obj->date_start) . "\n" . date('H\hi', $obj->date_start), $obj->score];
      }
      ++$i;
    }
    $build['#data']['tabs'] = $tabs;

/*
var_dump($scores);
array(2) {
  [0]=> array(1) { <- index des tabs jquery ui
    [1]=> array(3) { <- nid
      [0]=> array(2) {
        [0]=> string(23) "14 Mar 2018 à 11:19:42"
        [1]=> string(1) "3"
      }
      [1]=> array(2) {
        [0]=> string(23) "14 Mar 2018 à 11:19:01"
        [1]=> string(1) "4"
      }
      [2]=> array(2) {
        [0]=> string(23) "14 Mar 2018 à 11:14:19"
        [1]=> string(1) "5"
      }
    }
  }
  [1]=> array(1) {
    [2]=> array(1) {
      [0]=> array(2) {
        [0]=> string(23) "15 Mar 2018 à 13:32:06"
        [1]=> string(1) "4"
      }
    }
  }
}
*/

    $build['#attached']['drupalSettings']['TestD8']['chart']['rows'] = $scores;

    $build['#attached']['drupalSettings']['TestD8']['chart']['number_of_questions'] = $number_of_questions;
    $build['#attached']['drupalSettings']['TestD8']['chart']['Webmaster'] = [
      ["10 déc 2014\n11h20", 21],
      ["5 juin 2015\n11h20", 32],
      ["10 sep 2016\n11h20", 28],
      ["15 nov 2017\n11h20", 36]
    ];
    $build['#attached']['drupalSettings']['TestD8']['chart']['Themer'] = [
      ['10 déc 2014', 18],
      ['5 juin 2015', 24],
      ['10 sep 2016', 21],
      ['15 nov 2017', 34]
    ];

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
      ->orderBy('date_end', 'DESC')
      ->execute()
      ->fetchAll();
  }

  public function getCurrentUserID() {
    return \Drupal::currentUser()->id();
  }

}
