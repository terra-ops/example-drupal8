<?php

/**
 * @file
 * Contains \Drupal\views\Tests\Plugin\StyleTest.
 */

namespace Drupal\views\Tests\Plugin;

use Drupal\views\Views;
use Drupal\views\Tests\ViewTestBase;
use Drupal\views_test_data\Plugin\views\row\RowTest;
use Drupal\views\Plugin\views\row\Fields;
use Drupal\views\ResultRow;
use Drupal\views_test_data\Plugin\views\style\StyleTest as StyleTestPlugin;

/**
 * Tests general style functionality.
 *
 * @group views
 * @see \Drupal\views_test_data\Plugin\views\style\StyleTest.
 */
class StyleTest extends ViewTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('test_view');

  /**
   * Stores the SimpleXML representation of the output.
   *
   * @var \SimpleXMLElement
   */
  protected $elements;

  protected function setUp() {
    parent::setUp();

    $this->enableViewsTestModule();
  }

  /**
   * Tests the general rendering of styles.
   */
  public function testStyle() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');

    // This run use the test row plugin and render with it.
    $view = Views::getView('test_view');
    $view->setDisplay();
    $style = $view->display_handler->getOption('style');
    $style['type'] = 'test_style';
    $view->display_handler->setOption('style', $style);
    $row = $view->display_handler->getOption('row');
    $row['type'] = 'test_row';
    $view->display_handler->setOption('row', $row);
    $view->initDisplay();
    $view->initStyle();
    // Reinitialize the style as it supports row plugins now.
    $view->style_plugin->init($view, $view->display_handler);
    $this->assertTrue($view->rowPlugin instanceof RowTest, 'Make sure the right row plugin class is loaded.');

    $random_text = $this->randomMachineName();
    $view->rowPlugin->setOutput($random_text);

    $output = $view->preview();
    $output = $renderer->renderRoot($output);
    $this->assertTrue(strpos($output, $random_text) !== FALSE, 'Make sure that the rendering of the row plugin appears in the output of the view.');

    // Test without row plugin support.
    $view = Views::getView('test_view');
    $view->setDisplay();
    $style = $view->display_handler->getOption('style');
    $style['type'] = 'test_style';
    $view->display_handler->setOption('style', $style);
    $view->initDisplay();
    $view->initStyle();
    $view->style_plugin->setUsesRowPlugin(FALSE);
    $this->assertTrue($view->style_plugin instanceof StyleTestPlugin, 'Make sure the right style plugin class is loaded.');
    $this->assertTrue($view->rowPlugin instanceof Fields, 'Make sure that rowPlugin is now a fields instance.');

    $random_text = $this->randomMachineName();
    // Set some custom text to the output and make sure that this value is
    // rendered.
    $view->style_plugin->setOutput($random_text);
    $output = $view->preview();
    $output = $renderer->renderRoot($output);
    $this->assertTrue(strpos($output, $random_text) !== FALSE, 'Make sure that the rendering of the style plugin appears in the output of the view.');
  }

  function testGrouping() {
    $this->_testGrouping(FALSE);
    $this->_testGrouping(TRUE);
  }

  /**
   * Tests the grouping features of styles.
   */
  function _testGrouping($stripped = FALSE) {
    $view = Views::getView('test_view');
    $view->setDisplay();
    // Setup grouping by the job and the age field.
    $view->initStyle();
    $view->style_plugin->options['grouping'] = array(
      array('field' => 'job'),
      array('field' => 'age'),
    );

    // Reduce the amount of items to make the test a bit easier.
    // Set up the pager.
    $view->displayHandlers->get('default')->overrideOption('pager', array(
      'type' => 'some',
      'options' => array('items_per_page' => 3),
    ));

    // Add the job and age field.
    $view->displayHandlers->get('default')->overrideOption('fields', array(
      'name' => array(
        'id' => 'name',
        'table' => 'views_test_data',
        'field' => 'name',
        'relationship' => 'none',
        'label' => 'Name',
      ),
      'job' => array(
        'id' => 'job',
        'table' => 'views_test_data',
        'field' => 'job',
        'relationship' => 'none',
        'label' => 'Job',
      ),
      'age' => array(
        'id' => 'age',
        'table' => 'views_test_data',
        'field' => 'age',
        'relationship' => 'none',
        'label' => 'Age',
      ),
    ));

    // Now run the query and groupby the result.
    $this->executeView($view);

    $expected = array();
    $expected['Job: Singer'] = array();
    $expected['Job: Singer']['group'] = 'Job: Singer';
    $expected['Job: Singer']['rows']['Age: 25'] = array();
    $expected['Job: Singer']['rows']['Age: 25']['group'] = 'Age: 25';
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0] = new ResultRow(['index' => 0]);
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_data_name = 'John';
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_data_job = 'Singer';
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_data_age = '25';
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_data_id = '1';
    $expected['Job: Singer']['rows']['Age: 27'] = array();
    $expected['Job: Singer']['rows']['Age: 27']['group'] = 'Age: 27';
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1] = new ResultRow(['index' => 1]);
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_data_name = 'George';
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_data_job = 'Singer';
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_data_age = '27';
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_data_id = '2';
    $expected['Job: Drummer'] = array();
    $expected['Job: Drummer']['group'] = 'Job: Drummer';
    $expected['Job: Drummer']['rows']['Age: 28'] = array();
    $expected['Job: Drummer']['rows']['Age: 28']['group'] = 'Age: 28';
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2] = new ResultRow(['index' => 2]);
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_data_name = 'Ringo';
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_data_job = 'Drummer';
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_data_age = '28';
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_data_id = '3';


    // Alter the results to support the stripped case.
    if ($stripped) {

      // Add some html to the result and expected value.
      $rand = '<a data="' . $this->randomMachineName() . '" />';
      $view->result[0]->views_test_data_job .= $rand;
      $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_data_job = 'Singer' . $rand;
      $expected['Job: Singer']['group'] = 'Job: Singer';
      $rand = '<a data="' . $this->randomMachineName() . '" />';
      $view->result[1]->views_test_data_job .= $rand;
      $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_data_job = 'Singer' . $rand;
      $rand = '<a data="' . $this->randomMachineName() . '" />';
      $view->result[2]->views_test_data_job .= $rand;
      $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_data_job = 'Drummer' . $rand;
      $expected['Job: Drummer']['group'] = 'Job: Drummer';

      $view->style_plugin->options['grouping'][0] = array('field' => 'job', 'rendered' => TRUE, 'rendered_strip' => TRUE);
      $view->style_plugin->options['grouping'][1] = array('field' => 'age', 'rendered' => TRUE, 'rendered_strip' => TRUE);
    }


    // The newer api passes the value of the grouping as well.
    $sets_new_rendered = $view->style_plugin->renderGrouping($view->result, $view->style_plugin->options['grouping'], TRUE);

    $this->assertEqual($sets_new_rendered, $expected, 'The style plugins should properly group the results with grouping by the rendered output.');

    // Don't test stripped case, because the actual value is not stripped.
    if (!$stripped) {
      $sets_new_value = $view->style_plugin->renderGrouping($view->result, $view->style_plugin->options['grouping'], FALSE);

      // Reorder the group structure to grouping by value.
      $expected['Singer'] = $expected['Job: Singer'];
      $expected['Singer']['rows']['25'] = $expected['Job: Singer']['rows']['Age: 25'];
      $expected['Singer']['rows']['27'] = $expected['Job: Singer']['rows']['Age: 27'];
      $expected['Drummer'] = $expected['Job: Drummer'];
      $expected['Drummer']['rows']['28'] = $expected['Job: Drummer']['rows']['Age: 28'];
      unset($expected['Job: Singer']);
      unset($expected['Singer']['rows']['Age: 25']);
      unset($expected['Singer']['rows']['Age: 27']);
      unset($expected['Job: Drummer']);
      unset($expected['Drummer']['rows']['Age: 28']);

      $this->assertEqual($sets_new_value, $expected, 'The style plugins should proper group the results with grouping by the value.');
    }
  }

  /**
   * Tests custom css classes.
   */
  function testCustomRowClasses() {
    $view = Views::getView('test_view');
    $view->setDisplay();

    // Setup some random css class.
    $view->initStyle();
    $random_name = $this->randomMachineName();
    $view->style_plugin->options['row_class'] = $random_name . " test-token-{{ name }}";

    $output = $view->preview();
    $this->storeViewPreview(\Drupal::service('renderer')->renderRoot($output));

    $rows = $this->elements->body->div->div->div;
    $count = 0;
    foreach ($rows as $row) {
      $attributes = $row->attributes();
      $class = (string) $attributes['class'][0];
      $this->assertTrue(strpos($class, $random_name) !== FALSE, 'Make sure that a custom css class is added to the output.');

      // Check token replacement.
      $name = $view->field['name']->getValue($view->result[$count]);
      $this->assertTrue(strpos($class, "test-token-$name") !== FALSE, 'Make sure that a token in custom css class is replaced.');

      $count++;
    }
  }

  /**
   * Stores a view output in the elements.
   */
  protected function storeViewPreview($output) {
    $htmlDom = new \DOMDocument();
    @$htmlDom->loadHTML($output);
    if ($htmlDom) {
      // It's much easier to work with simplexml than DOM, luckily enough
      // we can just simply import our DOM tree.
      $this->elements = simplexml_import_dom($htmlDom);
    }
  }

}
