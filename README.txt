ACSF Acquia Search module
===================================

This small module overrides the Acquia Search module behavior for sites hosted
on ACSF so that they can share Solr core instances whose name has a
'.[environment].default' suffix.

This previously required a small patch or factory hook code override, but is now
provided in module form for easy installation.

INSTRUCTIONS
--------------

* If the site was previously using Acquia Search, see the WARNING section below.
* OPTIONAL: If you have any previous Solr indexes, it is recommended to clear
  them BEFORE enabling the module.
* Enable the module.
* If you can not connect to an Acquia Search core, you can follow the Acquia
  documentation at
  https://docs.acquia.com/acquia-search/multiple-cores/troubleshoot/
* Index (or reindex) your data. Wait 2-5 minutes so that data is replicated
  across the Acquia Solr instances.
* Clear Drupal caches before testing!
* Test your search results.

Uninstalling the module should revert back any behavior. You may need to reindex
again.

WARNING: for EXISTING sites already using Acquia-hosted Solr
--------------
NOTE: If you ALREADY are using existing Acquia-hosted Solr cores, installing
this module does introduce some changes which can cause problems.

1) This module will enforce the value of the Search API Solr "site_hash"
   state variable depending on the current site/environment.

  This is done to avoid data collision between the sites sharing the same Solr
  cores. HOWEVER, enabling this module will immediately "orphan" any Solr data
  that was previously indexed from this site.

  This means that right after enabling this module on a previously-indexed site,
  you will see these symptoms:

  * Your searches will no longer return results (unless cached!)
  * Your Search API "index" Admin UI can show that items have been indexed, but
    report that there are no items in the Solr index.

  SO: After enabling this module, YOU MUST reindex the site.

  Alternatively, uninstalling this module undoes the change and reverts back
  the search_api_solr.site_hash to the original value.

  (Again, this the above should not a problem for new sites!)

2) If your site has an existing override code (like using ACSF hooks), you
   should evaluate whether you want to keep that, or switch to just using this
   module.

   Note it is not recommended to keep both active.

Technical details
--------------
The module does essentially 2 things:

A) Implement this alter hook:

  hook_acquia_search_get_list_of_possible_cores_alter()

This adds an extra fallback core to the list of "elligible" cores. This core
will only be used if your subscription does not have any other elligible core.

See Drupal\acquia_search\PreferredSearchCoreService::getListOfPossibleCores()
for more information.

B) It overrides the search_api_solr.site_hash State value on each page load, to
ensure Solr data does not collide with other sites.
