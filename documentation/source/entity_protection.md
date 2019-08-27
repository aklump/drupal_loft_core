# Protecting Critical Entities

With Loft Core, it's easy to protect critical entities that should not be deleted through the admin UI.  Learn more by looking at the docblocks in `\Drupal\loft_core\Service\EntityProtectionService`.

1. Add this to _settings.php_, where `SE_CORE_` is the uppercase name of your custom module or other identifying prefix you wish to use for your PHP constants.

        $config['loft_core.entity_protection']['prefix'] = 'SE_CORE_';

1. Define some constants like so, in your core module file.

        <?php
        
        /**
         * @file
         * Module entry file for se_core.
         */
        
        define('SE_CORE_NID_ABOUT', 531);
        define('SE_CORE_COMMERCE_STORE_ID_SEAO', 1);
        
        ...

1. Add this to _hook_form_alter_ implementation:

        /**
         * Implements hook_form_alter().
         */
        function se_core_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
          \Drupal::service('loft_core.entity_protection')
            ->handleForm($form, $form_state, $form_id);
          ...  

1. Add this to _hook_entity_predelete_ implementation:

        /**
         * Implements hook_entity_predelete().
         */
        function se_core_entity_predelete(EntityInterface $entity) {
          \Drupal::service('loft_core.entity_protection')
            ->handlePreDelete($entity);
        }
        
1. The delete UI for these entities will be removed from Drupal.
