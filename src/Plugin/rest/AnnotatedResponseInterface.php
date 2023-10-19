<?php

namespace Drupal\loft_core\Plugin\rest;


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
 *     return AnnotatedResponse::createFromException($exception)->asJson();
 *   }
 * @endcode
 */
interface AnnotatedResponseInterface extends \JsonSerializable {

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
  public function setResult(string $result): AnnotatedResponseInterface;

  /**
   * @param int $code
   *   The status code to be returned in the HTTP responses.
   *
   * @return $this
   *   Self for chaining.
   */
  public function setHttpStatus(int $code): AnnotatedResponseInterface;

  public function getHttpStatus(): int;

  /**
   * Set a message to describe the result to the client.
   *
   * Compare this to AnnotatedResponse::addUserMessage()
   *
   * @param string $message
   *
   * @return $this
   */
  public function setMessage(string $message): AnnotatedResponseInterface;

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
  public function addUserMessage(string $log_level, string $message, array $context = []): AnnotatedResponseInterface;

  /**
   * @param array $data
   *   Arbitrary data to send back in response.
   *
   * @return $this
   *   Self for chaining.
   */
  public function setData(array $data): AnnotatedResponseInterface;

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize();
}
