<?php

namespace Drupal\loft_core\Plugin\rest;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides an annotated structure for REST responses.
 *
 * @code
 *   return AnnotatedResponse::create()
 *     ->setHttpStatus(406)
 *     ->setMessage("Event can't be loaded.")
 *     ->asJson();
 * @endcode
 *
 * @code
 *   try {
 *     // Perform something that might throw...
 *   }
 *   catch (\Exception $exception) {
 *     return AnnotatedResponse::fromException($exception)->asJson();
 *   }
 * @endcode
 */
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
    $this->setHttpStatus(200);
  }

  /**
   * Create a new instance.
   *
   * @return static
   *   A new response instance.
   */
  public static function create() {
    return new static();
  }

  /**
   * Create a new instance from an exception.
   *
   * If the exception code is in the HTTP response status code range, it will be
   * used.  It outside of this range it will be ignored.
   *
   * @param \Exception $exception
   *   The exception instance.
   *
   * @return static
   *   A new response instance.
   */
  public static function fromException(\Exception $exception) {
    $response = new static();
    $response->setHttpStatus(500);
    $code = $exception->getCode();
    // @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#client_error_responses
    if ($code >= 100 && $code < 600) {
      $response->setHttpStatus($code);
    }

    return $response->setMessage($exception->getMessage());
  }

  /**
   * Set a result word or phrase.
   *
   * @param string $result
   *   A word or phrase to finished this sentence "The request has ____", e.g.
   *   "succeeded", "failed", "created", "deleted".  This will be set by the
   *   http status code when possible, unless explicitly set with this method.
   *
   * @return $this
   */
  public function setResult(string $result): self {
    if (strlen($result) > 30) {
      throw new \InvalidArgumentException('The length may not exceed 30 characters');
    }
    $this->responseBody['result'] = $result;

    return $this;
  }


  /**
   * @param int $code
   *   The status code to be returned in the HTTP responses.
   *
   * @return $this
   *   Self for chaining.
   */
  public function setHttpStatus(int $code): self {
    if (empty($this->responseBody['result'])) {
      $this->setResult(substr($code, 0, 1) == 2 ? 'succeeded' : 'failed');
      if ($code == Response::HTTP_CREATED) {
        $this->setResult('created');
      }
    }
    $this->statusCode = $code;

    return $this;
  }

  /**
   * Set a message to describe the result to the client.
   *
   * Compare this to AnnotatedResponse::addUserMessage()
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

  /**
   * @param array $data
   *   Arbitrary data to send back in response.
   *
   * @return $this
   *   Self for chaining.
   */
  public function setData(array $data): self {
    $this->responseBody['data'] = $data;

    return $this;
  }

  /**
   * Return this as a JsonResponse instance.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function asJson(): JsonResponse {
    return new JsonResponse($this->responseBody, $this->statusCode);
  }

}
