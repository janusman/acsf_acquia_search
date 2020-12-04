<?php

namespace Drupal\acsf_acquia_search\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\Event\GatheringPluginInfoEvent;
use Solarium\Core\Event\Events;

use Drupal\search_api_solr\Solarium\EventDispatcher\EventProxy;
use Drupal\Core\Database\Database;

/**
 * Event subscriber to override the search_api_solr.site_hash value.
 */
class AcsfAcquiaSearchSubscriber implements EventSubscriberInterface {

  /**
   *  Create a hash that contains the site name, DB name and Site folder.
   */
  public function setSearchApiSiteHash() {
    ## DEBUG #################
    ##   appends output to $debug_file
    ##   add extra variables/etc in $debug_extra
    $debug_extra = []; ## i.e. [ $one, $two ];
    #
    $debug_file = "/mnt/tmp/acquia_support_debug.txt";
    $debug_header = "[" . date("Y-m-d G:i:s") . "] " . $_SERVER["REQUEST_URI"] . " ###########\n";
    $debug_exception = new \Exception; 
    $debug_parts = ["function" => __FUNCTION__, "backtrace" => var_export($debug_exception->getTraceAsString(), TRUE), "extra" => $debug_extra];
    file_put_contents($debug_file,  $debug_header . print_r($debug_parts, TRUE) . PHP_EOL, FILE_APPEND);
    ## END DEBUG #################
 
    echo "setSearchApiSiteHash()\n";

    $ah_site_name = $_ENV['AH_SITE_NAME'] ?: '';
    #exit;
    if (!$ah_site_name) {
      echo "Hash already set\n";
      return;
    }

    $db_options = Database::getConnection()->getConnectionOptions();
    $ah_db_name = $db_options['database'];
    $hash_db_name = substr(base_convert(hash('sha256', $ah_db_name), 16, 36), 0, 6);
    
    $hash = "acsf:{$ah_site_name}:{$hash_db_name}";
    echo "Setting hash to $hash\n";
    if (\Drupal::state()->get('search_api_solr.site_hash') != $hash) {
      \Drupal::state()->set('search_api_solr.site_hash', $hash);
    }

  }
  
  /**
   *  Fire on any request (but not on Drush commands).
   */
  public function requestEvent(GetResponseEvent $event) {
    $this->setSearchApiSiteHash();
  }
  
  /**
   *  Fire on some Search API event.
   */
  #public function searchApiEvent(GatheringPluginInfoEvent $event) {
  #  $this->setSearchApiSiteHash();
  #}
  
  public function solariumEvent(EventProxy $event) {
    $this->setSearchApiSiteHash();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['requestEvent'];
    #$events[SearchApiEvents::GATHERING_BACKENDS][] = ['searchApiEvent'];
    $events[Events::PRE_EXECUTE_REQUEST][] = ['solariumEvent'];
    return $events;
  }

}
