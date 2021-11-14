## 简介
 
基于monolog/monolog简单封装的日志扩展包。
 
[![Build Status](https://travis-ci.com/itsanr-oris/easy-logger.svg?branch=master)](https://travis-ci.com/itsanr-oris/easy-logger)
[![codecov](https://codecov.io/gh/itsanr-oris/easy-logger/branch/master/graph/badge.svg?token=E94oWQqjh0)](https://codecov.io/gh/itsanr-oris/easy-logger)
[![Latest Stable Version](https://poser.pugx.org/f-oris/easy-logger/v)](//packagist.org/packages/f-oris/easy-logger)
[![Latest Unstable Version](https://poser.pugx.org/f-oris/easy-logger/v/unstable)](//packagist.org/packages/f-oris/easy-logger)
[![Total Downloads](https://poser.pugx.org/f-oris/easy-logger/downloads)](//packagist.org/packages/f-oris/easy-logger)
[![License](https://poser.pugx.org/f-oris/easy-logger/license)](//packagist.org/packages/f-oris/easy-logger)

## 功能

- 支持多种策略进行日志记录
- 支持自定义扩展日志记录通道
- 可自定义日志配置行为，无配置的情况下，日志写入到系统临时目录下

## 安装

通过composer引入扩展包

```bash
composer require f-oris/easy-logger:^1.1
```

## 配置

参考`config.example.php`文件

## 基本用法

#### 1. 写入日志

```php
<?php

use Foris\Easy\Logger\Logger;

$config = [
    // ...
];
$logger = new Logger($config);

/**
 * 写入日志信息
 * 
 * 可以使用不同的方法写入不同级别的日志信息
 * 下面两种写法等价
 */
$logger->debug('调试日志', ['context' => 'context']);
$logger->log('debug', '调试日志', ['context' => 'context']);

/**
 * 日志信息信息写入指定通道 
 */
$logger->channel('channel')->debug('日志信息写入channel通道', ['context' => 'context']);
```

#### 2. 扩展自定义Logger driver

```php
<?php

// 扩展的driver需要实现Psr\Log\LoggerInterface接口规范

$callback = function ($channel) {
    $logger = new \Monolog\Logger($channel);
    return $logger->pushHandler(new \Monolog\Handler\TestHandler());
};

$factory = new \Foris\Easy\Logger\Driver\Factory();
$factory->extend($callback, 'test_driver');

$config = [
    // ...
    
    "channels" => [
        // ...
        
        "test" => [
            "driver" => "test_driver",    
        ]    
    ]
];

$logger = new \Foris\Easy\Logger\Logger($factory, $config);
$logger->channel('test')->debug('This is a debug message');

```

## License

MIT License

Copyright (c) 2019-present F.oris <us@f-oris.me>
