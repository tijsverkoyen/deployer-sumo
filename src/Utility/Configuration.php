<?php

namespace TijsVerkoyen\DeployerSumo\Utility;

use Deployer;
use Symfony\Component\Dotenv\Dotenv;

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

        // check if we have a .env file
        $envPath = Deployer\get('deploy_path') . '/shared/.env';
        if (Deployer\test("[ -f $envPath ]")) {
            $values = (new Dotenv())->parse(
                Deployer\run("cat $envPath")
            );
        }

        return new Configuration($values);
    }

    public static function fromLocal()
    {
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
}
