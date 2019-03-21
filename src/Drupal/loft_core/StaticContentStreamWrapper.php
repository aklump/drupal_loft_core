<?php

namespace Drupal\loft_core;

use DrupalLocalStreamWrapper;

/**
 * Provide a stream wrapper to load static content.
 *
 * An example of static content is an HTML document that lays out the terms of
 * service, which you do not want exposed to the Drupal node system, but rather
 * is programmatically appended to a page.  The static content should be added
 * to SCM as well.
 */
class StaticContentStreamWrapper extends DrupalLocalStreamWrapper {

  /**
   * The base URL for static content when creating public URLs.
   *
   * @var string
   */
  const URL_BASE = 'system/content';

  /**
   * The default system path to static content files relative to DRUPAL_ROOT.
   *
   * @var string
   */
  const DEFAULT_PATH = '../private/default/content';

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    return rtrim(variable_get('file_static_content_path', DRUPAL_ROOT . '/' . self::DEFAULT_PATH), '/') . '/';
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $path = str_replace('\\', '/', $this->getTarget());

    return url(self::URL_BASE . "/$path", array('absolute' => TRUE));
  }

}
