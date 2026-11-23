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

Build your project's assets by executing `npm run build` locally.

If `nvm` is detected `nvm` will be used.

This task is executed after `deploy:update_code`.

### `sumo:assets:npm-install`

Runs `npm install` locally

If `nvm` is detected `nvm` will be used.

### `sumo:assets:upload` (internal task)

Uploads `public/build` to the stage.

This task is executed after `sumo:assets:build`.

### `sumo:chrome:install`

Downloads the latest stable [Chrome for Testing](https://googlechromelabs.github.io/chrome-for-testing/) into
`{{chrome_path}}` (default: `{{deploy_path}}/shared/chrome`) and points `{{chrome_path}}/current` to the folder
with the binary. Use it for headless Chrome (e.g. PDF generation) on servers where the distro only offers Chromium
as a snap, which can't be started from PHP-FPM.

Each version gets its own folder, so running the task again only downloads when there is a new stable version, and
you can roll back by pointing `current` to an older folder. The task prints the binary to use:
`{{chrome_path}}/current/chrome`. If Chrome doesn't start, it lists the missing system libraries. The packages Chrome
needs are listed in `{{chrome_path}}/current/deb.deps` (Debian/Ubuntu) and `rpm.deps`; installing them needs root.

The server needs `curl` and `unzip`. Supported: Linux x86_64 and arm64.

The task is not part of the deploy flow. Run it by hand, or add it yourself:

```php
after('deploy:shared', 'sumo:chrome:install');
```

### `sumo:config:alter`

Alter the config file for local use.

### `sumo:config:get`

Get the required config files from the host.

### `sumo:db:create`

Creates a database on our dev server. This task can will only run on the
host with the stage "staging".

It will output the credentials.

### `sumo:db:create-local`

Creates a database on your local MySQL instance.

### `sumo:db:get`

Run this task to replace your local database with the remote database.
Be aware that no backup will be made.

### `sumo:db:info`

This task will output the credentials of the existing database on the dev server.
This task can will only run on the host with the stage "staging".

### `sumo:db:put`

Use this command to replace the remote database with your local database.

### `sumo:files:get`

Run this task to replace your local files with the remote files.
Be aware that no backup will be made.

### `sumo:files:put`

Run this task to replace the remote files with your local files.
Be aware that no backup will be made.

### `sumo:notifications:deploy`

Notify our webhooks on a deploy. This task is automatically added into the flow.

This task is executed after `deploy`.

### `sumo:opcache:reset-file`

Clears opcache and statcache using a file strategy.

### `sumo:project:init`

This is an aggregate task, it will run all the following tasks:

* `sumo:config:get`
* `sumo:config:alter`
* `sumo:db:create-local`
* `sumo:db:get`
* `sumo:files:get`
* `sumo:assets:fix-node-version`
* `sumo:assets:npm-install`
* `sumo:assets:build`

It can be used to locally set up a project that is already on the staging or production server.

### `sumo:redirect:enable`

Enable a redirect page, all traffic will be redirected to this page.

### `sumo:ssl:create`

Create the SSL certificate for the staging url

**This will only work on our dev/staging server

### `sumo:symlink:crontab`

**This will only work on Cloudstar servers**

If `.crontab` exists in your project a symlink for `~/.crontab/XXX.crontab` to your file is
created.

After a short period the content of your `.crontab` file will be used as crontab.

### `sumo:symlink:document-root`

Creates the needed symlinks to link the document root to the correct folder.

This task is executed after `deploy:symlink`.

## License

Licensed under the [MIT license](https://github.com/tijsverkoyen/deployer-sumo/blob/master/LICENSE).
