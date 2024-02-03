<?php

namespace Drupal\Tests\loft_core;

use Drupal\Core\File\MimeType\MimeTypeGuesser;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Image\ImageInterface;

trait TestWithImageServicesTrait {

  private function getMimeTypeGuesser() {
    $guesser = $this->createMock(MimeTypeGuesser::class);
    $guesser
      ->method('guessMimeType')
      ->willReturnCallback(function ($uri) {
        $type = getimagesize($uri)['mime'] ?? '';
        if (!$type) {
          if (0 === strcasecmp('svg', pathinfo($uri, PATHINFO_EXTENSION))) {
            $type = 'image/svg+xml';
          }
        }

        return $type;
      });

    return $guesser;
  }

  private function getImageFactoryByUri(...$uris) {
    $images = [];
    foreach ($uris as $uri) {
      $size = getimagesize($uri);
      $images[] = $this->createConfiguredMock(ImageInterface::class, [
        'getWidth' => $size[0],
        'getHeight' => $size[1],
      ]);
    }

    $factory = $this->createMock(ImageFactory::class);
    $factory
      ->method('get')
      ->willReturnOnConsecutiveCalls(...$images);

    return $factory;
  }
}
