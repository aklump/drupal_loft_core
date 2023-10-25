<?php

namespace Drupal\Tests\loft_core\Integration;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\State\StateInterface;
use Drupal\loft_core_testing\Component\Utility\TestingMarkup;

class TestingMarkupTest extends \PHPUnit\Framework\TestCase {

  public function testClassIsPresentBasedOnExpiryState() {
    $this->setStateExpiryTo(time() + 86400);
    $result = TestingMarkup::id('golden');
    $this->assertSame('t-golden', $result);
  }

  public function testClassIsEmptyForLive() {
    $this->setStateExpiryTo('');
    $result = TestingMarkup::id('golden');
    $this->assertSame('', $result);
  }

  private function setStateExpiryTo($value) {
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
    $state = $this->createConfiguredMock(StateInterface::class, [
      'get' => $value,
      'delete' => '',
    ]);
    $container->set('state', $state);
  }

  protected function setUp(): void {
    TestingMarkup::$isTestingFlag = NULL;
  }


}
