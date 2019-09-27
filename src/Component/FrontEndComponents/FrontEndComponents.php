<?php

namespace Drupal\loft_core\Component\FrontEndComponents;

use Symfony\Component\Yaml\Yaml;

/**
 * A Drupal component for organizing components grouped by individual dirs.
 */
class FrontEndComponents {

  protected $pathToTheme;

  protected $componentsDir;

  /**
   * A translation callback.
   *
   * Expecting \Drupal\Core\StringTranslation\StringTranslationTrait::t.
   * Because this is a component is does not have access to Drupal core so the
   * translation callback must be injected.
   *
   * @code
   *   new FrontEndComponents($path, 'components', 't');
   * @endcode
   *
   * @var callable
   */
  protected $translationCallback;

  /**
   * FrontEndComponents constructor.
   *
   * @param string $path_to_theme
   *   The system path to the Drupal theme.
   * @param string $components_directory
   *   The subdirectory within $path_to_theme where the front-end components
   *   are stored.  A single component is expected to be contained in a
   *   directory named for the component.  Component names should be hyphened,
   *   not underscored, e.g. my-thing.
   */
  public function __construct(string $path_to_theme, string $components_directory, callable $translation_callback) {
    $this->pathToTheme = $path_to_theme;
    $this->componentsDir = $components_directory;
    $this->translationCallback = $translation_callback;
  }

  /**
   * Return the theme's front end components as an array for hook_theme
   * implementation.
   *
   * @return array
   */
  public function getHookThemeDefinitions() {
    $path_to_components = rtrim($this->pathToTheme, '/') . '/' . $this->componentsDir;
    $themes = [];
    foreach (scandir($path_to_components) as $component_path) {
      if (in_array($component_path, ['.', '..'])) {
        continue;
      }
      $id = basename($component_path);
      $hook = str_replace(['-'], ['_'], $id);
      $themes[$hook] = [
        'path' => $path_to_components . "/$id",
      ];
      foreach (scandir($themes[$hook]['path']) as $definition_file) {
        if (in_array($definition_file, ['.', '..'])) {
          continue;
        }
        $full_path = $themes[$hook]['path'] . "/$definition_file";
        $info = pathinfo($definition_file);
        switch ($info['extension']) {
          case 'php':
            $themes[$hook]['file'] = $info['basename'];
            break;

          case'yml':
            $schema = Yaml::parse(file_get_contents($full_path));
            break;

          case'json':
            $schema = json_decode(file_get_contents($full_path), TRUE);
            break;
        }
      }
      if ($schema) {
        foreach ($schema['properties'] as $var_name => $data) {
          $themes[$hook]['vars'][$var_name] = $this->getDefaultVariableValueFromSchemaItem($data ?? []);
        }
      }
    }

    return $themes;
  }

  /**
   * Get the default value cast to the correct type.
   *
   * For string values, if the string is wrapped in @Translation(""), it will be
   * sent to t().
   *
   * @param array $data
   *   The schema definition for a single property.
   *
   * @return array|bool|float|int|mixed|\stdClass|string|null
   */
  private function getDefaultVariableValueFromSchemaItem(array $data) {
    // @link https://json-schema.org/latest/json-schema-validation.html#rfc.section.6.1.1
    $type = $data['type'] ?? 'string';
    $value = $data['default'] ?? NULL;
    switch ($type) {
      case 'null':
        return NULL;

      case 'boolean':
        return (bool) $value;

      case 'object':
        return is_object($value) ? $value : new \stdClass();

      case 'array':
        return (array) $value;

      case 'integer':
        return intval($value);

      case 'number':
        return $value * 1;

      case 'string':
        $value = strval($value);
        if (strstr($value, 'Translation(')) {
          $value = $this->translate($value);
        }

        return $value;
    }
  }

  /**
   * Translate based on @Translate annotation.
   *
   * @param string $annotation
   *
   * @return string
   */
  private function translate(string $annotation): string {
    // Parse the annotation.
    // TODO Could not figure out a quick way to pass this off to the
    // Doctrine parser, but it would sure be nice if this wasn't homebrewed.
    if (preg_match('/@.*?Translation\((.+?)(?:,\s*(.+))?\)/', $annotation, $matches)) {
      $string = trim($matches[1], '"');
      $data = [];
      foreach (explode(',', $matches[2] ?? '') as $arg) {
        preg_match('/(.+?)=(.+)/', $arg, $arg_data);
        list(, $name, $value) = $arg_data;
        $name = trim($name);
        $value = str_replace(' = ', ':', $value);
        $data[$name] = json_decode($value, TRUE);
      }

      // Send it off to the translation callback.
      $value = (string) call_user_func_array($this->translationCallback, [
        $string,
        $data['arguments'] ?? [],
        ['context' => $data['context'] ?? []],
      ]);
    }

    return $value;
  }

}
