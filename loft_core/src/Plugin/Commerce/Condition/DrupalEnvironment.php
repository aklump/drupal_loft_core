<?php

namespace Drupal\loft_core\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a condition to enable payment gateways by environment.
 *
 * @CommerceCondition(
 *   id = "loft_core_drupal_env",
 *   label = @Translation("Environments"),
 *   entity_type = "commerce_order",
 * )
 *
 * @link https://docs.drupalcommerce.org/commerce2/developer-guide/core/conditions
 */
class DrupalEnvironment extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'environments' => [],
      ] + parent::defaultConfiguration();
  }

  /**
   * Evaluates the condition.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate(EntityInterface $entity) {
    return in_array(DRUPAL_ENV, $this->configuration['environments']);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['environments'] = [
      '#type' => 'checkboxes',
      '#options' => [
        DRUPAL_ENV_DEV => 'Development',
        DRUPAL_ENV_PROD => 'Production',
      ],
      '#title' => $this->t('Environments'),
      '#default_value' => $this->configuration['environments'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['environments'] = array_values(array_filter($values['environments']));
  }

}
