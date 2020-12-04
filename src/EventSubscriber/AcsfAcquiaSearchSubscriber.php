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

    $ah_site_name = getenv('AH_SITE_NAME') ?: '';
    if (!$ah_site_name) {
      return;
    }

    $conf_path = \Drupal::service('site.path');
    $sites_foldername = substr($conf_path, strrpos($conf_path, '/') + 1);

    $db_options = Database::getConnection()->getConnectionOptions();
    $ah_db_name = $db_options['database'];

    $hash = "acsf:{$ah_site_name}:{$ah_db_name}:{$sites_foldername}";
    if (\Drupal::state()->get('search_api_solr.site_hash') != $hash) {
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
