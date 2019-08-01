<?php

namespace Drupal\Test\loft_core\Unit;

use AKlump\PHPUnit\EasyMockTrait;
use PHPUnit\Framework\TestCase;

class BreadcrumbMutatorUnitTest extends TestCase {

  use EasyMockTrait;

  /**
   * @expectedException OutOfRangeException
   */
  public function testAddLinkWithBadPositionThrows() {
    $this->obj
      ->at(12)
      ->replaceLink($this->getMockLink('chocolate'));
  }

  /**
   * @expectedException OutOfRangeException
   */
  public function testRemoveLinkWithBadPositionThrows() {
    $this->obj
      ->at(12)
      ->replaceLink($this->getMockLink('chocolate'));
  }

  /**
   * @expectedException OutOfRangeException
   */
  public function testReplaceLinkWithBadPositionThrows() {
    $this->obj
      ->at(12)
      ->replaceLink($this->getMockLink('chocolate'));
  }

  public function testReplaceLinkAtEndAffectsLast() {
    $links = $this->obj
      ->atEnd()
      ->replaceLink($this->getMockLink('chocolate'))
      ->get()
      ->getLinks();
    $this->assertCount(3, $links);
    $this->assertSame('alpha', $links[0]->getText());
    $this->assertSame('bravo', $links[1]->getText());
    $this->assertSame('chocolate', $links[2]->getText());
  }

  public function testReplaceLinkWithoutPositionAffectsLast() {
    $links = $this->obj
      ->replaceLink($this->getMockLink('chocolate'))
      ->get()
      ->getLinks();
    $this->assertCount(3, $links);
    $this->assertSame('alpha', $links[0]->getText());
    $this->assertSame('bravo', $links[1]->getText());
    $this->assertSame('chocolate', $links[2]->getText());
  }

  public function testReplaceLinkAtAffectsCorrectItem() {
    $links = $this->obj
      ->at(2)
      ->replaceLink($this->getMockLink('banana'))
      ->get()
      ->getLinks();
    $this->assertCount(3, $links);
    $this->assertSame('alpha', $links[0]->getText());
    $this->assertSame('banana', $links[1]->getText());
    $this->assertSame('charlie', $links[2]->getText());
  }

  public function testRemoveLinkAtRemovesCorrectItem() {
    $links = $this->obj
      ->at(2)
      ->removeLink()
      ->get()
      ->getLinks();
    $this->assertCount(2, $links);
    $this->assertSame('alpha', $links[0]->getText());
    $this->assertSame('charlie', $links[1]->getText());
  }

  public function testRemoveLinkAtEndPopsStack() {
    $links = $this->obj
      ->atEnd()
      ->removeLink()
      ->get()
      ->getLinks();
    $this->assertCount(2, $links);
    $this->assertSame('alpha', $links[0]->getText());
    $this->assertSame('bravo', $links[1]->getText());
  }

  public function testRemoveLinkWithoutPositionPopsStack() {
    $links = $this->obj
      ->removeLink()
      ->get()
      ->getLinks();
    $this->assertCount(2, $links);
    $this->assertSame('alpha', $links[0]->getText());
    $this->assertSame('bravo', $links[1]->getText());
  }

  public function testAddLinkAppendsToEndWithAtEnd() {
    $links = $this->obj
      ->atEnd()
      ->addLink($this->getMockLink('delta'))
      ->get()
      ->getLinks();
    $this->assertCount(4, $links);
    $link = array_pop($links);
    $this->assertSame('delta', $link->getText());
  }

  public function testAddLinkAppendsToEndWithoutPositionMethod() {
    $links = $this->obj
      ->addLink($this->getMockLink('delta'))
      ->get()
      ->getLinks();
    $this->assertCount(4, $links);
    $link = array_pop($links);
    $this->assertSame('delta', $link->getText());
  }

  public function testAddLinkInsertsBeforeFirstWhenUsingBefore() {
    $links = $this->obj
      ->before(1)
      ->addLink($this->getMockLink('delta'))
      ->get()
      ->getLinks();
    $this->assertCount(4, $links);
    $link = array_shift($links);
    $this->assertSame('delta', $link->getText());
  }

  public function testAddLinkInsertsAfterFirstWhenUsingAfter() {
    $links = $this->obj
      ->after(1)
      ->addLink($this->getMockLink('delta'))
      ->get()
      ->getLinks();
    $this->assertCount(4, $links);
    $link = array_shift($links);
    $link = array_shift($links);
    $this->assertSame('delta', $link->getText());
  }

  //  public function testReplaceLink() {
  //    $this->assertTrue(FALSE);
  //
  //    }

  public function testConstructor() {
    // Use this assertion to quickly make sure constructor works as expected.
    $this->assertConstructorSetsInternalProperties();
  }


  protected function getSchema() {
    return [
      'classToBeTested' => '\Drupal\loft_core\BreadcrumbMutator',
      'classArgumentsMap' => [
        'breadcrumb' => function () {
          $links = [];
          $links[] = $this->getMockLink('alpha');
          $links[] = $this->getMockLink('bravo');
          $links[] = $this->getMockLink('charlie');

          $obj = \Mockery::mock('\Drupal\Core\Breadcrumb\Breadcrumb');
          $obj->allows('getLinks')->andReturns($links);
          $obj->allows('getCacheContexts')->andReturn([]);
          $obj->allows('addCacheTags');
          $obj->allows('mergeCacheMaxAge');
          $obj->allows('getCacheMaxAge');

          return $obj;
        },
      ],
      'mockObjectsMap' => [
        'link' => '\Drupal\Core\Link',
      ],
    ];
  }

  private function getMockLink(string $text) {
    $link = \Mockery::mock('\Drupal\Core\Link');
    $link->allows('getText')->andReturns($text);

    return $link;
  }
}
