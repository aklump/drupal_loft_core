<?php

namespace Drupal\loft_core\Utility;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Template\Attribute;
use Drupal\loft_core\Loft;

/**
 * Provides a means for users to persistently dismiss DOM elements.
 *
 * It uses browser storage and doesn't care if the user is anonymous or
 * authenticated as it doesn't rely on user accounts or IDs.
 *
 * Cookies are strange these days.  It seems that the maximum duration for
 * setting via JS is 7 days.  But when setting with PHP it's one year.  This is
 * as of Sep 21, 2022.  This class has a built in extension mechanism to try and
 * persist the cookies longer see ::getCookieValue().
 * @url https://www.cookiestatus.com/
 *
 * YOU MUST ENSURE THE LIBRARY: CORE/JS-COOKIE TO REQUIRED WHEN USING THIS.
 *
 * @see ::applyTo()
 *
 * @code
 * // Add code to a button render array that will dismiss when clicked.
 * $expiry = date_create()->add(new \DateInterval("P30D"));
 * $dismiss = new UserPersistentDismiss('Foo_123', $expiry);
 * Core::ensureAttribute($build, '#attributes');
 * $button['#attributes']->merge($dismiss->getJavascriptDismiss());
 * $dismiss->applyTo($button);
 * @endcode
 */
final class UserPersistentDismiss implements CacheableDependencyInterface {

  /**
   * Stores cookie names that have been renewed.
   *
   * @var array
   */
  private static $renewed = [];

  /**
   * @var string
   */
  private $id;

  /**
   * @var \DateTime|null
   */
  private $expiresOn;

  /**
   * @var string
   */
  private $value;

  /**
   * Create a new instance for tracking an item.
   *
   * @param string $id
   *   The identifier for the item being dismissed.  It must not contain a comma.
   * @param \DateTime|null
   *   An expiration date to use to limit the hide lifetime.  You may omit this
   *   to use the default, which is one year from now.
   */
  public function __construct(string $id, \DateTime $expires_on = NULL) {
    if (strstr($id, '.') !== FALSE) {
      throw new \InvalidArgumentException('$id may not contain a period.');
    }
    $this->id = $id;
    $this->expiresOn = $expires_on;
  }

  public function getId(): string {
    return $this->id;
  }

  protected function getCookieNameForReading(): string {
    return 'Drupal_visitor_UserPersistentDismiss_' . $this->id;
  }

  protected function getCookieNameForSetting(): string {
    // @see user_cookie_save; it uses dot-separation so we're going to be
    // consistent.  The reason we don't use that function is because expiration
    // is not configurable and we need to be able to configure the expiry.
    return str_replace('_', '.', $this->getCookieNameForReading());
  }

  /**
   * @return string
   *   Two timestamps: {start}.{end}
   */
  protected function getCookieValue(): string {
    if (empty($this->value)) {
      $cookie_name = $this->getCookieNameForReading();
      $saved_value = \Drupal::request()->cookies->all()[$cookie_name] ?? NULL;
      if ($saved_value) {
        $this->value = $saved_value;

        // Because browsers may cap the expiration date, we store the real
        // expiration as the cookie value and then continue to reset the cookie
        // using that expiration value.  This will give us our desired, longer
        // cookie duration.
        // @link https://www.cookiestatus.com/
        if (!in_array($cookie_name, self::$renewed)) {
          list(, $expires) = explode('.', $saved_value . '.');
          if (empty($expires)) {
            // Someone has fiddled with the cookie value, so we'll delete and
            // start over.  The expected format includes a dot and expiry.
            $expires = time() - 86400;
          }
          setrawcookie($this->getCookieNameForSetting(), $saved_value, $expires, '/');
          self::$renewed[] = $cookie_name;
        }
      }
      else {
        // No stored value so we'll go with the default, which is based on the
        // expiration date passed to the constructor.
        if (empty($this->expiresOn)) {
          $days = $this->getCookieDurationInDays();
          $this->expiresOn = date_create()->add(new \DateInterval("P{$days}D"));
        }

        $this->value = time() . '.' . $this->expiresOn->getTimestamp();
      }
    }

    return $this->value;
  }

  /**
   * Check if this has already been dismissed by the user.
   *
   * @return bool
   *   False if the user has not yet dismissed this message.
   */
  public function isDismissed(): bool {
    // We have to call this in order to cause the renewal to take place...
    $this->getCookieValue();

    // ... but the answer to this method is if the cookie exists or not.
    return array_key_exists($this->getCookieNameForReading(), \Drupal::request()->cookies->all());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [
      'cookies:' . $this->getCookieNameForReading(),
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

  protected function getCookieDurationInDays(): int {
    if (empty($this->expiresOn)) {
      return 365;
    }
    $days = $this->expiresOn->diff(date_create())->days;
    // Give an extra day to account for time, and just to be sure it doesn't pop
    // up in the "eleventh hour" and show itself.
    $days += 1;

    return intval(max(-1, $days));
  }

  /**
   * Mark this message dismissed from the server-side.
   *
   * @return void
   *
   * @see \user_cookie_save()
   */
  public function dismiss(): void {
    $expires = \Drupal::time()
        ->getRequestTime() + ($this->getCookieDurationInDays() * 86400);
    setrawcookie($this->getCookieNameForSetting(), rawurlencode($this->getCookieValue()), $expires, '/');
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
    return new Attribute([
      'data-test-cookie' => $this->getCookieNameForReading() . '=' . $this->getCookieValue(),
      'onclick' => sprintf("window.Cookies.set('%s','%s',{expires:%d})",
        $this->getCookieNameForSetting(), $this->getCookieValue(), $this->getCookieDurationInDays()),
    ]);
  }

  /**
   * Helper to simplify usage with a render array.
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
