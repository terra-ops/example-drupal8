<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateAggregatorConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\config\Tests\SchemaCheckTestTrait;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\d6\MigrateDrupal6TestBase;

/**
 * Upgrade variables to aggregator.settings.yml.
 *
 * @group migrate_drupal
 */
class MigrateAggregatorConfigsTest extends MigrateDrupal6TestBase {

  use SchemaCheckTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('aggregator');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $migration = entity_load('migration', 'd6_aggregator_settings');
    $dumps = array(
      $this->getDumpDirectory() . '/Variable.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests migration of aggregator variables to aggregator.settings.yml.
   */
  public function testAggregatorSettings() {
    $config = $this->config('aggregator.settings');
    $this->assertIdentical('aggregator', $config->get('fetcher'));
    $this->assertIdentical('aggregator', $config->get('parser'));
    $this->assertIdentical(array('aggregator'), $config->get('processors'));
    $this->assertIdentical(600, $config->get('items.teaser_length'));
    $this->assertIdentical('<a> <b> <br /> <dd> <dl> <dt> <em> <i> <li> <ol> <p> <strong> <u> <ul>', $config->get('items.allowed_html'));
    $this->assertIdentical(9676800, $config->get('items.expire'));
    $this->assertIdentical(3, $config->get('source.list_max'));
    $this->assertConfigSchema(\Drupal::service('config.typed'), 'aggregator.settings', $config->get());
  }

}
