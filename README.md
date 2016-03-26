Roundcube linotp login plugin
=============================

Introduction
------------

This plugin adds an additional input box to the login form where the user has to additionally enter the Linotp OTP code in order to successfully authenticate.

This plugin has been tested with Roundcube version 1.1 and LinOTP server had been setup in Ubuntu 14.04 and Google Authenticator was used for generating the OTP code.


Installation
------------

* LinOTP server should be setup and configured (instructions for doing this are beyond the scope of this README)
* Upload the linotp plugin to roundcube plugins directory.  
```
* Activate the plugin by adding it in roundcube config file (config/main.inc.php).  
```
$rcmail_config['plugins'] = array('linotp');
```

Configuration
-------------

The linotp server and port must be configured by renaming config.inc.php.dist to config.inc.php and modifying it. You can also set an emergency password that can be used if the linotp server cannot be contacted.


LICENCE
-------

GPLv3


Author
------

Joerg Hillebrand (hillebrand@jhillebrand.de)

