<?php

/**
 * @file
 * Contains \Drupal\Tests\migrate_drupal\Unit\source\d6\FileTest.
 */

namespace Drupal\Tests\migrate_drupal\Unit\source\d6;

use Drupal\Tests\migrate\Unit\MigrateSqlSourceTestCase;

/**
 * Tests D6 file source plugin.
 *
 * @group migrate_drupal
 */
class FileTest extends MigrateSqlSourceTestCase {

  const PLUGIN_CLASS = 'Drupal\migrate_drupal\Plugin\migrate\source\d6\File';

  // The fake Migration configuration entity.
  protected $migrationConfiguration = array(
    // The ID of the entity, can be any string.
    'id' => 'test',
    // Leave it empty for now.
    'idlist' => array(),
    'source' => array(
      'plugin' => 'd6_file',
    ),
  );

  protected $expectedResults = array(
    array(
      'fid' => 1,
      'uid' => 1,
      'filename' => 'migrate-test-file-1.pdf',
      'filepath' => 'sites/default/files/migrate-test-file-1.pdf',
      'filemime' => 'application/pdf',
      'filesize' => 890404,
      'status' => 1,
      'timestamp' => 1382255613,
    ),
    array(
      'fid' => 2,
      'uid' => 1,
      'filename' => 'migrate-test-file-2.pdf',
      'filepath' => 'sites/default/files/migrate-test-file-2.pdf',
      'filemime' => 'application/pdf',
      'filesize' => 204124,
      'status' => 1,
      'timestamp' => 1382255662,
    ),
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->databaseContents['files'] = $this->expectedResults;
    parent::setUp();
  }

}
