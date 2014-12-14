ShowRSS PHP
===========
This is tool that adds torrents to deluge/transmission from your showrss feed. 
After downloading is complete, it will be moved and sorted to a directory of your choice.

**This is under heavy development, do not use if you don't know what you're doing!**

Requirements:
-------------

* [Git](http://git-scm.com/)
* PHP 5.3+ with pdo_sqlite support. If you are on linux, 
* [composer](http://getcomposer.org) 
    * You can also download composer.phar to the project directory.
    * If you are on windows, you can download an 
      [executable that installs composer](https://getcomposer.org/Composer-Setup.exe) 
      for you that adds the composer bin directory to the environment(`%PATH%`)
      
#### Optional:


Install:
--------

Run the following commands in your terminal:

```
git clone https://github.com/rubenvincenten/showrss-php.git
composer install
php cronjob.php
```

After this, default configuration will be available in /config/. Add your showrss user_id there.

Next, use task manager(windows) or crontab to schedule updating. ShowRSS by default updates every half hour. 
You can also set up a task/cron script for when you login or boot your computer/server.

Upgrading:
----------

Assuming you used git to checkout:

```
git pull
composer update
```
