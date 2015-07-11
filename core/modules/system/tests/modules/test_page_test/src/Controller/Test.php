<?php

/**
 * @file
 * Contains \Drupal\test_page_test\Controller\Test.
 */

namespace Drupal\test_page_test\Controller;

/**
 * Defines a test controller for page titles.
 */
class Test {

  /**
   * Renders a page with a title.
   *
   * @return array
   *   A render array as expected by drupal_render()
   */
  public function renderTitle() {
    $build = array();
    $build['#markup'] = 'Hello Drupal';
    $build['#title'] = 'Foo';

    return $build;
  }

  /**
   * Renders a page.
   *
   * @return array
   *   A render array as expected by drupal_render().
   */
  public function staticTitle() {
    $build = array();
    $build['#markup'] = 'Hello Drupal';

    return $build;
  }

  /**
   * Returns a 'dynamic' title for the '_title_callback' route option.
   *
   * @return string
   *   The page title.
   */
  public function dynamicTitle() {
    return 'Dynamic title';
  }

  /**
   * Returns a generic page render array for title tests.
   *
   * @return array
   *   A render array as expected by drupal_render()
   */
  public function renderPage() {
    return array(
      '#markup' => 'Content',
    );
  }

}
