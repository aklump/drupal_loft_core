<?php

namespace Drupal\loft_core\Utility;

use Drupal\Core\Cache\Cache;

/**
 * A utility to facility time-based cache tag expiration.
 *
 * If you need to invalidate cache tags in the future, you should use this
 * class to do so.  According to this article
 * https://www.drupal.org/docs/drupal-apis/cache-api/cache-max-age using
 * #cache.max-age does not always work for anonymous users, so they suggest
 * using a cron-based solution.  This class is such a solution.
 *
 * "Until these (and perhaps other) issues are resolved, beware that setting
 * max-age on a render array included in a page is insufficient to ensure that
 * anonymous users will see a new version after the max-age as elapsed... You
 * might also have more luck setting a custom cache tag on pages with
 * time-dependent content and invalidating those cache tags manually via
 * hook_cron()."
 *
 * HOW TO IMPLEMENT THIS SOLUTION:
 * 1. Add \Drupal\loft_core\Utility\ExpiringCacheTags::expire() to a hook_cron
 * implementation or other similar mechanism.
 * 2. In your code add something like the following when you need to schedule
 * tags to expire, such as when render caching something.
 *
 * @code
 *   function my_module_cron() {
 *     ExpiringCacheTags::expire();
 *   }
 *   ...
 *   $start_date = \Drupal::service('se.elections')->getLocalStartDate();
 *   $max_age = ExpiringCacheTags::maxAgeByDate($start_date);
 *   ExpiringCacheTags::add($election_cache_tags, $max_age);
 * @endcode
 */
class ExpiringCacheTags {

  /**
   * The Drupal configuration ID.
   *
   * @var string
   */
  private static $cid = 'loft_core.expiring_class_tags';

  /**
   * Set up some cache tags to expire in the future.
   *
   * @param array $cache_tags
   *   An array of cache tags to invalidate after $max_age seconds.
   * @param int $max_age
   *   The number of seconds until these tags should be cached.  Be aware that
   *   the tags are not guaranteed to expire at this time--and this will depend
   *   on your cron frequency--but only AFTER this time.
   */
  public static function add(array $cache_tags, int $max_age): void {
    $expiry = time() + $max_age;
    $records = \Drupal::configFactory()
      ->getEditable(self::$cid);
    $cache_tags = Cache::mergeTags($records->get($expiry) ?? [], $cache_tags);
    $records->set($expiry, $cache_tags)->save();
  }

  /**
   * Invalidate all expired cache items.
   *
   * This should be called from some time of time-based trigger such as a
   * hook_cron implementation.
   */
  public static function expire(): void {
    $settings = \Drupal::configFactory()
      ->getEditable(self::$cid);
    if (!($items = $settings->get() ?? [])) {
      return;
    }
    $needs_save = FALSE;
    foreach ($items as $expiry => $cache_tags) {
      if (time() >= $expiry) {
        Cache::invalidateTags($cache_tags);
        $settings->clear($expiry);
        $needs_save = TRUE;
      }
    }
    if ($needs_save) {
      $settings->save();
    }
  }

  /**
   * Deletes all records of scheduled invalidations.
   *
   * This does not invalidate the tags, you may call ::expire() before this if
   * you want or just rebuild the Drupal cache.
   */
  public static function removeAll() {
    \Drupal::configFactory()
      ->getEditable(self::$cid)
      ->delete();
  }

  /**
   * Convert a future date into a max age value.
   *
   * @param \DateTime|\Drupal\Core\Datetime\DrupalDateTime $date
   *   A datetime object presumably in the future.
   *
   * @return int
   *   The number of seconds between now and a future date, to be used as
   *   max-age.  If this is a negative number it means that $date
   *   has already passed.
   */
  public static function maxAgeByDate($date) {
    return $date->format('U') - date_create('now', $date->getTimezone())->format('U');
  }

}
