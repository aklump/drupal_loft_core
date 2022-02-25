<?php

namespace Drupal\check_pages_api;

use Symfony\Component\HttpFoundation\JsonResponse;

final class AnnotatedResponse {

  /**
   * @var array
   */
  private $responseBody;

  /**
   * @var int
   */
  private $statusCode;

  public function __construct() {
    $this->responseBody = [
      'result' => '',
      'message' => '',
      'user_messages' => [],
      'data' => [],
    ];
    $this->setHttpStatus(500);
  }

  public static function create() {
    return new static();
  }

  public function setResult(string $status): self {
    if (strlen($status) > 30) {
      throw new \InvalidArgumentException('The length of $status may not exceed 30 characters');
    }
    $this->responseBody['result'] = $status;

    return $this;
  }


  public function setHttpStatus(int $code): self {
    if (empty($this->responseBody['result'])) {
      $this->setResult(substr($code, 0, 1) == 2 ? 'success' : 'failed');
    }
    $this->statusCode = $code;

    return $this;
  }

  /**
   * Set a message to describe the result to the client.
   *
   * Compare this to \Drupal\check_pages_api\AnnotatedResponse::addUserMessage.
   *
   * @param string $message
   *
   * @return $this
   */
  public function setMessage(string $message): self {
    $this->responseBody['message'] = $message;

    return $this;
  }

  /**
   * Add a message appropriate to the end user.
   *
   * @param string $log_level
   *   One of \Psr\Log\LogLevel constants.
   * @param string $message
   * @param array $context
   *
   * @return $this
   */
  public function addUserMessage(string $log_level, string $message, array $context = []): self {
    $this->responseBody['user_message'][] = [
      'level' => $log_level,
      'message' => $message,
      'context' => $context,
    ];

    return $this;
  }

  public function setData(array $data): self {
    $this->responseBody['data'] = $data;

    return $this;
  }

  public function asJson(): JsonResponse {
    return new JsonResponse($this->responseBody, $this->statusCode);
  }

}
