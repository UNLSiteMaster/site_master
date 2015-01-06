site_master
===========

[![Build Status](https://travis-ci.org/UNLSiteMaster/site_master.svg?branch=master)](https://travis-ci.org/UNLSiteMaster/site_master)

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

Working with the vagrant development machine
--------------------------------------------
To install:
```
cd vagrant/dev
vagrant up
```

To update:
```
cd vagrant/dev
vagrant provision
```

To run tests:
```
cd vagrant/dev
vagrant ssh
php /var/www/html/vendor/bin/phpunit --bootstrap /var/www/html/tests/init.php tests
```

Working with a custom machine
-----------------------------
If you are working with a vagrant box, you can simply run `vagrant provision`

To install on a custom machine:

1. `cp config.sample.php config.inc.php` and edit the configuration to match your set up
2. `cp sample.htaccess .htaccess` and edit the new file to match your set up
3. `php scripts/install.php`

To update a custom machine

1. `php scripts/update.php`
2. `php scripts/update_libs.php` update libraries if you need to

Working with plugins
------------------
To install a plugin:

1. Add the plugin machine name and options to your `config.inc.php` file (see config.inc.php for examples)
2. run `php scripts/update.php`
3. run `php scripts/update_libs.php` if the plugin defines any libraries

To uninstall a plugin:

1. Remove the plugin from the `config.inc.php` file
2. run `php scripts/update.php`
