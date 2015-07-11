<?php

/**
 * @file
 * Contains \Drupal\views\Tests\TokenReplaceTest.
 */

namespace Drupal\views\Tests;

use Drupal\views\Views;

/**
 * Tests core view token replacement.
 *
 * @group views
 */
class TokenReplaceTest extends ViewUnitTestBase {

  public static $modules = array('system');

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('test_tokens');

  protected function setUp() {
    parent::setUp();
    $this->installSchema('system', 'url_alias');
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Tests core token replacements generated from a view.
   */
  function testTokenReplacement() {
    $token_handler = \Drupal::token();
    $view = Views::getView('test_tokens');
    $view->setDisplay('page_1');
    $this->executeView($view);

    $expected = array(
      '[view:label]' => 'Test tokens',
      '[view:description]' => 'Test view to token replacement tests.',
      '[view:id]' => 'test_tokens',
      '[view:title]' => 'Test token page',
      '[view:url]' => $view->getUrl(NULL, 'page_1')->setAbsolute(TRUE)->toString(),
      '[view:total-rows]' => (string) $view->total_rows,
      '[view:base-table]' => 'views_test_data',
      '[view:base-field]' => 'id',
      '[view:items-per-page]' => '10',
      '[view:current-page]' => '1',
      '[view:page-count]' => '1',
    );

    foreach ($expected as $token => $expected_output) {
      $output = $token_handler->replace($token, array('view' => $view));
      $this->assertIdentical($output, $expected_output, format_string('Token %token replaced correctly.', array('%token' => $token)));
    }
  }

}
