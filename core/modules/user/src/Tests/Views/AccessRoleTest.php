<?php

/**
 * @file
 * Contains \Drupal\user\Tests\Views\AccessRoleTest.
 */

namespace Drupal\user\Tests\Views;

use Drupal\user\Plugin\views\access\Role;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests views role access plugin.
 *
 * @group user
 * @see \Drupal\user\Plugin\views\access\Role
 */
class AccessRoleTest extends AccessTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('test_access_role');

  /**
   * Tests role access plugin.
   */
  function testAccessRole() {
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = \Drupal::entityManager()->getStorage('view')->load('test_access_role');
    $display = &$view->getDisplay('default');
    $display['display_options']['access']['options']['role'] = array(
      $this->normalRole => $this->normalRole,
    );
    $view->save();
    $this->container->get('router.builder')->rebuildIfNeeded();
    $expected = [
      'config' => ['user.role.' . $this->normalRole],
      'module' => ['user'],
    ];
    $this->assertIdentical($expected, $view->calculateDependencies());

    $executable = Views::executableFactory()->get($view);
    $executable->setDisplay('page_1');

    $access_plugin = $executable->display_handler->getPlugin('access');
    $this->assertTrue($access_plugin instanceof Role, 'Make sure the right class got instantiated.');

    // Test the access() method on the access plugin.
    $this->assertFalse($executable->display_handler->access($this->webUser));
    $this->assertTrue($executable->display_handler->access($this->normalUser));

    $this->drupalLogin($this->webUser);
    $this->drupalGet('test-role');
    $this->assertResponse(403);

    $this->drupalLogin($this->normalUser);
    $this->drupalGet('test-role');
    $this->assertResponse(200);

    // Test allowing multiple roles.
    $view = Views::getView('test_access_role')->storage;
    $display = &$view->getDisplay('default');
    $display['display_options']['access']['options']['role'] = array(
      $this->normalRole => $this->normalRole,
      'anonymous' => 'anonymous',
    );
    $view->save();
    $this->container->get('router.builder')->rebuildIfNeeded();

    // Ensure that the list of roles is sorted correctly, if the generated role
    // ID comes before 'anonymous', see https://www.drupal.org/node/2398259.
    $roles = ['user.role.anonymous', 'user.role.' . $this->normalRole];
    sort($roles);
    $expected = [
      'config' => $roles,
      'module' => ['user'],
    ];
    $this->assertIdentical($expected, $view->calculateDependencies());
    $this->drupalLogin($this->webUser);
    $this->drupalGet('test-role');
    $this->assertResponse(403);
    $this->drupalLogout();
    $this->drupalGet('test-role');
    $this->assertResponse(200);
    $this->drupalLogin($this->normalUser);
    $this->drupalGet('test-role');
    $this->assertResponse(200);
  }

  /**
   * Tests access on render caching.
   */
  public function testRenderCaching() {
    $view = Views::getView('test_access_role');
    $display = &$view->storage->getDisplay('default');
    $display['display_options']['cache'] = [
      'type' => 'tag',
    ];
    $display['display_options']['access']['options']['role'] = array(
      $this->normalRole => $this->normalRole,
    );
    $view->save();

    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    /** @var \Drupal\Core\Session\AccountSwitcherInterface $account_switcher */
    $account_switcher = \Drupal::service('account_switcher');


    // First access as user without access.
    $build = DisplayPluginBase::buildBasicRenderable('test_access_role', 'default');
    $account_switcher->switchTo($this->normalUser);
    $result = $renderer->renderPlain($build);
    $this->assertNotEqual($result, '');

    // Then with access.
    $build = DisplayPluginBase::buildBasicRenderable('test_access_role', 'default');
    $account_switcher->switchTo($this->webUser);
    $result = $renderer->renderPlain($build);
    $this->assertEqual($result, '');
  }

}
