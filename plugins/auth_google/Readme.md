auth_google
===========

This plugin provides authentication via google.  It uses [opauth](http://opauth.org/) to accomplish this.

Please see the opauth [google strategy](https://github.com/opauth/google) for more details

Configuration
-------------
Configuration for this plugin requires three things.  This is an example plugin configuration:

1. Create a Google APIs project at https://code.google.com/apis/console/
2. Configure thi plugin in config.inc.php

```
\SiteMaster\Config::set('PLUGINS', array(
  'auth_google' => array(
      'security_salt' => 'enter random string here',
      'Strategy' => array(
          'Google' => array(
              'client_id' => 'enter client id here',
              'client_secret' => 'enter client secret here'
          )
      )
  )
));
```
