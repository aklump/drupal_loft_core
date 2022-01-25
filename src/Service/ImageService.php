<?php

namespace Drupal\loft_core\Service;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\Markup;
use Drupal\image\Entity\ImageStyle;
use League\ColorExtractor\Color;
use League\ColorExtractor\Palette;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * Functions for working with images.
 */
class ImageService {

  /**
   * An image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * A mime type guesser.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Service instance.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   Service instance.
   * @param \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface $mime_type_guesser
   *   Service instance.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ImageFactory $image_factory, MimeTypeGuesserInterface $mime_type_guesser) {
    $this->entityTypeManager = $entity_type_manager;
    $this->imageFactory = $image_factory;
    $this->mimeTypeGuesser = $mime_type_guesser;
  }

  /**
   * Determine if an image is taller than the width by at least 10%.
   *
   * @param string $uri
   *   The image URI.
   *
   * @return bool
   *   True if the image is taller than wide.
   */
  public function isImageTall(string $uri): bool {
    $image = $this->imageFactory->get($uri);
    $width = $image->getWidth();

    return $width + 0.1 * $width < $image->getHeight();
  }

  /**
   * Adds image_src as an web-accessible URL using image_uri/style_name.
   *
   * @param array $vars
   *   An array of preprocess variables.
   *   - image_uri REQUIRED
   *   - style_name OPTIONAL
   *   - image_src FORBIDDEN.
   *
   * @return self
   *   Self for chaining.
   *
   * @throws \InvalidArgumentException
   *   If image_uri is empty or missing; if image_src is already set; if style_name is
   *   invalid.
   */
  public function preprocessImageVars(array &$vars): self {
    if (empty($vars['image_uri'])) {
      throw new \InvalidArgumentException("Empty value for image_uri");
    }
    if (!array_key_exists('image_uri', $vars)) {
      throw new \InvalidArgumentException("Missing key image_uri");
    }
    if (!empty($vars['image_src'])) {
      throw new \InvalidArgumentException("image_src is already set; it must be empty to use this method.");
    }
    if (!empty($vars['style_name'])) {
      $style = $this->entityTypeManager->getStorage('image_style')
        ->load($vars['style_name']);
      if (!$style) {
        throw new \InvalidArgumentException(sprintf('Invalid image style: %s', $vars['style_name']));
      }
      $vars['image_src'] = $style->buildUrl($vars['image_uri']);
    }
    else {
      $vars['image_src'] = file_create_url($vars['image_uri']);
    }

    return $this;
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
    $mime = $this->mimeTypeGuesser->guess($file_resource);
    if ('image/svg+xml' === $mime) {
      $dom = new \DOMDocument();
      $contents = file_get_contents($file_resource);
      if (!$dom->loadXML($contents)) {
        throw new \RuntimeException(sprintf('Failed to load SVG XML from %s', $file_resource));
      }

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

  /**
   * Get the data URI for an image to be used in inline CSS.
   *
   * @param string $file_resource
   *   The path to an image file.
   *
   * @return string
   *   The data URI.
   *
   * @link https://css-tricks.com/data-uris
   */
  public function getBase64DataSrc($file_resource): string {
    $this->validateResourceExists($file_resource);
    list($path) = explode('?', $file_resource . '?');
    $mime = $this->mimeTypeGuesser->guess($path);
    if (is_null($mime)) {
      throw new \RuntimeException(sprintf('Unable to determine mimetype for: %s.', $path));
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
   * @return object|false
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
    $allowed_extensions = [
      'jpg' => IMAGETYPE_JPEG,
      'jpeg' => IMAGETYPE_JPEG,
      'gif' => IMAGETYPE_GIF,
      'png' => IMAGETYPE_PNG,
    ];
    list($remote_path) = explode('?', $remote_url);
    $info = pathinfo($remote_path);
    if (empty($info['extension'])) {
      $file_type = exif_imagetype($remote_url);
      $extension = array_search($file_type, $allowed_extensions);
      $remote_path = rtrim($remote_path, '.') . ".$extension";
      $info = pathinfo($remote_path);
    }

    $file = [];
    $file['uid'] = \Drupal::currentUser()->id();
    $file['status'] = 0;
    $file['filename'] = file_munge_filename($info['basename'], implode(' ', array_keys($allowed_extensions)));
    $file['uri'] = 'temporary://' . $file['filename'];
    if (!copy($remote_url, $file['uri'])) {
      return FALSE;
    }

    $file['filemime'] = $this->mimeTypeGuesser->guess($file['uri']);
    $file['filesize'] = filesize($file['uri']);

    return $this->entityTypeManager->getStorage('file')->create($file);
  }

  /**
   * Detect the final width of an image style.
   *
   * This is not bulletproof but should work in most cases.  It looks at all the
   * image effects for a configured width value, and takes the final one found.
   *
   * @param \Drupal\image\Entity\ImageStyle $image_style
   *   An image style to decipher.
   *
   * @return int
   *   The configured width for the final effect having a width configuration
   *   value.
   */
  public function getStyleWidth(ImageStyle $image_style): int {
    $width = NULL;
    foreach ($image_style->getEffects() as $effect) {
      $effect_width = $effect->getConfiguration()['data']['width'] ?? NULL;
      if ($effect_width) {
        $width = $effect_width;
      }
    }

    return (int) $width;
  }

  /**
   * Detect the final height of an image style.
   *
   * This is not bulletproof but should work in most cases.  It looks at all the
   * image effects for a configured height value, and takes the final one found.
   *
   * @param \Drupal\image\Entity\ImageStyle $image_style
   *   An image style to decipher.
   *
   * @return int|null
   *   The configured width for the final effect having a width configuration
   *   value.
   */
  public function getStyleHeight(ImageStyle $image_style) {
    $height = NULL;
    foreach ($image_style->getEffects() as $effect) {
      $effect_height = $effect->getConfiguration()['data']['height'] ?? NULL;
      if ($effect_height) {
        $height = $effect_height;
      }
    }

    return $height;
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

  /**
   * Get a render array for SVG text.
   *
   * @link https://www.w3schools.com/graphics/svg_text.asp
   *
   * @param string $text
   *   The text to render.
   * @param $viewbox_width
   *   The width of the viewbox.
   * @param $font_size
   *   The font size to use.
   * @param $line_height
   *   The line height to use.
   *
   * @return array
   *   A render array for an SVG text element.
   */
  public function getSvgText(string $text, $viewbox_width, $font_size, $line_height) {
    return [
      '#prefix' => Markup::create(sprintf('<svg viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg"><text x="0" y="%d">', $viewbox_width, $line_height, $font_size)),
      '#markup' => $text,
      '#suffix' => Markup::create('</text></svg>'),
    ];
  }

  /**
   * Given an URL without a multiplier, return the same with @2x added.
   *
   * @param string $url
   *   An URL without a multiplier (magnification value), e.g. foo/bar.jpg.
   * @param string $multiplier
   *   E.g. '2x', '.1x', etc.
   *
   * @return string
   *   E.g., 'foo/bar@2x.jpg'
   */
  public function getUrlWithMultiplier(string $url, string $multiplier): string {
    $multiplier = ltrim($multiplier, '@');
    $multiplier = rtrim($multiplier, 'x');
    $info = pathinfo($url);

    return sprintf("%s/%s@%sx.%s", $info['dirname'], $info['filename'], $multiplier, $info['extension']);
  }

  /**
   * Get the dominant color of an image.
   *
   * @param string $uri
   *   The URI to the image to analyze.
   * @param string $default
   *   A default color if the analysis fails.
   *
   * @return string
   *   The dominant color ready for CSS style, e.g. "#ff0000" or $default.
   *
   * @throws \InvalidArgumentException
   *   If the file at $uri does not exist.
   */
  public function getDominantColor(string $uri, string $default = 'transparent'): string {
    if (!file_exists($uri)) {
      throw new \InvalidArgumentException(sprintf('File not found: %s', $uri));
    }
    $palette = Palette::fromFilename($uri);
    $colors = $palette->getMostUsedColors(1);
    $color = Color::fromIntToHex(key($colors));

    return $color ?? $default;
  }

  /**
   * Get the aspect ratio of an image or style derivative.
   *
   * @param string $uri
   * @param string|null $style_name
   *   Include this if $uri is the original and you wish to determine the aspect
   *   ratio when applying $style_name.
   * @param &$width
   *   Will be set with the width of the style; when $style_name is null the
   *   native image width will be set.
   * @param &$height
   *   Will be set with the height based on the aspect ratio and $width.  So if
   *   style is omitted this will be the native height, but if style is present,
   *   this will be the aspect ratio applied to the style width.
   *
   * @return float
   *   The aspect ratio.  Note that you need to use the reciprocal (1/$ratio)
   *   for the CSS padding bottom trick, including computing as a percentage
   *   (100%/$ratio), e.g. `'padding-bottom', 100 / $ratio . '%'
   */
  public function getAspectRatio(string $uri, string $style_name = NULL, &$width = NULL, &$height = NULL): float {
    if (!file_exists($uri)) {
      throw new \RuntimeException(sprintf('The provided URI does not exist: %s', $uri));
    }


    // It's possible that the orientation of the image is 90 degrees off, which
    // results in the height coming back as the width, and visa versa.  We try
    // to fix that by looking for the orientation information.
    // @link https://stackoverflow.com/a/13963783/3177610
    $exif = @exif_read_data($uri, 'EXIF');
    $width = $exif['ExifImageWidth'] ?? NULL;
    $height = $exif['ExifImageLength'] ?? NULL;

    // The EXIF data is not present on some images, so we need to fallback to
    // the image API for dimensions.
    if (empty($width) || empty($height)) {
      $image = $this->imageFactory->get($uri);
      $width = $image->getWidth();
      $height = $image->getHeight();
    }

    $orientation = $exif['Orientation'] ?? NULL;
    if ($orientation === 6 || $orientation === 8) {
      list($width, $height) = [$height, $width];
    }

    // Create our native response, only to be changed by the image style if it
    // has a different aspect ratio.
    $ratio = $width / $height;

    if (!empty($style_name)) {
      /** @var ImageStyle $image_style */
      $image_style = $this->entityTypeManager
        ->getStorage('image_style')
        ->load($style_name);
      if (!$image_style) {
        throw new \InvalidArgumentException(sprintf('Failed to load image style: %s', $style_name));
      }

      $width = $this->getStyleWidth($image_style);
      $height = $this->getStyleHeight($image_style);

      // If the style provides a height then the aspect ratio needs to be
      // recalculated based on the image style.
      if (NULL !== $height) {
        $ratio = $width / $height;
      }
    }

    $width = intval($width);
    if (empty($height)) {
      // This will happen if the image style has a null height.
      $height = intval(round($width / $ratio));
    }

    return floatval($ratio);
  }

  /**
   * Get an image or style derivative aspect ratio.
   *
   * @code
   *   list($ratio, $width, $height) = $foo->getImageDimensionAndAspectRatio($uri);
   *   list($ratio, $original_width, $original_height) = $foo->getImageDimensionAndAspectRatio($uri, 'small');
   * @endcode
   *
   * @return int[]
   *   - 0 The aspect ratio or NULL if height is empty.  Note that you need to
   *   use the reciprocal (1/$ratio) for the CSS padding bottom trick, including
   *   computing as a percentage (100%/$ratio), e.g. `'padding-bottom', 100 /
   *   $aspect_ratio . '%'`.
   *   - 1 The original image width.
   *   - 2 The original image height.
   *
   * @deprecated Use getAspectRatio() instead.
   */
  public function getAspectRatioWidthAndHeight(string $uri, string $style_name = NULL): array {
    if (!file_exists($uri)) {
      throw new \RuntimeException(sprintf('The provided URI does not exist: %s', $uri));
    }

    $image = $this->imageFactory->get($uri);
    $response = [$image->getWidth(), $image->getHeight()];

    // It's possible that the orientation of the image is 90 degrees off, which
    // results in the height coming back as the width, and visa versa.  We try
    // to fix that by looking for the orientation information.
    // @link https://stackoverflow.com/a/13963783/3177610
    $exif = @exif_read_data($uri, 'EXIF');
    $orientation = $exif['Orientation'] ?? NULL;
    if ($orientation === 6 || $orientation === 8) {
      $response = array_reverse($response);
    }

    // Create our native response, only to be changed by the image style if it
    // has a different aspect ratio.
    $response = [$response[0] / $response[1], $response[0], $response[1]];

    if (!empty($style_name)) {
      /** @var ImageStyle $image_style */
      $image_style = $this->entityTypeManager
        ->getStorage('image_style')
        ->load($style_name);
      if (!$image_style) {
        throw new \InvalidArgumentException(sprintf('Failed to load image style: %s', $style_name));
      }

      // If the style provides a height then the aspect ratio needs to be
      // recalculated and the native one replaced by it.
      if (NULL !== $this->getStyleHeight($image_style)) {
        $response[0] = $this->getStyleWidth($image_style) / $this->getStyleHeight($image_style);
      }
    }

    return $response;
  }

}
