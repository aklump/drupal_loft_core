<?php

namespace Drupal\loft_core\Service;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Functions for working with images.
 */
class ImageService {

  /**
   * Adds image_src as an web-accessible URL using image_uri/style_name.
   *
   * @param array $vars
   *   An array of preprocess variables.
   *   - image_uri REQUIRED
   *   - style_name OPTIONAL
   *   - image_src FORBIDDEN.
   */
  public function preprocessImageVars(array &$vars) {
    if (empty($vars['image_uri'])) {
      throw new \InvalidArgumentException("Missing key image_uri");
    }
    if (!empty($vars['image_src'])) {
      throw new \InvalidArgumentException("image_src is already set; it must be empty to use this method.");
    }
    if (!empty($vars['style_name'])) {
      if (!($style = ImageStyle::load($vars['style_name']))) {
        throw new \InvalidArgumentException(sprintf('Invalid image style: %s', $vars['style_name']));
      }
      $vars['image_src'] = $style->buildUrl($vars['image_uri']);
    }
    else {
      $vars['image_src'] = file_create_url($vars['image_uri']);
    }
  }

  /**
   * Return HTML markup for an image uri or path.
   *
   * In the case of SVG, this returns the SVG code itself, optimized.
   *
   * @param string $file_resource
   *   The filepath to the file to use for extracting markup.  This can also be
   *   a valid stream resource.
   * @param callable|null $processor
   *   A callback to mutate the markup before it gets created as a Markup
   *   instance.  You can use this to replace the stroke color, as an example.
   *
   * @return string
   *   The markup representing the image.
   */
  public function getMarkup(string $file_resource, callable $processor = NULL) {
    $this->validateResourceExists($file_resource);
    $extension = pathinfo($file_resource, PATHINFO_EXTENSION);
    if ($extension === 'svg') {
      $dom = new \DOMDocument();
      $dom->load($file_resource);

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

    if (is_callable($processor)) {
      $svg = $processor($svg);
    }

    return Markup::create($svg);
  }

  public function getBase64DataSrc($file_resource): string {
    $this->validateResourceExists($file_resource);
    $path = parse_url($file_resource, PHP_URL_PATH);
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
    if (!file_exists($file_resource)) {
      throw new \InvalidArgumentException(sprintf('Provided URI: %s does not exist.', $file_resource));
    }

    return sprintf('data:%s;base64,%s', $mime, base64_encode(file_get_contents($file_resource)));
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

  /**
   * Shared helper.
   *
   * @param string $file_resource
   *   Ensure that path exists or throw an exception.
   */
  private function validateResourceExists(string $file_resource) {
    $file_resource = trim($file_resource);
    if (!file_exists($file_resource)) {
      throw new \InvalidArgumentException(sprintf('The file does not exists at: "%s"', $file_resource));
    }
  }

}
