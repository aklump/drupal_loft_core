<?php

namespace Drupal\loft_core\Service;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;

/**
 * Functions for working with images.
 */
class ImageService {

  /**
   * Return HTML markup for an image uri.
   *
   * In the case of SVG, this is the SVG code itself.
   *
   * @param string $uri
   *   The URI of the image.
   *
   * @return string
   *   The markup representing the image.
   *
   * @throws \InvalidArgumentException
   *   If the $uri is not understood.
   */
  public function getMarkup($uri) {
    if (empty(trim($uri))) {
      return '';
    }
    $extension = pathinfo($uri, PATHINFO_EXTENSION);
    if ($extension === 'svg') {
      $dom = new \DOMDocument();
      $dom->load($uri);

      // Strip XML header.
      $svg = '';
      foreach ($dom->childNodes as $node) {
        $svg .= $dom->saveXML($node);
      }

      // Strip comments.
      $svg = preg_replace("/<!--.+?-->/", '', $svg);
      $svg = $this->filterXssSvg($svg);
    }
    else {
      throw new \RuntimeException("Unsupported filetype: $extension");
    }

    return Markup::create($svg);
  }

  public function getBase64DataSrc($uri): string {
    if (empty(trim($uri))) {
      return '';
    }
    $path = parse_url($uri, PHP_URL_PATH);
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    switch ($extension) {
      case 'png':
        $mime = 'image/png';
        break;

      case 'jpg':
      case 'jpeg':
        $mime = 'image/jpeg';
        break;

      default:
        throw new \RuntimeException("URI is not yet supported.");
    }
    if (!file_exists($uri)) {
      throw new \InvalidArgumentException(sprintf('Provided URI: %s does not exist.', $uri));
    }

    return sprintf('data:%s;base64,%s', $mime, base64_encode(file_get_contents($uri)));
  }

  /**
   * Filters SVG markup removing unsafe tags.
   *
   * @param string $svg_markup
   *   The contents of an SVG file.
   *
   * @return string
   *   The filtered markup.
   *
   * @link https://developer.mozilla.org/en-US/docs/Web/SVG/Element
   */
  public function filterXssSvg($svg_markup) {
    return Xss::filter($svg_markup, [
      'a',
      'altGlyph',
      'altGlyphDef',
      'altGlyphItem',
      'animate',
      'animateColor',
      'animateMotion',
      'animateTransform',
      'circle',
      'clipPath',
      'color-profile',
      'cursor',
      'defs',
      'desc',
      'discard',
      'ellipse',
      'feBlend',
      'feColorMatrix',
      'feComponentTransfer',
      'feComposite',
      'feConvolveMatrix',
      'feDiffuseLighting',
      'feDisplacementMap',
      'feDistantLight',
      'feDropShadow',
      'feFlood',
      'feFuncA',
      'feFuncB',
      'feFuncG',
      'feFuncR',
      'feGaussianBlur',
      'feImage',
      'feMerge',
      'feMergeNode',
      'feMorphology',
      'feOffset',
      'fePointLight',
      'feSpecularLighting',
      'feSpotLight',
      'feTile',
      'feTurbulence',
      'filter',
      'font',
      'font-face',
      'font-face-format',
      'font-face-name',
      'font-face-src',
      'font-face-uri',
      'foreignObject',
      'g',
      'glyph',
      'glyphRef',
      'hatch',
      'hatchpath',
      'hkern',
      'image',
      'line',
      'linearGradient',
      'marker',
      'mask',
      'mesh',
      'meshgradient',
      'meshpatch',
      'meshrow',
      'metadata',
      'missing-glyph',
      'mpath',
      'path',
      'pattern',
      'polygon',
      'polyline',
      'radialGradient',
      'rect',
      'script',
      'set',
      'solidcolor',
      'stop',
      'style',
      'svg',
      'switch',
      'symbol',
      'text',
      'textPath',
      'title',
      'tref',
      'tspan',
      'unknown',
      'use',
      'view',
      'vkern',
    ]);
  }


  /**
   * Copy a remote image to a local, temporary file.
   *
   * @param string $remote_url
   *   The remote URL of the image.
   *
   * @return \stdClass|false
   *   The temporary file instance.  You should use file_copy() with this as
   *   the first argument to save this to a permanent file.
   *
   * @code
   *   $local = \Drupal::service('gop.images')
   *     ->copyRemoteImageByUrl(...);
   *     $local = file_copy($local, 'public://images/' . $local->filename);
   * @endcode
   */
  public function copyRemoteImageByUrl($remote_url) {
    global $user;
    $extensions = 'jpg jpeg gif png';

    $info = explode('?', $remote_url);
    $info = pathinfo($info[0]);

    $file['uid'] = \Drupal::currentUser()->id();
    $file['status'] = 0;
    $file['filename'] = file_munge_filename($info['basename'], $extensions);
    $file['uri'] = 'temporary://' . $file['filename'];
    if (!copy($remote_url, $file['uri'])) {
      return FALSE;
    }

    $file['filemime'] = \Drupal::service('file.mime_type.guesser')
      ->guess($file['uri']);
    $file['filesize'] = filesize($file['uri']);

    return File::create($file);
  }

}
