<?php

return [
    /**
     * default log channel
     */
    'default' => 'single',

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
            'path' => sys_get_temp_dir() . '/logs/easy-logger.log',
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => sys_get_temp_dir() . '/logs/easy-logger.log',
            'level' => 'debug',
        ],
    ],
];
