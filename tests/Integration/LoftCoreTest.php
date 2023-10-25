<?php

namespace Drupal\Tests\loft_core\Integration;

use Drupal\loft_core\Attribute;
use Drupal\loft_core_users\Utility\LoftCoreUsers;
use PHPUnit\Framework\TestCase;

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

}
