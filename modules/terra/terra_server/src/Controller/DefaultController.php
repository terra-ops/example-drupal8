<?php

/**
 * @file
 * Contains Drupal\terra_server\Controller\DefaultController.
 */

namespace Drupal\terra_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Yaml\Parser;
use Drupal\Component\Utility\SafeMarkup;

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
    $path = $config->get('path_to_config');

    $yaml = new Parser();
    $terra_config = $yaml->parse(file_get_contents($path));

    $output = array();
    $output['note'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, Terra!'),
    ];
    $output['apps'] = [
      '#type' => 'table',
      '#header' => array(t('Name'), t('Description'), t('Repo'), t('Environments')),
      '#empty' => t('There are no apps yet.'),
    ];

    foreach ($terra_config['apps'] as $name => $app) {
      $output['apps'][$name]['title'] = array(
        '#markup' => SafeMarkup::checkPlain($app['name']),
      );
      $output['apps'][$name]['description'] = array(
        '#markup' => SafeMarkup::checkPlain($app['description']),
      );
      $output['apps'][$name]['repo'] = array(
        '#markup' => SafeMarkup::checkPlain($app['repo']),
      );

      $environments = is_array($app['environments']) ? implode(', ', array_keys($app['environments'])) : 'None';
      $output['apps'][$name]['environments'] = array(
        '#markup' => SafeMarkup::checkPlain($environments),
      );

    }

    return $output;
  }

}
