<?php

namespace Foris\Easy\Logger;

use Psr\Log\LoggerInterface;
use Foris\Easy\Logger\Driver\Factory as DriverFactory;

/**
 * Class Logger
 */
class Logger implements LoggerInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var LoggerInterface
     */
    protected $driver;

    /**
     * @var DriverFactory
     */
    protected $driverFactory;

    /**
     * Logger constructor.
     *
     * @param DriverFactory $factory
     * @param array         $config
     */
    public function __construct(DriverFactory $factory, array $config = [])
    {
        $this->setDriverFactory($factory)->initConfig($config);
    }

    /**
     * Init logger driver config
     *
     * @param $config
     * @return $this
     */
    protected function initConfig($config)
    {
        $this->config = $config;
        $this->getDriverFactory()->setConfig($config);
        return $this;
    }

    /**
     * Set logger driver factory
     *
     * @param DriverFactory $factory
     * @return $this
     */
    public function setDriverFactory(DriverFactory $factory)
    {
        $this->driverFactory = $factory;
        return $this;
    }

    /**
     * Get logger driver factory instance
     *
     * @return DriverFactory
     */
    public function getDriverFactory() : DriverFactory
    {
        return $this->driverFactory;
    }

    /**
     * Set logger channel
     *
     * @param string $channel
     * @return $this
     */
    public function channel(string $channel)
    {
        $this->config['default'] = $channel;
        $this->driver = null;
        return $this;
    }

    /**
     * Set stack logger channel
     *
     * @param array $channels
     * @return $this
     */
    public function stack(array $channels)
    {
        $this->config['default'] = 'stack';
        $this->config['channels']['stack']['channels'] = $channels;
        $this->initConfig($this->config);
        $this->driver = null;
        return $this;
    }

    /**
     * Get logger driver instance
     *
     * @return LoggerInterface
     */
    public function driver() : LoggerInterface
    {
        if (!$this->driver instanceof LoggerInterface) {
            $channel = $this->config['default'] ?? null;
            $config = $this->config['channels'][$channel] ?? [];
            $this->driver = $this->getDriverFactory()->make($channel, $config);
        }

        return $this->driver;
    }

    /**
     * Log emergency message
     *
     * @param string $message
     * @param array  $context
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
     */
    public function log($level, $message, array $context = array())
    {
        $this->driver()->log($level, $message, $context);
    }
}
