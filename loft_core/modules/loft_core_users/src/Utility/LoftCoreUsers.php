<?php

namespace Drupal\loft_core_users\Utility;

class LoftCoreUsers {

  public function getEmailDomain($email) {
    if (!trim($email)) {
      return '';
    }
    $domain = preg_replace('/^.+@/', '', $email);
    $domain = explode('.', $domain);

    return trim(implode('.', array_slice($domain, -2)));
  }

}
