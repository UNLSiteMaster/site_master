site_master
===========

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
