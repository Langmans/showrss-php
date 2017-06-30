# ShowRSS PHP

This is tool that adds torrents to Deluge/Transmission from your [ShowRSS][ShowRSS] feed. 
After downloading is complete, it will be moved and sorted to a directory of your choice.

**This is under heavy development, do not use if you don't know what you're doing!**

### Requirements:

* [Git]
* PHP 5.3+ with pdo_sqlite support.
* [composer]  
    * You can also download composer.phar to the project directory.
    * If you are on windows, you can download an 
      [executable that installs composer][composer_exe] 
      for you that adds the composer bin directory to the environment (`%PATH%`)

### Install:

Run the following commands in your terminal:

```bash
git clone https://github.com/rubenvincenten/showrss-php.git
composer install
vendor\bin\doctrine orm:schema-tool:update --force
php cronjob.php
```

After this, default configuration will be available in `config/`. Add your ShowRss user_id there.

Next, use Task Scheduler (windows) or [Cron Tab] to schedule updating. [ShowRSS][ShowRSS] by default updates every half hour. 
You can also set up a task/cron script for when you login or boot your computer/server.

### Upgrading:

Assuming you used git to checkout:
```bash
git pull
composer update
vendor\bin\doctrine orm:schema-tool:update --force
```

[ShowRSS]: https://showrss.info
[git]: https://git-scm.com
[composer]: https://getcomposer.org
[composer_exe]: https://getcomposer.org/Composer-Setup.exe
[Cron Tab]: https://en.wikipedia.org/wiki/Cron