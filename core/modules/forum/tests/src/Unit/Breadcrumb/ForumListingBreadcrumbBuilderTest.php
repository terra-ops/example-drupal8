<?php

/**
 * @file
 * Contains \Drupal\Tests\forum\Unit\Breadcrumb\ForumListingBreadcrumbBuilderTest.
 */

namespace Drupal\Tests\forum\Unit\Breadcrumb;

use Drupal\Core\Link;
use Drupal\Tests\UnitTestCase;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * @coversDefaultClass \Drupal\forum\Breadcrumb\ForumListingBreadcrumbBuilder
 * @group forum
 */
class ForumListingBreadcrumbBuilderTest extends UnitTestCase {

  /**
   * Tests ForumListingBreadcrumbBuilder::applies().
   *
   * @param bool $expected
   *   ForumListingBreadcrumbBuilder::applies() expected result.
   * @param string|null $route_name
   *   (optional) A route name.
   * @param array $parameter_map
   *   (optional) An array of parameter names and values.
   *
   * @dataProvider providerTestApplies
   * @covers ::applies
   */
  public function testApplies($expected, $route_name = NULL, $parameter_map = array()) {
    // Make some test doubles.
    $entity_manager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $config_factory = $this->getConfigFactoryStub(array());
    $forum_manager = $this->getMock('Drupal\forum\ForumManagerInterface');

    // Make an object to test.
    $builder = $this->getMockBuilder('Drupal\forum\Breadcrumb\ForumListingBreadcrumbBuilder')
      ->setConstructorArgs(array(
        $entity_manager,
        $config_factory,
        $forum_manager,
      ))
      ->setMethods(NULL)
      ->getMock();

    $route_match = $this->getMock('Drupal\Core\Routing\RouteMatchInterface');
    $route_match->expects($this->once())
      ->method('getRouteName')
      ->will($this->returnValue($route_name));
    $route_match->expects($this->any())
      ->method('getParameter')
      ->will($this->returnValueMap($parameter_map));

    $this->assertEquals($expected, $builder->applies($route_match));
  }

  /**
   * Provides test data for testApplies().
   *
   * @return array
   *   Array of datasets for testApplies(). Structured as such:
   *   - ForumListBreadcrumbBuilder::applies() expected result.
   *   - ForumListBreadcrumbBuilder::applies() $attributes input array.
   */
  public function providerTestApplies() {
    // Send a Node mock, because NodeInterface cannot be mocked.
    $mock_term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->getMock();

    return array(
      array(
        FALSE,
      ),
      array(
        FALSE,
        'NOT.forum.page',
      ),
      array(
        FALSE,
        'forum.page',
      ),
      array(
        TRUE,
        'forum.page',
        array(array('taxonomy_term', 'anything')),
      ),
      array(
        TRUE,
        'forum.page',
        array(array('taxonomy_term', $mock_term)),
      ),
    );
  }

  /**
   * Tests ForumListingBreadcrumbBuilder::build().
   *
   * @see \Drupal\forum\ForumListingBreadcrumbBuilder::build()
   *
   * @covers ::build
   */
  public function testBuild() {
    // Build all our dependencies, backwards.
    $term1 = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->getMock();
    $term1->expects($this->any())
      ->method('label')
      ->will($this->returnValue('Something'));
    $term1->expects($this->any())
      ->method('id')
      ->will($this->returnValue(1));

    $term2 = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->getMock();
    $term2->expects($this->any())
      ->method('label')
      ->will($this->returnValue('Something else'));
    $term2->expects($this->any())
      ->method('id')
      ->will($this->returnValue(2));

    $forum_manager = $this->getMock('Drupal\forum\ForumManagerInterface');
    $forum_manager->expects($this->at(0))
      ->method('getParents')
      ->will($this->returnValue(array($term1)));
    $forum_manager->expects($this->at(1))
      ->method('getParents')
      ->will($this->returnValue(array($term1, $term2)));

    // The root forum.
    $vocab_item = $this->getMock('Drupal\taxonomy\VocabularyInterface');
    $vocab_item->expects($this->any())
      ->method('label')
      ->will($this->returnValue('Fora_is_the_plural_of_forum'));
    $vocab_storage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $vocab_storage->expects($this->any())
      ->method('load')
      ->will($this->returnValueMap(array(
        array('forums', $vocab_item),
      )));

    $entity_manager = $this->getMockBuilder('Drupal\Core\Entity\EntityManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $entity_manager->expects($this->any())
      ->method('getStorage')
      ->will($this->returnValueMap(array(
        array('taxonomy_vocabulary', $vocab_storage),
      )));

    $config_factory = $this->getConfigFactoryStub(
      array(
        'forum.settings' => array(
          'vocabulary' => 'forums',
        ),
      )
    );

    // Build a breadcrumb builder to test.
    $breadcrumb_builder = $this->getMock(
      'Drupal\forum\Breadcrumb\ForumListingBreadcrumbBuilder', NULL, array(
        $entity_manager,
        $config_factory,
        $forum_manager,
      )
    );

    // Add a translation manager for t().
    $translation_manager = $this->getStringTranslationStub();
    $breadcrumb_builder->setStringTranslation($translation_manager);

    // The forum listing we need a breadcrumb back from.
    $forum_listing = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->getMock();
    $forum_listing->tid = 23;
    $forum_listing->expects($this->any())
      ->method('label')
      ->will($this->returnValue('You_should_not_see_this'));

    // Our data set.
    $route_match = $this->getMock('Drupal\Core\Routing\RouteMatchInterface');
    $route_match->expects($this->exactly(2))
      ->method('getParameter')
      ->with('taxonomy_term')
      ->will($this->returnValue($forum_listing));

    // First test.
    $expected1 = array(
      Link::createFromRoute('Home', '<front>'),
      Link::createFromRoute('Fora_is_the_plural_of_forum', 'forum.index'),
      Link::createFromRoute('Something', 'forum.page', array('taxonomy_term' => 1)),
    );
    $this->assertEquals($expected1, $breadcrumb_builder->build($route_match));

    // Second test.
    $expected2 = array(
      Link::createFromRoute('Home', '<front>'),
      Link::createFromRoute('Fora_is_the_plural_of_forum', 'forum.index'),
      Link::createFromRoute('Something else', 'forum.page', array('taxonomy_term' => 2)),
      Link::createFromRoute('Something', 'forum.page', array('taxonomy_term' => 1)),
    );
    $this->assertEquals($expected2, $breadcrumb_builder->build($route_match));
  }

}
