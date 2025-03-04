<?php

namespace Drupal\loft_core\Plugin\rest;

use Symfony\Component\HttpFoundation\JsonResponse;

final class AnnotatedCollectionJsonResponse implements AnnotatedResponseInterface {

  private string $href = '';

  private array $links = [];

  private string $version;

  private AnnotatedResponse $annotatedResponse;

  public function __construct(string $version) {
    $this->version = $version;
    $this->annotatedResponse = AnnotatedResponse::create();
  }

  public static function create($version = "1.0") {
    return new static($version);
  }

  public function __call($method, $args) {
    $result = call_user_func_array([$this->annotatedResponse, $method], $args);
    if ($result === $this->annotatedResponse) {
      return $this;
    }

    return $result;
  }


  public function setLinks(array $links) {
    $this->links = $links;
  }

  public function setHref(string $href) {
    $this->href = $href;
  }

  /**
   * {@inheritdoc}
   */
  public function setData(array $items): AnnotatedResponseInterface {

    // Ensure the items is an array of objects.
    if ($items !== array_values($items)) {
      $items = [$items];
    }

    if (count($items) && !array_key_exists('data', $items[0])) {
      throw new \InvalidArgumentException('$items[0][data] must be set.');
    }

    $data = [
      'version' => $this->version,
      'href' => $this->href,
      'links' => $this->links,
      'items' => $items,
    ];
    $this->annotatedResponse->setData($data);

    return $this;
  }

  /**
   * Return this as a JsonResponse instance.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function asJson(): JsonResponse {
    $body = $this->annotatedResponse->jsonSerialize();
    $body['collection'] = $body['data'];
    unset($body['data']);

    return new JsonResponse($body, $this->annotatedResponse->getHttpStatus());
  }

  public function setResult(string $result): AnnotatedResponseInterface {
    return $this->__call(__FUNCTION__, func_get_args());
  }

  public function setHttpStatus(int $code): AnnotatedResponseInterface {
    return $this->__call(__FUNCTION__, func_get_args());
  }

  public function getHttpStatus(): int {
    return $this->__call(__FUNCTION__, func_get_args());
  }

  public function setMessage(string $message): AnnotatedResponseInterface {
    return $this->__call(__FUNCTION__, func_get_args());
  }

  public function addUserMessage(string $log_level, string $message, array $context = []): AnnotatedResponseInterface {
    return $this->__call(__FUNCTION__, func_get_args());
  }

  public function jsonSerialize() {
    return $this->__call(__FUNCTION__, func_get_args());
  }
}
