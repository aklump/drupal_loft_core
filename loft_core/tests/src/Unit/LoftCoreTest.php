<?php

use Drupal\loft_core\Attribute;
use Drupal\loft_core_users\Utility\LoftCoreUsers;
use PHPUnit\Framework\TestCase;
use Drupal\loft_core_users\Utility\Core;
use Drupal\loft_core_testing\Component\Utility\TestingMarkup;

class LoftCoreTest extends TestCase {

  /**
   * Provides data for test_loft_core_users_get_email_domain.
   */
  function DataForTest_loft_core_users_get_email_domainProvider() {
    $tests = array();
    $tests[] = array(
      'brainboostingsupplements.org',
      'heath@e.brainboostingsupplements.org',
    );
    $tests[] = array(
      'brainboostingsupplements.org',
      'brainboostingsupplements.org',
    );

    return $tests;
  }

  /**
   * @dataProvider DataForTest_loft_core_users_get_email_domainProvider
   */
  public function test_loft_core_users_get_email_domain($control, $mail) {
    $core = new LoftCoreUsers();
    $this->assertSame($control, $core->getEmailDomain($mail));
  }

  public function testLoftCoreTestClass() {
    $this->assertSame('t-golden', TestingMarkup::id('golden'));
  }

  /**
   * Provides data for testLoftCoreCl.
   */
  function DataForTestLoftCoreClProvider() {
    $tests = array();
    $tests[] = array(
      'do',
      're',
      'do__re',
      'do--re',
    );
    $tests[] = array(
      'a_list_apart',
      'chapter-one-thing',
      'a-list-apart__chapter-one-thing',
      'a-list-apart--chapter-one-thing',
    );

    return $tests;
  }

  /**
   * @dataProvider DataForTestLoftCoreClProvider
   */
  public function testLoftCoreCl($base, $subject, $control_component, $control_version) {
    $cl = loft_core_cl($base);
    $this->assertSame($control_component, $cl($subject));
    $this->assertSame($control_component, $cl($subject, TRUE));
    $this->assertSame($control_version, $cl($subject, FALSE));
  }

  public function testLoftCoreTabIndexWithObject() {
    $tabindex = $ti_control = 100;
    $el = array('#attributes' => new Attribute());
    $control = array('tabindex' => 100);
    loft_core_form_tabindex($el, $tabindex);
    $this->assertSame($control, $el['#attributes']->toArray());
    $this->assertSame($ti_control + 1, $tabindex);
  }

  public function testLoftCoreTabIndexWithArray() {
    $tabindex = $ti_control = 100;
    $el = array();
    $control = array(
      '#attributes' => array('tabindex' => 100),
    );
    loft_core_form_tabindex($el, $tabindex);
    $this->assertSame($control, $el);
    $this->assertSame($ti_control + 1, $tabindex);
  }

  public function testRemovePartOfStyle() {
    $attributes = new Attribute(array('style' => 'background-repeat:repeat;color:red;'));
    $attributes->removeStyle('background-repeat');
    $this->assertSame(' style="color:red"', strval($attributes));
    $this->assertTrue($attributes->hasStyle('color'));


  }

  public function testRemoveStyle() {
    $attributes = new Attribute(array('style' => 'background-repeat:repeat'));
    $return = $attributes->removeStyle('background-repeat');
    $this->assertEmpty(strval($attributes));
    $this->assertSame($attributes, $return);
  }

  public function testAddStyleReplace() {
    $attributes = new Attribute(array('style' => 'background-repeat:repeat'));
    $attributes->addStyle('background-repeat', 'no-repeat');
    $this->assertSame(' style="background-repeat:no-repeat"', strval($attributes));
  }

  public function testAddStyle() {
    $attributes = new Attribute;
    $return = $attributes->addStyle('background-repeat', 'no-repeat');
    $this->assertSame(' style="background-repeat:no-repeat"', strval($attributes));
    $this->assertSame($attributes, $return);
  }

  /**
   * Provides data for testMinWeight.
   */
  function DataForTestWeightProvider() {
    $tests = array();
    $tests[] = array(
      -10,
      9,
      array(
        array('#weight' => 0),
        array('#weight' => 9),
        array('#weight' => -5),
        array('#weight' => -10),
      ),
    );

    return $tests;
  }

  /**
   * @dataProvider DataForTestWeightProvider
   */
  public function testMaxWeight($min, $max, $subject) {
    $this->assertSame($max, loft_core_max_weight($subject));
  }

  /**
   * @dataProvider DataForTestWeightProvider
   */
  public function testMinWeight($min, $max, $subject) {
    $this->assertSame($min, loft_core_min_weight($subject));
  }

}
