<?php
namespace Lavender\Account\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Auth\EloquentUserProvider;

class Manager extends AuthManager
{

    protected $name;

    protected $config;

    /**
     * @param \Illuminate\Foundation\Application $app
     * @param $name
     * @param $config
     */
    public function __construct($app, $name, $config)
    {
        parent::__construct($app);

        $this->name = $name;

        $this->config = $config;
    }

    protected function createDriver($driver)
    {
        $guard = parent::createDriver($driver);

        $guard->setCookieJar($this->app['cookie']);
        $guard->setDispatcher($this->app['events']);

        return $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
    }

    /**
     * @param string $driver
     * @return \Illuminate\Auth\Guard|Guard
     */
    protected function callCustomCreator($driver)
    {
        $custom = parent::callCustomCreator($driver);

        if($custom instanceof Guard) return $custom;

        return new Guard($custom, $this->app['session.store'], $this->name);
    }

    /**
     * @return Guard
     */
    public function createDatabaseDriver()
    {
        $provider = $this->createDatabaseProvider();

        return new Guard($provider, $this->app['session.store'], $this->name);
    }

    /**
     * @return DatabaseUserProvider
     */
    protected function createDatabaseProvider()
    {
        $connection = $this->app['db']->connection();
        $table = $this->config['table'];

        return new DatabaseUserProvider($connection, $this->app['hash'], $table);
    }

    /**
     * @return Guard
     */
    public function createEloquentDriver()
    {
        $provider = $this->createEloquentProvider();

        return new Guard($provider, $this->app['session.store'], $this->name);
    }

    /**
     * @return EloquentUserProvider
     */
    protected function createEloquentProvider()
    {
        $model = $this->config['model'];

        return new EloquentUserProvider($this->app['hash'], $model);
    }

    /**
     * @return mixed
     */
    public function getDefaultDriver()
    {
        return $this->config['driver'];
    }

    /**
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
        $this->config['driver'] = $name;
    }
}
