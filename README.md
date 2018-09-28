site_master
===========

[![Build Status](https://travis-ci.org/CuBoulder/site_master.svg?branch=master)](https://travis-ci.org/CuBoulder/site_master)

About
-----
SiteMaster is a web auditing and registry tool.  These are some of its features:
* Registry of Sites
  * Maintains a registry of sites and user roles within the site
  * Searchable by user or URL
  * Can be used as a central repository to track roles/responsibility
  * Has a JSON API so that other applications can integrate with it.
* Auditor - Scan registered sites for potential problems.
  * Crawls and audits registered sites automatically
  * Site owners can manually run scans on both entire sites or specific pages
  * Scans against a set of 'metrics', such as broken links, W3C html validity, Accessibility.
  * Generates a graded report for each scan (customizable grade scales, metric grades and supports pass/fail).
  * Tracks changes over time
  * Emails only sent if changes were detected
* Plugin Support For Custom:
  * Themes
  * Authentication
  * Metrics
  * More!
  
  Take a look at our [Wiki](https://github.com/UNLSiteMaster/site_master/wiki/Installing-SiteMaster---demo-testing-site) for more details

Current Plugins
---------------
* Metic: [w3c validator](https://github.com/UNLSiteMaster/metric_w3c_html) - tests HTML validity
* Metirc: [pa11y](https://github.com/UNLSiteMaster/metric_pa11y) - tests accessibility
* Metric: Link Checker - included
* Metric: Example Metric - included
* Metric/Plugin: [UNL plugin](https://github.com/unl/sitemaster_plugin_unl) - UNL specific tests and functionality
* Theme: [UNL Theme](https://github.com/unl/sitemaster_theme_unl) - advanced theme usage
* Theme: Foundation - included
* Auth: [UNL Auth](https://github.com/unl/sitemaster_plugin_auth_unl)
* Auth: Google - included [readme](https://github.com/UNLSiteMaster/site_master/tree/master/plugins/auth_google)


## Install
See [our documentation on installing](https://github.com/UNLSiteMaster/site_master/wiki/Manual-Install-Process)

## Testing

`vendor/bin/phpunit --testsuite core` for core tests
`vendor/bin/phpunit` to run all tests (including plugins)

## Working with plugins

To install a plugin:

1. Add the plugin machine name and options to your `config.inc.php` file (see config.inc.php for examples)
2. run `php scripts/update.php`
3. run `php scripts/update_libs.php` if the plugin defines any libraries

To uninstall a plugin:

1. Remove the plugin from the `config.inc.php` file
2. run `php scripts/update.php`
