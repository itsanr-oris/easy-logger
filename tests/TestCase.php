<?php
/**
 * Created by PhpStorm.
 * User: f-oris
 * Date: 2019/7/9
 * Time: 4:29 PM
 */

namespace Foris\Easy\Logger\Tests;

use Mockery;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Tear down the test case.
     */
    public function tearDown(): void
    {
        if (class_exists('Mockery')) {
            if ($container = Mockery::getContainer()) {
                $this->addToAssertionCount($container->mockery_getExpectationCount());
            }

            Mockery::close();
        }
    }
}