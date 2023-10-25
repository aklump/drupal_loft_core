<?php

namespace Drupal\Tests\loft_core\Integration;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\loft_core\Utility\BreadcrumbMutator;
use PHPUnit\Framework\TestCase;

class BreadcrumbMutatorUnitTest extends TestCase {

  public function testAddLinkWithBadPositionThrows() {
    $this->expectException(\OutOfRangeException::class);
    $this->obj
      ->at(12)
      ->replaceLink($this->getMockLink('chocolate'));
  }

  public function testRemoveLinkWithBadPositionThrows() {
    $this->expectException(\OutOfRangeException::class);
    $this->obj
      ->at(12)
      ->replaceLink($this->getMockLink('chocolate'));
  }

  public function testReplaceLinkWithBadPositionThrows() {
    $this->expectException(\OutOfRangeException::class);
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
    array_shift($links);
    $link = array_shift($links);
    $this->assertSame('delta', $link->getText());
  }

  protected function setUp(): void {
    $breadcrumb = $this->createConfiguredMock(Breadcrumb::class, [
      'getLinks' => [
        $this->getMockLink('alpha'),
        $this->getMockLink('bravo'),
        $this->getMockLink('charlie'),
      ],
      'getCacheContexts' => [],
    ]);
    $this->obj = new BreadcrumbMutator($breadcrumb);
  }

  private function getMockLink(string $text) {
    return $this->createConfiguredMock(Link::class, [
      'getText' => $text,
    ]);
  }
}
