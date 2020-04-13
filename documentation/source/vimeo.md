# Vimeo

* Use _\Drupal\loft_core\Utility\VimeoBasedEntityBuilder_ to help with pulling metadata from Vimeo into an entity.
* https://developer.vimeo.com/apps

## Suggested Modules

* `composer require vimeo/vimeo-api`
* `composer require drupal/video_embed_field`

## _.env_

        VIMEO_CLIENT_ID="..."
        VIMEO_CLIENT_SECRET="..."
        VIMEO_ACCESS_TOKEN="..."

## `hook_presave`

        public function presave__video() {
            if (!($vimeo_url = $this->f('', 'field_vimeo'))) {
              return;
            }
            $provider = \Drupal::service('video_embed_field.provider_manager')
              ->createInstance('vimeo', ['input' => $vimeo_url]);
            if (!($vimeo_id = $provider->getIdFromInput($vimeo_url))) {
              return;
            }
            if (!($client_id = getenv('VIMEO_CLIENT_ID'))) {
              throw new \RuntimeException("Missing VIMEO_CLIENT_ID");
            }
            if (!($secret = getenv('VIMEO_CLIENT_SECRET'))) {
              throw new \RuntimeException("Missing VIMEO_CLIENT_SECRET");
            }
            if (!($token = getenv('VIMEO_ACCESS_TOKEN'))) {
              throw new \RuntimeException("Missing VIMEO_ACCESS_TOKEN");
            }
            try {
              $client = new Vimeo(
                $client_id,
                $secret,
                $token
              );
              \Drupal::service('loft_core.vimeo_based_entity')
                ->setClient($client)
                ->setTitleField('title')
                ->setPosterField('field_video_poster')
                ->fillWithRemoteData($this->getEntity(), $vimeo_id);
            }
            catch (\Exception $exception) {
              watchdog_exception('se.vimeo', $exception);
            }
        }
