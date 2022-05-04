<?php

namespace Deployer;

require_once 'src/Utility/Configuration.php';
require_once 'src/Utility/Database.php';
require_once 'src/Utility/Git.php';
require_once 'src/Utility/Path.php';

require_once 'recipe/assets.php';
require_once 'recipe/database.php';
require_once 'recipe/config.php';
require_once 'recipe/files.php';
require_once 'recipe/notifications.php';
require_once 'recipe/redirect.php';
require_once 'recipe/symlink.php';
require_once 'recipe/opcache.php';
require_once 'recipe/project.php';
require_once 'recipe/ssl.php';
