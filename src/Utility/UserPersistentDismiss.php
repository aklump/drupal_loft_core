<?php

namespace Drupal\loft_core\Utility;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Template\Attribute;
use Drupal\loft_core\Loft;

/**
 * Provides a means for users to persistently dismiss things.
 *
 * It uses browser storage and doesn't care if the user is anonymous or
 * authenticated as it doesn't rely on user accounts or IDs.
 *
 * YOU MUST ENSURE THE LIBRARY: CORE/JS-COOKIE TO REQUIRED WHEN USING THIS.
 */
class UserPersistentDismiss implements CacheableDependencyInterface {

  /**
   * @var string
   */
  private $id;

  /**
   * @var \DateTime|null
   */
  private $expiresOn;

  /**
   * Create a new instance for tracking an item.
   *
   * @param string $id
   *   The identifier for the item being dismissed.  It must not contain a comma.
   * @param \DateTime|null
   *   An expiration date to use to limit the hide lifetime.  You may omit this
   *   to use the default.
   */
  public function __construct(string $id, ?\DateTime $expires_on) {
    if (strstr($id, ',') !== FALSE) {
      throw new \InvalidArgumentException('$id may not contain a comma.');
    }
    $this->id = $id;
    $this->expiresOn = $expires_on;
  }

  protected function getCookieName(): string {
    return 'Drupal_visitor_HideMessage';
  }

  protected function getCookieValue(): string {
    $csv = \Drupal::request()->cookies->all()[$this->getCookieName()] ?? '';
    $value = explode(',', $csv);
    if (!in_array($this->id, $value)) {
      $value[] = $this->id;
    }
    sort($value);

    return implode(',', $value);
  }

  public function isDismissed(): bool {
    $csv = \Drupal::request()->cookies->all()[$this->getCookieName()] ?? '';
    $value = explode(',', $csv);

    return in_array($this->id, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [
      'cookies:' . $this->getCookieName(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  protected function getCookieDurationInDays(): string {
    if (empty($this->expiresOn)) {
      return 365;
    }
    $days = $this->expiresOn->diff(date_create())->days;
    // Give an extra day to account for time, and just to be sure it doesn't pop
    // up in the "eleventh hour" and show itself.
    $days += 1;

    return max(-1, $days);
  }

  /**
   * @return void
   *
   * @see \user_cookie_save()
   */
  public function dismiss(): void {
    $expires = \Drupal::time()
        ->getRequestTime() + ($this->getCookieDurationInDays() * 86400);
    setrawcookie($this->getCookieName(), rawurlencode($this->getCookieValue()), $expires, '/');
  }

  /**
   * Get HTML attributes for Javascript dismissal.
   *
   * This provides the attributes for a button or link that will set the
   * appropriate cookie to hide this item via javascript.
   *
   * @return \Drupal\Core\Template\Attribute
   *   An attribute object providing the onclick cookie setting.
   *
   * This is an example for using this with an FEC and a default value.
   * @code
   * $fec = \Drupal\front_end_components\FEC::component('message');
   * $dismiss = new UserPersistentDismiss($dismiss_id);
   * $build['#dismiss_attributes'] = $fec->default('attributes', $dismiss
   *   ->getJavascriptDismiss()
   *   ->addClass('js-header-height')
   *   ->toArray());
   * @endcode
   */
  public function getJavascriptDismiss(): Attribute {
    // @see user_cookie_save; it uses dot-separation so we're going to be
    // consistent.  The reason we don't use that function is because expiration
    // is not configurable if used.
    $js_cookie_name = str_replace('_', '.', $this->getCookieName());

    return new Attribute([
      'data-test-cookie' => $this->getCookieName() . '=' . $this->getCookieValue(),
      'onclick' => sprintf("window.Cookies.set('%s','%s',{expires:%d})",
        $js_cookie_name, $this->getCookieValue(), $this->getCookieDurationInDays()),
    ]);
  }

  /**
   * Helper to simply usage with a render array.
   *
   * @param array &$build
   *   A render array.
   */
  public function applyTo(array &$build) {
    $build['#access'] = !$this->isDismissed();
    $build['#attached']['library'][] = 'core/js-cookie';
    $dismiss_cache = CacheableMetadata::createFromObject($this);
    CacheableMetadata::createFromRenderArray($build)
      ->merge($dismiss_cache)
      ->applyTo($build);
    $build = Loft::renderAccess($build, $build['#access']);
  }

}
