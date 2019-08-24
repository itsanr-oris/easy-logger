<?php
/**
 * Created by PhpStorm.
 * User: f-oris
 * Date: 2019/7/9
 * Time: 4:25 PM
 */

namespace Foris\Easy\Logger\Tests\Driver;

use Foris\Easy\Logger\Exception\InvalidConfigException;
use Foris\Easy\Logger\Tests\TestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Foris\Easy\Logger\Driver\Factory;
use Psr\Log\LoggerInterface;
use Monolog\Handler\RotatingFileHandler;

/**
 * Class FactoryTest
 * @package EasySmartProgram\Tests\Support\Log\Driver
 * @author  f-oris <us@f-oris.me>
 * @version 1.0.0
 */
class FactoryTest extends TestCase
{
    /**
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
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
     * @throws \Foris\Easy\Logger\Exception\InvalidConfigException
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
     * @throws InvalidConfigException
     */
    public function testMakeNotExistLoggerDriver()
    {
        $factory = new Factory();
        $logger = $factory->make('not_exists_driver', ['driver' => 'not_exists_driver']);

        $this->assertTrue($logger instanceof LoggerInterface);
        $this->assertSame('log', $logger->getName());
        $this->assertTrue($logger->getHandlers()[0] instanceof RotatingFileHandler);
    }

    /**
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
     */
    public function testMakeStackLoggerDriver()
    {
        $config = [
            'driver' => 'stack',
            'channels' => ['single', 'daily'],
            'total_channels' => [
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

        $factory = new Factory();
        $logger = $factory->make('stack', $config);

        $this->assertTrue($logger instanceof LoggerInterface);
        $this->assertSame('daily', $logger->getName());
        $this->assertTrue($logger->getHandlers()[0] instanceof StreamHandler);
        $this->assertTrue($logger->getHandlers()[1] instanceof RotatingFileHandler);
    }

    /**
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
     */
    public function testAliasNotExistsDriver()
    {
        $factory = new Factory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Driver creator [my-single] not exists!');

        $factory->alias('my-single', 'my-single-2');
    }
}