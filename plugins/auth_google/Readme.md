auth_google
===========

This plugin provides authentication via google.  It uses [opauth](http://opauth.org/) to accomplish this.

Please see the opauth [google strategy](https://github.com/opauth/google) for more details

Configuration
-------------
Configuration for this plugin requires three things.  This is an example plugin configuration:

### Create a Google APIs project at https://code.google.com/apis/console/

Note: You will have to create a project if you have not already.

1. Go to `APIs & Auth` -> `Credentials`
2. Click the button to `Add Credentials` -> `OAuth 2.0 Client ID`
3. Set your `Authorized JavaScript origins` to something like `http://localhost:8001`
4. Set your `Authorized redirect URIs` to something like `http://localhost:8001/auth/google/callback`

### Configure the plugin in config.inc.php

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
