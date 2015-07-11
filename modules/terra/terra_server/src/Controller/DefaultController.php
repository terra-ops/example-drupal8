<?php

/**
 * @file
 * Contains Drupal\terra_server\Controller\DefaultController.
 */

namespace Drupal\terra_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Yaml\Parser;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;

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

    if (!file_exists($path)) {
       drupal_set_message('Terra config file does not exist at ' . $path . '!', 'error');
    }

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

      $environments = [];
      foreach ($app['environments'] as $environment) {
        // Make sure the URL exists before trying to format it.
        if (empty($environment['url'])) {
          continue;
        }
        $url = Url::fromUri($environment['url']);
        $environments[] = $this->l($environment['name'], $url);
      }
      $output['apps'][$name]['environments'] = array(
        '#markup' => SafeMarkup::format(is_array($environments) ? implode(', ', $environments) : 'None'),
      );

    }

    return $output;
  }

}
