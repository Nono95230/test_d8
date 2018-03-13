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
    $build['#theme'] = 'test_per_user_chart';

    $themes = $this->getThemes();

    foreach ($themes as $theme){

    }


    //foreach ($var as $k => $v){
      $build['#attached']['drupalSettings']['TestD8']['chart'] = [
        [1,  37.8, 80.8, 41.8],
        [2,  30.9, 69.5, 32.4],
        [3,  25.4,   57, 25.7],
        [4,  11.7, 18.8, 10.5],
        [5,  11.9, 17.6, 10.4],
        [6,   8.8, 13.6,  7.7],
        [7,   7.6, 12.3,  9.6],
        [8,  12.3, 29.2, 10.6],
        [9,  16.9, 42.9, 14.8],
        [10, 12.8, 30.9, 11.6],
        [11,  5.3,  7.9,  4.7],
        [12,  6.6,  8.4,  5.2],
        [13,  4.8,  6.3,  3.6],
        [14,  4.2,  6.2,  3.4]
      ];
    //}

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

  public function getScoresByTheme($id_theme){
      $query = \Drupal::database()->select('test_d8_test_result', 'd8');
    $query->fields('d8', ['score'])
      ->condition('uid', $this->getCurrentUserID())
      ->condition('nid', $id_theme)
      ->orderBy('date_end', 'DESC');
    return $query->execute()->fetchAll();
  }

}
