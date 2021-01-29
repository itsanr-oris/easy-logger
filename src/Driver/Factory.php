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
     * Logger driver aliases
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Logger driver creators,
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
     * Set logger driver config.
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
     * Gets the channel configuration.
     *
     * @param       $channel
     * @param array $default
     * @return array
     */
    public function getChannelConfig($channel, $default = [])
    {
        return isset($this->config['channels'][$channel]) ? $this->config['channels'][$channel] : $default;
    }

    /**
     * Register default logger driver creator
     *
     * @throws InvalidConfigException
     */
    protected function registerDefaultCreator()
    {
        $this->extend($this->singleFileLogDriverCreator(), 'single');
        $this->extend($this->dailyFileLogDriverCreator(), 'daily');
        $this->extend($this->stackLogDriverCreator(), 'stack');
    }

    /**
     * Make and get logger driver instance
     *
     * @param string $channel
     * @param array  $config
     * @return Monolog
     * @throws InvalidParamsException
     */
    public function make($channel, array $config = [])
    {
        $config = array_merge($this->getChannelConfig($channel), $config);

        $driver = isset($config['driver']) ? $config['driver'] : '';
        $creator = isset($this->aliases[$driver]) ? $this->aliases[$driver] : $driver;

        if (!isset($this->creators[$creator])) {
            throw new InvalidParamsException(sprintf('Invalid channel [%s], channel driver not exist!', $channel));
        }

        return $this->creators[$creator]($channel, $config);
    }

    /**
     * Extend logger driver creator
     *
     * @param callable    $factory
     * @param string      $name
     * @param string|null $alias
     * @return $this
     * @throws InvalidConfigException
     */
    public function extend(callable $factory, $name, $alias = null)
    {
        if (isset($this->creators[$name]) || isset($this->aliases[$alias])) {
            throw new InvalidConfigException(sprintf('Log driver [%s] already exists!', $name));
        }

        $this->creators[$name] = $factory;
        !empty($alias) && $this->aliases[$alias] = $name;

        return $this;
    }

    /**
     * Alias logger driver creator
     *
     * @param string $name
     * @param string $alias
     * @return $this
     * @throws InvalidConfigException
     */
    public function alias($name, $alias)
    {
        if (!isset($this->creators[$name])) {
            throw new InvalidConfigException(sprintf('Driver creator [%s] not exists!', $name));
        }

        if (isset($this->aliases[$alias]) || isset($this->creators[$alias])) {
            throw new InvalidConfigException(sprintf('Driver creator [%s] already exists!', $alias));
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
     * Get single-file logger driver instance
     *
     * @return \Closure
     */
    protected function singleFileLogDriverCreator()
    {
        return function ($channel, array $config = []) {
            return new Monolog($channel, [
                $this->prepareHandler(
                    new StreamHandler($config['path'], $this->level($config))
                ),
            ]);
        };
    }

    /**
     * Get daily-file logger driver instance
     *
     * @return \Closure
     */
    protected function dailyFileLogDriverCreator()
    {
        return function ($channel, array $config = []) {
            $days = isset($config['days']) ? $config['days'] : 7;
            return new Monolog($channel, [
                $this->prepareHandler(new RotatingFileHandler($config['path'], $days, $this->level($config))),
            ]);
        };
    }

    /**
     * Get stack logger driver instance
     *
     * @return \Closure
     */
    protected function stackLogDriverCreator()
    {
        return function ($channel, array $config = []) {
            $handlers = [];

            $channels = isset($config['channels']) ? $config['channels'] : [];
            foreach ($channels as $itemChannel) {
                $handlers = array_merge(
                    $handlers,
                    $this->make($itemChannel, $this->getChannelConfig($itemChannel))->getHandlers()
                );
            }

            if (empty($handlers)) {
                throw new InvalidParamsException('Channels can not be empty!');
            }

            return new Monolog($channel, $handlers);
        };
    }
}
