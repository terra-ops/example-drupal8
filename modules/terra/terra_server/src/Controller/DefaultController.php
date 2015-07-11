<?php

/**
 * @file
 * Contains Drupal\terra_server\Controller\DefaultController.
 */

namespace Drupal\terra_server\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DefaultController.
 *
 * @package Drupal\terra_server\Controller
 */
class DefaultController extends ControllerBase {
  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function status() {

    //Immutable Config (Read Only)
    $config = \Drupal::config('terra.settings');
    $path = 'path:' . $config->get('path_to_config');

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello Terra!') . $path
    ];
  }

}
