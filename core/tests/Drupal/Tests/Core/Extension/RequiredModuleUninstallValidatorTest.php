<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Extension\RequiredModuleUninstallValidatorTest.
 */

namespace Drupal\Tests\Core\Extension;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Extension\RequiredModuleUninstallValidator
 * @group Extension
 */
class RequiredModuleUninstallValidatorTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\Extension\RequiredModuleUninstallValidator|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $uninstallValidator;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->uninstallValidator = $this->getMockBuilder('Drupal\Core\Extension\RequiredModuleUninstallValidator')
      ->disableOriginalConstructor()
      ->setMethods(['getModuleInfoByModule'])
      ->getMock();
    $this->uninstallValidator->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * @covers ::validate
   */
  public function testValidateNoModule() {
    $this->uninstallValidator->expects($this->once())
      ->method('getModuleInfoByModule')
      ->willReturn([]);

    $module = $this->randomMachineName();
    $expected = [];
    $reasons = $this->uninstallValidator->validate($module);
    $this->assertSame($expected, $reasons);
  }

  /**
   * @covers ::validate
   */
  public function testValidateNotRequired() {
    $module = $this->randomMachineName();

    $this->uninstallValidator->expects($this->once())
      ->method('getModuleInfoByModule')
      ->willReturn(['required' => FALSE, 'name' => $module]);

    $expected = [];
    $reasons = $this->uninstallValidator->validate($module);
    $this->assertSame($expected, $reasons);
  }

  /**
   * @covers ::validate
   */
  public function testValidateRequired() {
    $module = $this->randomMachineName();

    $this->uninstallValidator->expects($this->once())
      ->method('getModuleInfoByModule')
      ->willReturn(['required' => TRUE, 'name' => $module]);

    $expected = [SafeMarkup::format('The @module module is required', ['@module' => $module])];
    $reasons = $this->uninstallValidator->validate($module);
    $this->assertSame($expected, $reasons);
  }

}
