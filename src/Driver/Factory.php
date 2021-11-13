<?php

namespace Foris\Easy\Logger\Driver;

use Foris\Easy\Logger\Exception\InvalidParamsException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;
use Foris\Easy\Logger\Exception\InvalidConfigException;

/**
 * Class Factory
 */
class Factory
{
    /**
     * Logger config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Logger channel aliases
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Logger channel creators,
     *
     * @var array
     */
    protected $creators = [];

    /**
     * Factory constructor.
     *
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        $this->setConfig($config)->registerDefaultCreator();
    }

    /**
     * Sets the logger configuration.
     *
     * @param $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Gets the logger configuration.
     *
     * @param $channel
     * @return array
     */
    public function getConfig($channel = null, $default = [])
    {
        return $channel === null
            ? ($this->config)
            : (isset($this->config['channels'][$channel]) ? $this->config['channels'][$channel] : $default);
    }

    /**
     * Gets the channel configuration.
     *
     * @param       $channel
     * @param array $default
     * @return array
     * @deprecated
     */
    public function getChannelConfig($channel, $default = [])
    {
        return $this->getConfig($channel, $default);
    }

    /**
     * Register default logger channel creator
     *
     * @throws InvalidConfigException
     */
    protected function registerDefaultCreator()
    {
        $this->extend($this->singleFileLogChannelCreator(), 'single');
        $this->extend($this->dailyFileLogChannelCreator(), 'daily');
        $this->extend($this->stackLogChannelCreator(), 'stack');
    }

    /**
     * Make and get logger channel instance
     *
     * @param string $channel
     * @param array  $config
     * @return Monolog
     * @throws InvalidParamsException
     */
    public function make($channel, array $config = [])
    {
        $config = array_merge($this->getConfig($channel), $config);

        if (!$this->driverExists($config['driver'])) {
            throw new InvalidParamsException(
                sprintf('Logger channel [%s] configuration error, driver [%s] not exist!', $channel, $config['driver'])
            );
        }

        return call_user_func_array($this->getCreator($config['driver']), [$channel, $config]);
    }

    /**
     * Determine if the logger channel exists.
     *
     * @param $channel
     * @return bool
     * @deprecated
     */
    protected function channelExists($channel)
    {
        return is_callable($this->getCreator($channel));
    }

    /**
     * Determine if the logger driver exists.
     *
     * @param $driver
     * @return bool
     */
    protected function driverExists($driver)
    {
        return is_callable($this->getCreator($driver));
    }

    /**
     * Gets the logger channel creator.
     *
     * @param $channel
     * @return mixed|null
     */
    protected function getCreator($channel)
    {
        if (isset($this->aliases[$channel])) {
            $channel = $this->aliases[$channel];
        }

        if (!isset($this->creators[$channel]) || !is_callable($this->creators[$channel])) {
            return null;
        }

        return $this->creators[$channel];
    }

    /**
     * Extend logger channel creator
     *
     * @param callable    $factory
     * @param string      $name
     * @param string|null $alias
     * @return $this
     * @throws InvalidConfigException
     */
    public function extend(callable $factory, $name, $alias = null)
    {
        if ($this->driverExists($name) || $this->driverExists($alias)) {
            throw new InvalidConfigException(sprintf('Logger driver [%s] already exists!', $name));
        }

        $this->creators[$name] = $factory;
        !empty($alias) && $this->aliases[$alias] = $name;

        return $this;
    }

    /**
     * Alias logger channel creator
     *
     * @param string $name
     * @param string $alias
     * @return $this
     * @throws InvalidConfigException
     * @deprecated
     */
    public function alias($name, $alias)
    {
        if (!$this->driverExists($name)) {
            throw new InvalidConfigException(sprintf('Logger driver [%s] not exists!', $name));
        }

        if ($this->driverExists($alias)) {
            throw new InvalidConfigException(sprintf('Logger driver [%s] already exists!', $alias));
        }

        $this->aliases[$alias] = $name;
        return $this;
    }

    /**
     * Prepare the handler for usage by Monolog.
     *
     * @param \Monolog\Handler\HandlerInterface $handler
     *
     * @return \Monolog\Handler\HandlerInterface
     */
    protected function prepareHandler(HandlerInterface $handler)
    {
        return method_exists($handler, 'setFormatter') ? $handler->setFormatter($this->formatter()) : $handler;
    }

    /**
     * Get a Monolog formatter instance.
     *
     * @return \Monolog\Formatter\FormatterInterface
     */
    protected function formatter()
    {
        $formatter = new LineFormatter(null, null, true, true);
        $formatter->includeStacktraces();

        return $formatter;
    }

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param array $config
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function level(array $config)
    {
        return isset($config['level']) ? $config['level'] : 'debug';
    }

    /**
     * Get single-file logger channel instance
     *
     * @return \Closure
     */
    protected function singleFileLogChannelCreator()
    {
        return function ($channel, array $config = []) {
            $config = array_merge($this->getConfig('single'), $config);

            return new Monolog($channel, [
                $this->prepareHandler(new StreamHandler($config['path'], $this->level($config)))
            ]);
        };
    }

    /**
     * Get daily-file logger channel instance
     *
     * @return \Closure
     */
    protected function dailyFileLogChannelCreator()
    {
        return function ($channel, array $config = []) {
            $config = array_merge($this->getConfig('daily'), $config);
            $days = isset($config['days']) ? $config['days'] : 7;

            return new Monolog($channel, [
                $this->prepareHandler(new RotatingFileHandler($config['path'], $days, $this->level($config)))
            ]);
        };
    }

    /**
     * Get stack logger channel instance
     *
     * @return \Closure
     */
    protected function stackLogChannelCreator()
    {
        return function ($channel, array $config = []) {
            $handlers = [];
            $config = array_merge($this->getConfig('stack'), $config);

            $channels = isset($config['channels']) ? $config['channels'] : [];
            foreach ($channels as $itemChannel) {
                $handlers = array_merge(
                    $handlers,
                    $this->make($itemChannel, $this->getConfig($itemChannel))->getHandlers()
                );
            }

            if (empty($handlers)) {
                throw new InvalidParamsException('Channels can not be empty!');
            }

            return new Monolog($channel, $handlers);
        };
    }
}
