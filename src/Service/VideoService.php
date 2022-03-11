<?php

namespace Drupal\loft_core\Service;

use Clwu\Mp4;
use wapmorgan\MediaFile\MediaFile;

class VideoService implements VisualMediaInterface {

  private $cache;

  public function getAspectRatio(string $uri): float {
    $info = $this->openMedia($uri);
    if (0 == $info['height']) {
      return 0;
    }

    return $info['width'] / $info['height'];
  }

  public function getWidth(string $uri): float {
    return floatval($this->openMedia($uri)['width']);
  }

  public function getHeight(string $uri): float {
    return floatval($this->openMedia($uri)['height']);
  }

  public function getOrientation(string $uri): int {
    $ratio = $this->getAspectRatio($uri);
    if ($ratio === 1.0) {
      return VisualMediaInterface::SQUARE;
    }
    if ($ratio < 1) {
      return VisualMediaInterface::PORTRAIT;
    }

    return VisualMediaInterface::LANDSCAPE;
  }

  /**
   * Loads $uri and warms $this->cache.
   *
   * @param string $uri
   *
   * @return array
   *   - width int
   *   - height int
   */
  private function openMedia(string $uri): array {
    if (empty($uri)) {
      throw new \InvalidArgumentException('$uri cannot be empty');
    }
    $cid = md5($uri);
    if (isset($this->cache[$cid])) {
      return $this->cache[$cid];
    }

    $get_info = function ($uri) {
      $fd = fopen($uri, 'r');
      if (!$fd) {
        throw new \DomainException('$uri cannot be opened.');
      }
      $file_info = fstat($fd);
      fclose($fd);
      if (!$file_info) {
        throw new \DomainException('$uri cannot be read; probably a remote file.');
      }

      $info = Mp4::getInfo($uri);
      unset($info['rotate']);

      return $info;
    };

    $this->cache[$cid] = ['height' => 0, 'width' => 0];
    try {
      $this->cache[$cid] = $get_info($uri);
    }
    catch (\DomainException $exception) {
      list($extension) = explode('?', $uri, 2);
      $extension = pathinfo($extension, PATHINFO_EXTENSION);
      $local_temp = 'temporary://' . $cid . ".$extension";
      if (copy($uri, $local_temp)) {
        $this->cache[$cid] = $get_info($local_temp);
      }
    }

    return $this->cache[$cid];
  }
}
