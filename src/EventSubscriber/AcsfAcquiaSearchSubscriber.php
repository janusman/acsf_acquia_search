<?php

namespace Drupal\acsf_acquia_search\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to override the search_api_solr.site_hash value.
 */
class AcsfAcquiaSearchSubscriber implements EventSubscriberInterface {

  /**
   *  Create a hash that contains the site name, DB name and Site folder.
   */
  public function setSearchApiSiteHash(GetResponseEvent $event) {
    $ah_site_name = $_ENV['AH_SITE_NAME']) ?: '';
    if ($ah_site_name) {
      $conf_path = \Drupal::service('site.path');
      $sites_foldername = substr($conf_path, strrpos($conf_path, '/') + 1);

      $options = Database::getConnection()->getConnectionOptions();
      $ah_db_name = $options['database'];
      $hash = $ah_site_name . '-' . $ah_db_name . '-' . $sites_foldername;

      \Drupal::state()->set('search_api_solr.site_hash', $hash);
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('setSearchApiSiteHash');
    return $events;
  }

}
