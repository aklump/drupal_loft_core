<?php

namespace Drupal\loft_core\Utility;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Helper class for advanced modification of breadcrumb objects.
 *
 * Here are some example use cases:
 *
 * @code
 *   <?php
 *   $mutator = new BreadcrumbMutator($breadcrumb);
 *
 *   // Generate a new Link to add to the breadcrumb.
 *   $link = new \Drupal\Core\Link::createFromRoute(...);
 *
 *   // Add a new link between the first and second links.
 *   $mutator->after(1)->addLink($link);
 *
 *   // Add a new link before the first link.  Think unshift().
 *   $mutator->before(1)->addLink($link);
 *
 *   // Add a new link after the last link.
 *   $mutator->atEnd()->addLink($link);
 *   // Note the default position is the same as if atEnd() had been called,
 *   e.g.
 *   $mutator->addLink($link);
 *
 *   // Replace the first link with another.
 *   $mutator->at(1)->replaceLink($link);
 *
 *   // Replace the last link.
 *   $mutator->atEnd()->replaceLink($link);
 *
 *   // Remove the first link
 *   $mutator->at(1)->removeLink();
 *
 *   // Remove the last link.  Think "pop".
 *   $mutator->atEnd()->removeLink();
 *
 *   $breadcrumb = $mutator->get();
 * @endcode
 */
final class BreadcrumbMutator {

  /**
   * Represents the end position.
   *
   * @var int
   */
  const END = -1;

  /**
   * Working links array, this will be modified.
   *
   * @var \Drupal\Core\Link[]
   */
  private $links;

  /**
   * Original set of links to compare against.
   *
   * @var \Drupal\Core\Link[]
   */
  private $linksUnmodified;

  /**
   * The instance used to instantiate this.
   *
   * @var \Drupal\Core\Breadcrumb\Breadcrumb
   */
  private $breadcrumb;

  /**
   * Points the to link position.
   *
   * @var int
   */
  private $position = self::END;

  /**
   * BreadcrumbMutator constructor.
   *
   * @param \Drupal\Core\Breadcrumb\Breadcrumb $breadcrumb
   *   A breadcrumb instance to mutate.
   */
  public function __construct(Breadcrumb $breadcrumb) {
    $this->breadcrumb = $breadcrumb;
    $this->links = $breadcrumb->getLinks();
    $this->linksUnmodified = $breadcrumb->getLinks();
  }

  /**
   * Return the mutated breadcrumb object.
   *
   * @return \Drupal\Core\Breadcrumb\Breadcrumb
   *   This may not be a new object, if no transformations have taken place.
   *   Otherwise it will be a new instance.
   */
  public function get() {
    if ($this->linksUnmodified !== $this->links) {
      $new = new Breadcrumb();
      $new->addCacheContexts($this->breadcrumb->getCacheContexts())
        ->addCacheTags($this->breadcrumb->getCacheContexts())
        ->mergeCacheMaxAge($this->breadcrumb->getCacheMaxAge())
        ->setLinks(array_values($this->links));
      $this->breadcrumb = $new;
      unset($new);
    }

    return $this->breadcrumb;
  }

  /**
   * Set stack position.
   *
   * @param int $position
   *   The 1-based index.  This should proceed removeLink, replaceLink.
   *
   * @return $this
   *   Self for chaining.
   */
  public function at(int $position) {
    $this->validatePosition($position - 1);

    return $this;
  }

  /**
   * Set stack position to the end.
   *
   * @return $this
   *   Self for chaining.
   */
  public function atEnd() {
    $this->position = self::END;

    return $this;
  }

  /**
   * Set stack position to just after an link.
   *
   * @param int $position
   *   The 1-based index.  This should proceed addLink.
   *
   * @return $this
   *   Self for chaining.
   */
  public function after(int $position) {
    $this->validatePosition($position);

    return $this;
  }

  /**
   * Set stack position to just before an link.
   *
   * @param int $position
   *   The 1-based index.  This should proceed addLink.
   *
   * @return $this
   *   Self for chaining.
   */
  public function before(int $position) {
    $this->validatePosition($position - 1);

    return $this;
  }

  /**
   * Remove link at current position.
   */
  public function removeLink() {
    if ($this->position === self::END) {
      array_pop($this->links);
    }
    else {
      unset($this->links[$this->position]);
    }

    return $this;
  }

  /**
   * Replace an old link with a new.
   *
   * @param \Drupal\Core\Link $link
   *   A link that will replace the one at the current pointer location.
   *
   * @return $this
   */
  public function replaceLink(Link $link) {
    $position = $this->position === self::END ? count($this->links) - 1 : $this->position;
    $this->links[$position] = $link;

    return $this;
  }

  /**
   * Get the position of the link pointing to $url if it exists.
   *
   * @code
   *   // Add a link if it doesn't yet exist.
   *   $mutator = new BreadcrumbMutator($breadcrumb);
   *   $foo = \Drupal\Core\Url::fromRoute('foo.bar');
   *   if (!$mutator->hasLink($foo)) {
   *     $breadcrumb->addLink(new Link(t('Home' $foo->toUrl()));
   *   }
   * @endcode
   *
   * @param \Drupal\loft_core\Utility\Url $url
   *   The URL that you want to look for.
   *
   * @return FALSE|int
   *   False if the URL does not exist in the breadcrumb instance, otherwise
   *   the index, which can be use for at(), etc.
   */
  public function hasLink(Url $url) {
    $find = $url->toUriString();
    foreach ($this->breadcrumb->getLinks() as $delta => $item) {
      if ($item->getUrl()->toUriString() === $find) {
        return (int) $delta;
      }
    }

    return FALSE;
  }

  /**
   * Add a link.
   *
   * @param \Drupal\Core\Link $link
   *   A new link to add at the defined position.
   *
   * @return $this
   *   Self for chaining.
   *
   * @see self::before
   * @see self::after
   */
  public function addLink(Link $link) {
    if ($this->position === self::END) {
      $this->links[] = $link;
    }
    else {
      array_splice($this->links, $this->position, 0, [$link]);
    }

    return $this;
  }

  /**
   * Validates that $position is within range.
   *
   * @param int $position
   *   The position value to validate.
   *
   * @return $this
   *
   * @throws \OutOfRangeException
   *   If the position is invalid.
   */
  private function validatePosition(int $position) {
    if ($position > ($c = count($this->links))) {
      throw new \OutOfRangeException("\$position cannot be greater than %s", $c);
    }
    $this->position = $position;

    return $this;
  }

}
