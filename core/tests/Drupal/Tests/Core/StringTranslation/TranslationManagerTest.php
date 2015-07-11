<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\StringTranslation\TranslationManagerTest.
 */

namespace Drupal\Tests\Core\StringTranslation {

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\StringTranslation\TranslationManager
 * @group StringTranslation
 */
class TranslationManagerTest extends UnitTestCase {

  /**
   * The tested translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translationManager;

  protected function setUp() {
    $this->translationManager = new TestTranslationManager();
  }

  /**
   * Provides some test data for formatPlural()
   * @return array
   */
  public function providerTestFormatPlural() {
    return array(
      array(1, 'Singular', '@count plural', array(), array(), 'Singular', TRUE),
      array(2, 'Singular', '@count plural', array(), array(), '2 plural', TRUE),
      // @todo support locale_get_plural
      array(2, 'Singular', '@count @arg', array('@arg' => '<script>'), array(), '2 &lt;script&gt;', TRUE),
      array(2, 'Singular', '@count %arg', array('%arg' => '<script>'), array(), '2 <em class="placeholder">&lt;script&gt;</em>', TRUE),
      array(2, 'Singular', '@count !arg', array('!arg' => '<script>'), array(), '2 <script>', FALSE),
    );
  }

  /**
   * @dataProvider providerTestFormatPlural
   */
  public function testFormatPlural($count, $singular, $plural, array $args = array(), array $options = array(), $expected, $safe) {
    $translator = $this->getMock('\Drupal\Core\StringTranslation\Translator\TranslatorInterface');
    $translator->expects($this->once())
      ->method('getStringTranslation')
      ->will($this->returnCallback(function ($langcode, $string) {
        return $string;
      }));
    $this->translationManager->addTranslator($translator);
    $result = $this->translationManager->formatPlural($count, $singular, $plural, $args, $options);
    $this->assertEquals($expected, $result);
    $this->assertEquals(SafeMarkup::isSafe($result), $safe);
  }

}

class TestTranslationManager extends TranslationManager {

  public function __construct() {
  }

}

}

namespace {
  if (!defined('LOCALE_PLURAL_DELIMITER')) {
    define('LOCALE_PLURAL_DELIMITER', "\03");
  }
}
