<?php

namespace Drupal\Tests\loft_core;

trait TestWithFilesTrait {

  /**
   * Delete all the files in the test files directory.
   *
   * This should generally be added to the tearDown method.
   *
   * @return void
   */
  public function deleteAllTestFiles() {
    $basepath = $this->getTestFilesDirectory();
    $all_files = array_diff(scandir($basepath), ['.', '..']);
    foreach ($all_files as $file) {
      $this->deleteRecursively("$basepath/$file");
    }
  }

  /**
   * @param $path
   *   An absolute or relative path to a file in the test files directory to be deleted.  Absolute files must be in the test directory.
   *
   * @return void
   *
   * @see ::getTestFilesDirectory
   */
  public function deleteTestFile($test_file) {
    if (empty($test_file)) {
      throw new \InvalidArgumentException('$test_file cannot be empty');
    }
    $is_absolute = substr($test_file, 0, 1) === '/';
    if ($is_absolute && !$this->isTestFile($test_file)) {
      throw new \InvalidArgumentException(sprintf('You cannot delete absolute paths outside of the sandbox: %s', $test_file));
    }
    if (!$is_absolute) {
      $test_file = $this->getTestFileFilepath($test_file);
    }
    if (file_exists($test_file)) {
      if (is_dir($test_file)) {
        $this->deleteRecursively($test_file);
      }
      else {
        chmod($test_file, 0777);
        unlink($test_file);
      }
    }
  }

  private function isTestFile($path) {
    return strpos($path, $this->getTestFilesDirectory()) === 0;
  }

  private function getTestFilesDirectory() {
    $basepath = __DIR__ . '/files/';
    if (!file_exists($basepath)) {
      mkdir($basepath, 0755, TRUE);
    }
    if (!$basepath || !is_writable($basepath)) {
      throw new \RuntimeException(sprintf('Failed to establish a sandbox base directory: %s', $basepath));
    }

    return $basepath;
  }

  private function deleteRecursively($path) {
    if (!$this->isTestFile($path)) {
      throw new \RuntimeException(sprintf('$path is not in the files sandbox and cannot be deleted. %s', $path));
    }
    chmod($path, 0777);
    if (!is_dir($path)) {
      unlink($path);

      return;
    }
    $files = array_diff(scandir($path), ['.', '..']);
    foreach ($files as $file) {
      $this->deleteRecursively("$path/$file");
    }
    rmdir($path);
  }

  public function getTestFileFilepath($relative = '', $create = FALSE) {
    $basedir = $this->getTestFilesDirectory();
    if (empty($relative)) {
      return $basedir;
    }
    $path = $basedir . ltrim($relative, '/');
    $is_dir = substr($path, -1) === '/';
    if ($is_dir) {
      if ($create && !file_exists($path)) {
        mkdir($path, 0755, TRUE);
      }
    }
    else {
      // For all files, always create the parent structure to make it easy on
      // the implementing test to work with the filepath.  No harm as the
      // teardown method should be calling deleteAll(), which will remove the
      // created directories.
      $parent = dirname($path);
      if (!file_exists($parent)) {
        mkdir($parent, 0755, TRUE);
      }
      if ($create && !file_exists($path)) {
        touch($path);
      }
    }

    return $path;
  }

}

