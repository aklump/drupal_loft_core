<?php

namespace Drupal\Tests\loft_core\Integration;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\loft_core\Service\ImageService;
use Drupal\loft_core\Service\VisualMediaInterface;
use PHPUnit\Framework\TestCase;
use Drupal\Tests\loft_core\TestWithImageServicesTrait;
use Drupal\Tests\loft_core\TestWithFilesTrait;

/**
 * @covers \Drupal\loft_core\Service\ImageService
 */
final class ImageServiceTest extends TestCase {

  use TestWithImageServicesTrait;
  use TestWithFilesTrait;

  private $imageFactory;

  private $entityTypeManager;

  private $mimeTypeGuesser;


  public function testGetBase64DataSrc() {
    $uri = $this->getTestFileFilepath('teeny.jpg');
    $source = $this->getService()->getBase64DataSrc($uri);
    $this->assertSame('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAASABIAAD/4QCMRXhpZgAATU0AKgAAAAgABQESAAMAAAABAAEAAAEaAAUAAAABAAAASgEbAAUAAAABAAAAUgEoAAMAAAABAAIAAIdpAAQAAAABAAAAWgAAAAAAAABIAAAAAQAAAEgAAAABAAOgAQADAAAAAQABAACgAgAEAAAAAQAAABygAwAEAAAAAQAAABwAAAAA/8IAEQgAHAAcAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAMCBAEFAAYHCAkKC//EAMMQAAEDAwIEAwQGBAcGBAgGcwECAAMRBBIhBTETIhAGQVEyFGFxIweBIJFCFaFSM7EkYjAWwXLRQ5I0ggjhU0AlYxc18JNzolBEsoPxJlQ2ZJR0wmDShKMYcOInRTdls1V1pJXDhfLTRnaA40dWZrQJChkaKCkqODk6SElKV1hZWmdoaWp3eHl6hoeIiYqQlpeYmZqgpaanqKmqsLW2t7i5usDExcbHyMnK0NTV1tfY2drg5OXm5+jp6vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAQIAAwQFBgcICQoL/8QAwxEAAgIBAwMDAgMFAgUCBASHAQACEQMQEiEEIDFBEwUwIjJRFEAGMyNhQhVxUjSBUCSRoUOxFgdiNVPw0SVgwUThcvEXgmM2cCZFVJInotIICQoYGRooKSo3ODk6RkdISUpVVldYWVpkZWZnaGlqc3R1dnd4eXqAg4SFhoeIiYqQk5SVlpeYmZqgo6SlpqeoqaqwsrO0tba3uLm6wMLDxMXGx8jJytDT1NXW19jZ2uDi4+Tl5ufo6ery8/T19vf4+fr/2wBDAAIDAwMEAwQFBQQGBgYGBggIBwcICA0JCgkKCQ0TDA4MDA4MExEUEQ8RFBEeGBUVGB4jHRwdIyolJSo1MjVFRVz/2wBDAQIDAwMEAwQFBQQGBgYGBggIBwcICA0JCgkKCQ0TDA4MDA4MExEUEQ8RFBEeGBUVGB4jHRwdIyolJSo1MjVFRVz/2gAMAwEAAhEDEQAAAfgL7B+PfqbxPZsW3tPO/C/afBF9Qq/VPzP9VqT5E/Sz4z6r/9oACAEBAAEFAg7RAm2vkMI7eFLgLdxbAFVtq7S5kt57Oe33KxXbdXbwrf3NtuckEef/2gAIAQMRAT8B+OyiGej4lwmKHpeukYVLmn//2gAIAQIRAT8B+UxGfTEjzE2jIaat6voIwyfb4L//2gAIAQEABj8Cdqr+TifmjRnQ95bUn2utHzHEPTvHIg0KSCD8mi5j/wAtH7KvMPiB3hSg9MywhaTwIZf/xAAzEAEAAwACAgICAgMBAQAAAgsBEQAhMUFRYXGBkaGxwfDREOHxIDBAUGBwgJCgsMDQ4P/aAAgBAQABPyHkUE8/llKBGBUZwfcR/wAcG4fhC+S8zRB8VOBHqP8AnoI+j/Tz6pIwuEzHv+h8VRf8BzTNwOSiY+zprufGL//aAAwDAQACEQMRAAAQwG2h/8QAMxEBAQEAAwABAgUFAQEAAQEJAQARITEQQVFhIHHwkYGhsdHB4fEwQFBgcICQoLDA0OD/2gAIAQMRAT8QPJgagGSIj1ZAUO7/2gAIAQIRAT8Q6q1n6nTI5bICJo8MkUCaH0v/2gAIAQEAAT8QQIkgklhH2glewnyQ1eaVBjHzk0JR55JP1RRm7ckC8nyHjY7ZuVz4Bw5A8RRKA+Uos1YooTqkUEnoO0lKREAIQl5h57IJRwB0Ofz/AM4rqBeA7SSM5M2S3ERLhNj6v//Z', $source);
  }

  public function testGetMarkupSvg() {
    $uri = $this->getTestFileFilepath('kiwi.svg');
    $markup = $this->getService()->getMarkup($uri);
    $this->assertInstanceOf(MarkupInterface::class, $markup);
    $this->assertSame('<svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewbox="0 0 1024 1024" class="icon" version="1.1"><path d="M479.33 146.1q8.59-0.33 17.27-0.33c228.35 0 413.46 171.45 413.46 382.94S724.94 911.65 496.6 911.65q-8.68 0-17.27-0.33-7.14 0.33-14.35 0.33c-189.8 0-343.67-171.45-343.67-382.94S275.17 145.77 465 145.77q7.19 0 14.33 0.33z" fill="#FFFFFF" /><path d="M660.73 177.13C807.42 236 910.05 371.23 910.05 528.71S807.42 821.46 660.73 880.29c116-74 193-203.8 193-351.58s-76.97-277.59-193-351.58z" fill="#0DAA26" /><path d="M515.68 149.91c165.76 27.31 293 186.5 293 378.8s-127.2 351.49-293 378.8c-165.76-27.31-293-186.5-293-378.8s127.2-351.49 293-378.8z" fill="#E4FFB0" /><path d="M624.26 189.3c109.61 64 184.39 192 184.39 339.41 0 181.86-113.77 334.11-266.33 373.2-109.61-64-184.39-192-184.39-339.41 0-181.86 113.77-334.11 266.33-373.2z" fill="#84DD00" /><path d="M401.16 426.28l-38.36-118 27.64-9 38.49 118.42a111.89 111.89 0 0 0-27.77 8.58z m72.38-7.28l38.93-119.73 27.64 9-39.32 120.87A111.93 111.93 0 0 0 473.54 419z m60.56 37l104.73-76.06 17.08 23.5-106 77A112.74 112.74 0 0 0 534.1 456z m26.27 63.46h133.24v29H559a113.27 113.27 0 0 0 1.73-19.77q0.02-4.69-0.36-9.26z m-14.46 65.19l110 79.86L638.8 688l-110.47-80.24a113.06 113.06 0 0 0 17.58-23.14z m-48.06 45.16l42.22 129.89-27.64 9-42.1-129.56a111.89 111.89 0 0 0 27.52-9.36z m-65.69 10.44l-41.77 128.44-27.64-9 41.38-127.26a111.93 111.93 0 0 0 28.03 7.8z m-61.48-29.66L264 688l-17-23.48L352.27 588a113.17 113.17 0 0 0 18.41 22.57z m-33.55-62.09H209.26v-29h126.51q-0.37 4.58-0.38 9.26a113.27 113.27 0 0 0 1.74 19.74zM348 476.83l-101-73.38 17.08-23.5 100.59 73A112.89 112.89 0 0 0 348 476.83z" fill="#FF6767" /><path d="M465 932.13a331.87 331.87 0 0 1-142.5-32c-43.41-20.46-82.35-49.7-115.75-86.91A407.15 407.15 0 0 1 129.16 685a445.31 445.31 0 0 1 0-312.65 407.15 407.15 0 0 1 77.56-128.13c33.4-37.21 72.34-66.46 115.75-86.91a332.85 332.85 0 0 1 285 0c43.41 20.46 82.35 49.7 115.75 86.91a407.14 407.14 0 0 1 77.56 128.13 445.31 445.31 0 0 1 0 312.65 407.14 407.14 0 0 1-77.56 128.13c-33.4 37.21-72.34 66.45-115.75 86.91A331.88 331.88 0 0 1 465 932.13z m0-765.88c-85.9 0-166.79 37.42-227.77 105.36-61.53 68.56-95.42 159.87-95.42 257.1s33.89 188.54 95.42 257.1c61 67.94 141.86 105.36 227.77 105.36s166.79-37.42 227.77-105.36c61.53-68.56 95.42-159.87 95.42-257.1s-33.89-188.54-95.42-257.1c-61-67.94-141.89-105.36-227.77-105.36z" fill="" /><path d="M496.6 932.13c-6 0-12.15-0.12-18.16-0.35l1.56-40.93c5.49 0.21 11.07 0.32 16.59 0.32a418.92 418.92 0 0 0 153.47-28.68 394.94 394.94 0 0 0 125-78c73.86-68.41 114.54-159.24 114.54-255.75S848.9 341.37 775 273a395 395 0 0 0-125-78 418.92 418.92 0 0 0-153.4-28.75c-5.51 0-11.09 0.11-16.59 0.32l-1.56-40.93c6-0.23 12.13-0.35 18.15-0.35A459.7 459.7 0 0 1 665 156.8a435.73 435.73 0 0 1 137.87 86.11c40 37 71.37 80.16 93.32 128.24a378.63 378.63 0 0 1 0 315.13c-22 48.08-53.35 91.22-93.32 128.24A435.72 435.72 0 0 1 665 900.62a459.7 459.7 0 0 1-168.4 31.51z" fill="" /><path d="M290.32 528.71a157.75 157.68 0 1 0 315.5 0 157.75 157.68 0 1 0-315.5 0Z" fill="#FFFFFF" /><path d="M401.16 426.28l-38.36-118 27.64-9 38.49 118.42a111.89 111.89 0 0 0-27.77 8.58z m72.38-7.28l38.93-119.73 27.64 9-39.32 120.87A111.94 111.94 0 0 0 473.54 419z m60.56 37l104.73-76.06 17.08 23.5-106 77A112.74 112.74 0 0 0 534.1 456z m30.2 60.16h131.78v30.71H563a127.75 127.75 0 0 0 1.71-20.9q-0.03-4.97-0.41-9.84z m-18.39 68.49l110 79.86L638.8 688l-110.47-80.24a113.06 113.06 0 0 0 17.58-23.14z m-48.06 45.16l42.21 129.89-27.64 9-42.09-129.56a111.89 111.89 0 0 0 27.52-9.36z m-65.69 10.44l-41.77 128.44-27.64-9 41.38-127.26a111.93 111.93 0 0 0 28.03 7.8z m-61.48-29.66L264 688l-17-23.48L352.27 588a113.17 113.17 0 0 0 18.41 22.57z m-43.23-63.75H204.57v-30.71h121.57q-0.36 4.84-0.36 9.79a131.41 131.41 0 0 0 1.67 20.9z m20.59-70L247 403.45l17.08-23.5 100.59 73A112.89 112.89 0 0 0 348 476.83z" fill="" /></svg>', (string) $markup);
  }

  public function dataFortestGetUrlWithMultiplierProvider() {
    $tests = [];
    $tests[] = ['foo/bar/landscape.jpg', '2x', 'foo/bar/landscape@2x.jpg'];
    $tests[] = ['foo/bar/landscape.jpg', '@2x', 'foo/bar/landscape@2x.jpg'];
    $tests[] = ['foo/bar/landscape.jpg', 2, 'foo/bar/landscape@2x.jpg'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestGetUrlWithMultiplierProvider
   */
  public function testGetUrlWithMultiplier($uri, $multiplier, $expected) {
    $result = $this->getService()
      ->getUrlWithMultiplier($uri, $multiplier);
    $this->assertSame($expected, $result);
  }


  public function dataFortestGetAspectRatioProvider() {
    $tests = [];
    $tests[] = [
      'landscape.jpg',
      VisualMediaInterface::LANDSCAPE,
      1.49883,
    ];
    $tests[] = [
      'square.jpg',
      VisualMediaInterface::SQUARE,
      1,
    ];
    $tests[] = [
      'portrait.jpg',
      VisualMediaInterface::PORTRAIT,
      0.66719,
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestGetAspectRatioProvider
   */
  public function testGetAspectRatio(string $basename, $b, float $expected) {
    $uri = $this->getTestFileFilepath($basename);
    $this->imageFactory = $this->getImageFactoryByUri($uri);
    $result = $this->getService()->getAspectRatio($uri);
    $this->assertSame($expected, round($result, 5));
  }

  /**
   * @dataProvider dataFortestGetAspectRatioProvider
   */
  public function testGetHeight($basename) {
    $uri = $this->getTestFileFilepath($basename);
    $this->imageFactory = $this->getImageFactoryByUri($uri);
    $result = $this->getService()->getHeight($uri);
    list(, $expected) = getimagesize($uri);
    $this->assertEquals($expected, $result);
  }

  /**
   * @dataProvider dataFortestGetAspectRatioProvider
   */
  public function testGetWidth($basename) {
    $uri = $this->getTestFileFilepath($basename);
    $this->imageFactory = $this->getImageFactoryByUri($uri);
    $result = $this->getService()->getWidth($uri);
    list($expected) = getimagesize($uri);
    $this->assertEquals($expected, $result);
  }

  /**
   * @dataProvider dataFortestGetAspectRatioProvider
   */
  public function testGetOrientation($basename, $expected) {
    $uri = $this->getTestFileFilepath($basename);
    $this->imageFactory = $this->getImageFactoryByUri($uri);
    $result = $this->getService()->getOrientation($uri);
    $this->assertSame($expected, $result);
  }

  public function testGetAspectRatioOnNonFileThrows() {
    $no_file = $this->getTestFileFilepath('foo.jpg');
    $this->expectException(\RuntimeException::class);
    $this->getService()->getAspectRatio($no_file);
  }

  private function getService(): ImageService {
    $this->imageFactory = $this->imageFactory ?? $this->createMock(ImageFactory::class);
    $this->entityTypeManager = $this->entityTypeManager ?? $this->createMock(EntityTypeManagerInterface::class);
    $this->mimeTypeGuesser = $this->mimeTypeGuesser ?? $this->getMimeTypeGuesser();

    return new ImageService($this->entityTypeManager, $this->imageFactory, $this->mimeTypeGuesser);
  }
}
