<?php

namespace Foris\Easy\Logger;

use Foris\Easy\Logger\Driver\Factory;
use Foris\Easy\Logger\Exception\InvalidConfigException;
use Foris\Easy\Logger\Exception\InvalidParamsException;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
 */
class Logger implements LoggerInterface
{
    /**
     * Logger configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Logger driver instance.
     *
     * @var LoggerInterface
     */
    protected $driver;

    /**
     * Logger driver factory
     *
     * @var Factory
     */
    protected $driverFactory;

    /**
     * Logger instance.
     *
     * @var Logger
     */
    protected static $instance;

    /**
     * Logger constructor.
     *
     * @param Factory $factory
     * @param array   $config
     * @throws InvalidConfigException
     */
    public function __construct($factory = null, $config = [])
    {
        if ($factory instanceof Factory) {
            $this->setDriverFactory($factory);
        }

        if (is_array($factory)) {
            $config = array_merge($factory, $config);
        }

        $this->setConfig(array_merge($this->defaultConfig(), $config));
    }

    /**
     * Gets the default configuration.
     *
     * @return array
     */
    protected function defaultConfig()
    {
        return [
            'default' => 'single',
            'channels' => [
                'single' => [
                    'driver' => 'single',
                    'path' => sys_get_temp_dir() . '/logs/easy-logger.log',
                    'level' => 'debug',
                ],
            ],
        ];
    }

    /**
     * Sets the logger configuration.
     *
     * @param array $config
     * @throws InvalidConfigException
     */
    public function setConfig(array $config = [])
    {
        $this->config = $config;
        $this->getDriverFactory()->setConfig($config);
    }

    /**
     * Gets the logger configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set logger driver factory
     *
     * @param Factory $factory
     * @return $this
     */
    public function setDriverFactory(Factory $factory = null)
    {
        $this->driverFactory = $factory;
        return $this;
    }

    /**
     * Get logger driver factory instance
     *
     * @return Factory
     * @throws Exception\InvalidConfigException
     */
    public function getDriverFactory()
    {
        if (!$this->driverFactory instanceof Factory) {
            $this->driverFactory = new Factory($this->config);
        }

        return $this->driverFactory;
    }

    /**
     * Set logger channel
     *
     * @param string $channel
     * @return $this
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function channel($channel)
    {
        $this->driver = $this->getDriverFactory()->make($channel);
        return $this;
    }

    /**
     * Set stack logger channel
     *
     * @param array $channels
     * @return $this
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function stack(array $channels)
    {
        $this->driver = $this->getDriverFactory()->make('stack', ['driver' => 'stack', 'channels' => $channels]);
        return $this;
    }

    /**
     * Get logger driver instance
     *
     * @return LoggerInterface|\Monolog\Logger
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function driver()
    {
        if (!$this->driver instanceof LoggerInterface) {
            $this->driver = $this->getDriverFactory()->make($this->config['default']);
        }

        return $this->driver;
    }

    /**
     *  Extend logger driver.
     *
     * @param          $name
     * @param callable $factory
     * @return $this
     * @throws InvalidConfigException
     */
    public function extend($name, callable $factory)
    {
        $this->getDriverFactory()->extend($factory, $name);
        return $this;
    }

    /**
     * Log emergency message
     *
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function emergency($message, array $context = array())
    {
        $this->driver()->emergency($message, $context);
    }

    /**
     * Log alert message
     *
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function alert($message, array $context = array())
    {
        $this->driver()->alert($message, $context);
    }

    /**
     * Log critical message
     *
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function critical($message, array $context = array())
    {
        $this->driver()->critical($message, $context);
    }

    /**
     * Log error message
     *
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function error($message, array $context = array())
    {
        $this->driver()->error($message, $context);
    }

    /**
     * Log warning message
     *
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function warning($message, array $context = array())
    {
        $this->driver()->warning($message, $context);
    }

    /**
     * Log notice message
     *
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function notice($message, array $context = array())
    {
        $this->driver()->notice($message, $context);
    }

    /**
     * Log info message
     *
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function info($message, array $context = array())
    {
        $this->driver()->info($message, $context);
    }

    /**
     * Log debug message
     *
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function debug($message, array $context = array())
    {
        $this->driver()->debug($message, $context);
    }

    /**
     * Log message
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function log($level, $message, array $context = array())
    {
        $this->driver()->log($level, $message, $context);
    }
}
