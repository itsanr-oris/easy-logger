<?php /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

namespace Foris\Easy\Logger\Tests;

use Mockery;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Foris\Easy\Logger\Logger;
use Foris\Easy\Logger\Driver\Factory;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerTest
 */
class LoggerTest extends TestCase
{
    /**
     * Logger driver factory instance.
     *
     * @var Factory
     */
    protected $factory;

    /**
     * Monolog test handler.
     *
     * @var TestHandler
     */
    protected $testLoggerHandler;

    /**
     * Gets the test handler instance.
     *
     * @return TestHandler
     */
    protected function handler()
    {
        if (empty($this->testLoggerHandler)) {
            $this->testLoggerHandler = new TestHandler();
        }

        return $this->testLoggerHandler;
    }

    /**
     * Gets the logger factory instance.
     *
     * @return Factory
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     */
    protected function factory()
    {
        if (empty($this->factory)) {
            $factory = new Factory();
            $handler = $this->handler();

            $callback = function ($channel) use ($handler) {
                $logger = new \Monolog\Logger($channel);
                return $logger->pushHandler($handler);
            };

            $factory->extend($callback, 'test');
            $this->factory = $factory;
        }

        return $this->factory;
    }

    /**
     * Test logger configuration.
     *
     * @return array
     */
    protected function config()
    {
        return [
            'default' => 'test',
            'channels' => [
                'stack' => [
                    'driver' => 'stack',
                    'channels' => ['test']
                ],
                'test' => [
                    'driver' => 'test',
                ],
                'single' => [
                    'driver' => 'single',
                    'path' => sys_get_temp_dir() . '/logs/easy-logger.log',
                    'level' => 'debug',
                ],
                'business_module_channel' => [
                    'driver' => 'single',
                    'path' => sys_get_temp_dir() . '/logs/easy-logger.log',
                    'level' => 'debug',
                ]
            ]
        ];
    }

    /**
     * Gets the logger instance.
     *
     * @return Logger
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     */
    protected function logger()
    {
        return new Logger($this->factory(), $this->config());
    }

    /**
     * Test gets the logger instance.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     */
    public function testGetCacheInstanceWithDriverFactory()
    {
        $factory = Mockery::mock(Factory::class);
        $factory->makePartial();

        $cache = new Logger($factory);
        $this->assertSame($factory, $cache->getDriverFactory());
    }

    /**
     * Test gets the logger instance.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     */
    public function testGetCacheInstanceWithConfiguration()
    {
        $defaultConfig = [
            'default' => 'single',
            'channels' => [
                'single' => [
                    'driver' => 'single',
                    'path' => sys_get_temp_dir() . '/logs/easy-logger.log',
                    'level' => 'debug',
                ],
            ],
        ];

        $cache = new Logger($this->config());
        $this->assertEquals(array_merge($defaultConfig, $this->config()), $cache->getConfig());
    }

    /**
     * Test get logger configuration.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     */
    public function testGetConfig()
    {
        $this->assertEquals($this->config(), $this->logger()->getConfig());
    }

    /**
     * Test add log
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testAddLogMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->log('debug', 'log message');
        list($record) = $handler->getRecords();
        $this->assertEquals(\Monolog\Logger::DEBUG, $record['level']);
        $this->assertEquals('log message', $record['message']);
    }

    /**
     * Test log an emergency message.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testLogEmergencyMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->emergency('emergency message');
        list($record) = $handler->getRecords();
        $this->assertEquals('emergency message', $record['message']);
    }

    /**
     * Test log an alert message
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testLogAlertMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->alert('alert message');
        list($record) = $handler->getRecords();
        $this->assertEquals('alert message', $record['message']);
    }

    /**
     * Test log a critical message
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testLogCriticalMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->critical('critical message');
        list($record) = $handler->getRecords();
        $this->assertEquals('critical message', $record['message']);
    }

    /**
     * Test log an error message.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testLogErrorMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->error('error message');
        list($record) = $handler->getRecords();
        $this->assertEquals('error message', $record['message']);
    }

    /**
     * Test log an warning message.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testLogWarningMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->warning('warning message');
        list($record) = $handler->getRecords();
        $this->assertEquals('warning message', $record['message']);
    }

    /**
     * Test log an notice message.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testLogNoticeMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->notice('notice message');
        list($record) = $handler->getRecords();
        $this->assertEquals('notice message', $record['message']);
    }

    /**
     * Test log an info message.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testLogInfoMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->info('info message');
        list($record) = $handler->getRecords();
        $this->assertEquals('info message', $record['message']);
    }

    /**
     * Test log an debug message.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testLogDebugMessage()
    {
        $logger = $this->logger();
        $handler = $this->handler();

        $logger->debug('debug message');
        list($record) = $handler->getRecords();
        $this->assertEquals('debug message', $record['message']);
    }

    /**
     * Test change the logger channel
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testChangeChannel()
    {
        $logger = $this->logger();
        $logger->channel('business_module_channel');
        $this->assertEquals('business_module_channel', $logger->driver()->getName());
        $this->assertInstanceOf(StreamHandler::class, $logger->driver()->getHandlers()[0]);
    }

    /**
     * Test stack channel
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testStackChannel()
    {
        $logger = $this->logger();
        $logger->stack(['single', 'test']);
        $this->assertEquals('stack', $logger->driver()->getName());
        $this->assertTrue($logger->driver()->getHandlers()[0] instanceof StreamHandler);
        $this->assertTrue($logger->driver()->getHandlers()[1] instanceof TestHandler);
    }

    /**
     * Test extend logger driver.
     *
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
     * @throws \Foris\Easy\Logger\Exception\InvalidParamsException
     */
    public function testExtendLoggerChannel()
    {
        $config = $this->config();
        $config['channels']['mock_channel'] = [
            'driver' => 'mock_channel'
        ];

        $logger = new Logger($config);
        $channel = Mockery::mock(LoggerInterface::class);

        // extend driver, not channel
        $logger->extend('mock_channel', function () use ($channel) {
            return $channel;
        });

        $this->assertSame($channel, $logger->channel('mock_channel')->driver());
    }
}
