<?php

namespace TijsVerkoyen\DeployerSumo\Utility;

use Deployer;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

class Configuration
{
    /**
     * @var array
     */
    private $values = [];

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new \RuntimeException(
                "Configuration `$name` does not exist."
            );
        }

        return $this->values[$name];
    }

    public static function fromRemote(): Configuration
    {
        $values = [];

        // check if we have parameters.yml file
        $envPath = Deployer\get('deploy_path') . '/shared/app/config/parameters.yml';

        if (Deployer\test("[ -f $envPath ]")) {
            // get database url from parameters
            $parameters = Yaml::parse(Deployer\run('cat app/config/parameters.yml'))['parameters'];
            $databaseUrl = self::getDatabaseUrlFromParameters($parameters);
            
            if ($databaseUrl !== '') {
                return new Configuration(['DATABASE_URL' => $databaseUrl]);
            }
        }

        // check if we have a .env.local or .env file
        $envPath = Deployer\get('deploy_path') . '/shared/.env';
        if (Deployer\test("[ -f $envPath.local ]")) {
            $envPath .= '.local';
        }

        if (Deployer\test("[ -f $envPath ]")) {
            $values = (new Dotenv())->parse(
                Deployer\run("cat $envPath")
            );
        }

        return new Configuration($values);
    }

    public static function fromLocal(): Configuration
    {
        if (file_exists(getcwd() . '/app/config/parameters.yml')) {
            $path = getcwd() . '/app/config/parameters.yml';
            $parameters = Yaml::parse(file_get_contents($path))['parameters'];
            $databaseUrl = self::getDatabaseUrlFromParameters($parameters);

            if ($databaseUrl !== '') {
                return new Configuration(['DATABASE_URL' => $databaseUrl]);
            }
        }

        if (file_exists(getcwd() . '/.env.local')) {
            $path = getcwd() . '/.env.local';
        } elseif (file_exists(getcwd() . '/.env.dist') && !file_exists(
                getcwd() . '/.env'
            )) {
            $path = getcwd() . '/.env.dist';
        } elseif (file_exists(getcwd() . '/.env')) {
            $path = getcwd() . '/.env';
        }

        $values = (new Dotenv())->parse(
            file_get_contents($path)
        );

        return new Configuration($values);
    }

    private static function getDatabaseUrlFromParameters(array $parameters): string
    {
        if (!array_key_exists('database.host', $parameters)) {
            return '';
        }
        if (!array_key_exists('database.user', $parameters)) {
            return '';
        }
        if (!array_key_exists('database.password', $parameters)) {
            return '';
        }

        return "mysql://{$parameters['database.user']}:{$parameters['database.password']}" .
            "@{$parameters['database.host']}:{$parameters['database.port']}/{$parameters['database.name']}";
    }
}
