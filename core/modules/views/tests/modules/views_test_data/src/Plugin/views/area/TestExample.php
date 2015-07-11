<?php

/**
 * @file
 * Contains \Drupal\views_test_data\Plugin\views\area\TestExample.
 */

namespace Drupal\views_test_data\Plugin\views\area;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Test area plugin.
 *
 * @see \Drupal\views\Tests\Handler\AreaTest
 *
 * @ViewsArea("test_example")
 */
class TestExample extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $this->options['custom_access'];
  }

  /**
   * Overrides Drupal\views\Plugin\views\area\AreaPluginBase::option_definition().
   */
  public function defineOptions() {
    $options = parent::defineOptions();
    $options['string'] = array('default' => '');
    $options['custom_access'] = array('default' => TRUE);

    return $options;
  }

  /**
   * Overrides Drupal\views\Plugin\views\area\AreaPluginBase::buildOptionsForm()
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $this->globalTokenForm($form, $form_state);
  }

  /**
   * Implements \Drupal\views\Plugin\views\area\AreaPluginBase::render().
   */
  public function render($empty = FALSE) {
    if (!$empty || !empty($this->options['empty'])) {
      return array(
        '#markup' => $this->globalTokenReplace($this->options['string']),
      );
    }
    return array();
  }

}
