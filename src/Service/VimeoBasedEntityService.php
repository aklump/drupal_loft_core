<?php

namespace Drupal\loft_core\Service;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\File\FileSystemInterface;
use Vimeo\Vimeo;

/**
 * An utility to assist with augmenting entities via Vimeo API.
 *
 * To use this class you must have access to \Vimeo\Vimeo
 * composer require vimeo/vimeo-api.
 */
final class VimeoBasedEntityService {

  private $fields = [];

  private $client;

  private $imageService;

  private $entityFieldManager;

  private $fileSystem;

  /**
   * VimeoBasedEntityService constructor.
   *
   * @param \Vimeo\Vimeo $vimeo_client
   * @param \Drupal\loft_core\Service\ImageService $image_service
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   */
  public function __construct(
    ImageService $image_service,
    EntityFieldManagerInterface $entity_field_manager,
    FileSystemInterface $file_system
  ) {
    $this->imageService = $image_service;
    $this->entityFieldManager = $entity_field_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * Set the Vimeo API Client.
   *
   * @param \Vimeo\Vimeo $vimeo_client
   */
  public function setClient(Vimeo $vimeo_client): VimeoBasedEntityService {
    $this->client = $vimeo_client;

    return $this;
  }

  /**
   * Use this if you want to set a field that is a poster image.
   *
   * @param string $field_name
   *
   * @return \Drupal\loft_core\Utility\VimeoBasedEntityService
   */
  public function setPosterField(string $field_name): VimeoBasedEntityService {
    $this->fields['poster'] = $field_name;

    return $this;
  }

  /**
   * Use this to set the duration on an entity field.
   *
   * @param string $field_name
   *
   * @return \Drupal\loft_core\Utility\VimeoBasedEntityService
   */
  public function setDurationField(string $field_name): VimeoBasedEntityService {
    $this->fields['duration'] = $field_name;

    return $this;

  }

  public function setTitleField(string $field_name): VimeoBasedEntityService {
    $this->fields['name'] = $field_name;

    return $this;
  }

  /**
   * Using the Vimeo API pull the metadata and set it on the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param int $vimeo_id
   *
   * @return $this
   * @throws \Vimeo\Exceptions\VimeoRequestException
   */
  public function fillWithRemoteData(EntityInterface $entity, int $vimeo_id) {
    if (empty($this->client)) {
      throw new \RuntimeException(sprintf("Missing Vimeo client object; did you use ::setClient() before calling ::%s()", __FUNCTION__));
    }

    $filter = $this->fields;
    if (isset($filter['poster'])) {
      $filter['pictures.sizes'] = $filter['poster'];
      unset($filter['poster']);
    }
    $filter = implode(',', array_keys($filter));

    $response = $this->client->request('/videos/' . $vimeo_id, [
      // https://vimeo.com/forums/api/topic:285010
      // https://developer.vimeo.com/api/common-formats#json-filter
      // https://vimeo.com/forums/help/topic:285033
      // That said, make sure you use our JSON filter parameters with your requests to be granted a higher rate limit. You can use the filter parameters on any request where a body is expected to be returned.
      // By using this 'field' filter, it upped our rate limit from 500 to 2500 2017-10-13T17:43, aklump
      'fields' => $filter,
    ], 'GET');

    $data = $response['body'];

    if (isset($data['pictures']['sizes'])) {
      uasort($data['pictures']['sizes'], function ($a, $b) {
        return ($a['width'] ?? 0) - ($b['width'] ?? 0);
      });
      $largest_image = array_pop($data['pictures']['sizes']);
    }

    foreach ($this->fields as $key => $field_name) {
      switch ($key) {
        case 'poster':
          $this->handleImageField($entity, $field_name, $largest_image['link']);
          break;

        default:
          $entity->{$this->fields[$key]} = $data[$key];
      }
    }

    return $this;
  }

  /**
   * Handle creating a public image field from a remote url.
   *
   * @param $entity
   * @param $field_name
   * @param $remote_url
   */
  private function handleImageField($entity, $field_name, $remote_url) {
    $temp_file = $this->imageService
      ->copyRemoteImageByUrl($remote_url);
    $video = $this->entityFieldManager
      ->getFieldDefinitions('commerce_product', 'video');
    $directory = 'public://' . $video[$field_name]->getSettings()['file_directory'];
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::MODIFY_PERMISSIONS);
    $local_file = file_copy($temp_file, $directory . '/' . $temp_file->getFilename(), FileSystemInterface::EXISTS_REPLACE);
    $entity->{$field_name} = $local_file;
  }
}
