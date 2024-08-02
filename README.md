# Deployer Sumo recipe

Recipe for usage with [Deployer](https://deployer.org/). It includes tasks
specific for [SumoCoders](https://sumocoders.be).

## Installing

~~~sh
composer require tijsverkoyen/deployer-sumo --dev
~~~

Include recipes in `deploy.php` file.

```php
require __DIR__ . '/vendor/tijsverkoyen/deployer-sumo/sumo.php';
```


## Available tasks

### `sumo:assets:build`

Build your project's assets using Webpack.

### `sumo:db:create`

Creates a database on our dev server. This task can will only run on the
host with the stage "staging".

It will output the credentials.

### `sumo:db:info`

This task will output the credentials of the existing database on the dev server.
This task can will only run on the host with the stage "staging".

### `sumo:db:create-local`

Creates a database on your local MySQL instance.

### `sumo:db:get`

Run this task to replace your local database with the remote database.
Be aware that no backup will be made.

### `sumo:db:put`

Use this command to replace the remote database with your local database.

### `sumo:project:init`

This is an aggregate task, it will run all the following tasks:
* sumo:db:create-local
* sumo:db:get
* sumo:config:get
* sumo:config:alter
* sumo:files:get
* sumo:assets:fix-node-version
* sumo:assets:npm-install
* sumo:assets:build

It can be used to locally set up a project that is already on the staging or production server.
### `sumo:files:get`

Run this task to replace your local files with the remote files.
Be aware that no backup will be made.

### `sumo:files:put`

Run this task to replace the remote files with your local files.
Be aware that no backup will be made.

### `sumo:notifications:deploy`   

Notify our webhooks on a deploy. This task is automatically added into the flow.

### `sumo:redirect:enable`
        
Enable a redirect page, all traffic will be redirected to this page.
  

### `sumo:symlink:crontab`

**This will only work on Cloudstar servers**

If `.crontab` exists in your project a symlink for `~/.crontab/XXX.crontab` to your file is 
created.

After a short period the content of your `.crontab` file will be used as crontab.

### `sumo:symlink:document-root`
  
Creates the needed symlinks to link the document root to the correct folder.

### `sumo:opcache:reset-file`

Clears opcache and statcache using a file strategy.
  

## License

Licensed under the [MIT license](https://github.com/tijsverkoyen/deployer-sumo/blob/master/LICENSE).
