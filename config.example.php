<?php
/**
 * Created by PhpStorm.
 * User: f-oris
 * Date: 2019/7/9
 * Time: 4:25 PM
 */

return [
    /**
     * default log channel
     */
    'default' => 'stack',

    /**
     * available log channels
     */
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'daily'],
        ],

        'single' => [
            'driver' => 'single',
            'path' => __DIR__ . '/logs/easy-logger.log',
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => __DIR__ . '/logs/easy-logger.log',
            'level' => 'info',
        ],
    ],
];