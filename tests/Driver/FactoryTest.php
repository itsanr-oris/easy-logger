<?php

namespace Foris\Easy\Logger\Tests\Driver;

use Foris\Easy\Logger\Exception\InvalidConfigException;
use Foris\Easy\Logger\Exception\InvalidParamsException;
use Foris\Easy\Logger\Tests\TestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Foris\Easy\Logger\Driver\Factory;
use Psr\Log\LoggerInterface;
use Monolog\Handler\RotatingFileHandler;

/**
 * Class FactoryTest
 */
class FactoryTest extends TestCase
{
    /**
     * Test extend logger driver.
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function testExtendCustomerDriver()
    {
        $factory = new Factory();

        $logger = new MonoLogger('extend');
        $callable = function () use ($logger){
            return $logger;
        };

        $factory->extend($callable, 'extend');
        $this->assertSame($logger, $factory->make('extend', ['driver' => 'extend']));
    }

    /**
     * Test override an exist logger driver.
     *
     * @throws InvalidConfigException
     */
    public function testExtendExistsDriver()
    {
        $factory = new Factory();

        $logger = new MonoLogger('single');
        $callable = function () use ($logger){
            return $logger;
        };

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Log driver [single] already exists!');

        $factory->extend($callable, 'single');
    }

    /**
     * Test make not exist logger driver.
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function testMakeNotExistLoggerDriver()
    {
        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionMessage('Invalid channel [not_exists_driver], channel driver not exist!');

        $factory = new Factory();
        $factory->make('not_exists_driver', ['driver' => 'not_exists_driver']);
    }

    /**
     * Test make a single logger driver instance.
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function testMakeSingleLoggerDriver()
    {
        $factory = new Factory();
        $logger = $factory->make('single', [
            'driver' => 'single',
            'path' => sys_get_temp_dir() . '/logs/smart-program.log',
        ]);

        $this->assertTrue($logger instanceof LoggerInterface);
        $this->assertSame('single', $logger->getName());
        $this->assertTrue($logger->getHandlers()[0] instanceof StreamHandler);
    }

    /**
     * Test make a daily logger driver instance.
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function testMakeDailyLoggerDriver()
    {
        $factory = new Factory();
        $logger = $factory->make('daily', [
            'driver' => 'daily',
            'path' => sys_get_temp_dir() . '/logs/smart-program.log',
        ]);

        $this->assertTrue($logger instanceof LoggerInterface);
        $this->assertSame('daily', $logger->getName());
        $this->assertTrue($logger->getHandlers()[0] instanceof RotatingFileHandler);
    }

    /**
     * Test make a stack logger driver instance.
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function testMakeStackLoggerDriver()
    {
        $config = [
            'default' => 'stack',
            'channels' => [
                'stack' => [
                    'driver' => 'stack',
                    'channels' => ['single', 'daily'],
                ],
                'single' => [
                    'driver' => 'single',
                    'path' => sys_get_temp_dir() . '/logs/smart-program.log',
                ],
                'daily' => [
                    'driver' => 'daily',
                    'path' => sys_get_temp_dir() . '/logs/smart-program.log',
                ],
            ],
        ];

        $factory = new Factory($config);
        $logger = $factory->make('stack');

        $this->assertTrue($logger instanceof LoggerInterface);
        $this->assertSame('stack', $logger->getName());
        $this->assertTrue($logger->getHandlers()[0] instanceof StreamHandler);
        $this->assertTrue($logger->getHandlers()[1] instanceof RotatingFileHandler);
    }

    /**
     * Test alias a logger driver.
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function testAliasDriver()
    {
        $factory = new Factory();
        $factory->alias('single', 'my-single');

        $logger = $factory->make('single', [
            'driver' => 'single',
            'path' => sys_get_temp_dir() . '/logs/smart-program.log',
        ]);

        $myLogger = $factory->make('my-single', [
            'driver' => 'single',
            'path' => sys_get_temp_dir() . '/logs/smart-program.log',
        ]);

        $this->assertEquals($logger->getHandlers(), $myLogger->getHandlers());
    }

    /**
     * Test alias logger driver with a duplicate alias.
     *
     * @throws InvalidConfigException
     */
    public function testDuplicateAlias()
    {
        $factory = new Factory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Driver creator [my-single] already exists!');

        $factory->alias('single', 'my-single');
        $factory->alias('single', 'my-single');

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Driver creator [single] already exists!');

        $factory->alias('daily', 'single');
    }

    /**
     * Test alias a not-exist logger driver.
     *
     * @throws InvalidConfigException
     */
    public function testAliasNotExistsDriver()
    {
        $factory = new Factory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Driver creator [my-single] not exists!');

        $factory->alias('my-single', 'my-single-2');
    }

    /**
     * Test make stack logger driver with empty channels.
     *
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     */
    public function testMakeStackLoggerDriverWithEmptyChannels()
    {
        $factory = new Factory();

        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionMessage('Channels can not be empty!');

        $factory->make('stack', ['driver' => 'stack', 'channels' => []]);
    }
}
