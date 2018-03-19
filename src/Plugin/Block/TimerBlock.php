<?php

namespace Drupal\test_d8\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a timer block.
 *
 * @Block(
 *   id = "test_d8_countdown_timer",
 *   admin_label = @Translation("Test D8 countdown timer"),
 * )
 */
class TimerBlock extends BlockBase {

  /**
  * {@inheritdoc}
  */
  public function build() {
    $config = \Drupal::config('test_d8.settings');
    $time = \Drupal::time()->getCurrentTime();
    $testD8Time = $time + $config->get('time_to_complete_test');
    $build = [];
    $build['#theme'] = 'countdowntimer';

    // set timer
    $session = \Drupal::service('user.private_tempstore')->get('test_d8');
    if (!$session->get('session_questions')){
      $build['#attached']['drupalSettings']['TestD8']['countdown'] = $testD8Time;
    } else {
      //$session_questions = $session->get('session_questions');
      $date_start = $session->get('date_start');
      $timer = $session->get('qcm_timer');

      // time left
      $timeLeft = $config->get('time_to_complete_test') - ($timer - $date_start);
      $build['#attached']['drupalSettings']['TestD8']['countdown'] = $time + $timeLeft;
    }
    return $build;
  }

}
