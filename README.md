## 简介
 
参考laravel log组件，基于monolog/monolog简单封装的日志扩展包，基本使用方式参考laravel日志使用方式。
 
[![Latest Stable Version](https://poser.pugx.org/f-oris/easy-logger/v)](//packagist.org/packages/f-oris/easy-logger) [![Total Downloads](https://poser.pugx.org/f-oris/easy-logger/downloads)](//packagist.org/packages/f-oris/easy-logger) [![Latest Unstable Version](https://poser.pugx.org/f-oris/easy-logger/v/unstable)](//packagist.org/packages/f-oris/easy-logger) [![License](https://poser.pugx.org/f-oris/easy-logger/license)](//packagist.org/packages/f-oris/easy-logger)

## 功能

- 支持多种策略进行日志记录
- 支持自定义扩展日志记录通道
- 可自定义日志配置行为，无配置的情况下，日志写入到系统临时目录下

## 安装

通过composer引入扩展包

```bash
composer require f-oris/easy-logger
```

## 配置

参考`config.example.php`文件

## 用法

```php
<?php

$logger = new \Foris\Easy\Logger\Logger();
$logger->debug('This is a debug message');
// 在sys_get_temp_dir() . '/logs/easy-logger.log'文件中可以找到相关日志内容

```

## 扩展自定义Logger driver

```php
<?php

$callback = function ($channel) {
    $logger = new \Monolog\Logger($channel);
    return $logger->pushHandler(new \Monolog\Handler\TestHandler());
};

$factory = new \Foris\Easy\Logger\Driver\Factory();
$factory->extend($callback, 'test');

$config = [
    // ...
    
    "channels" => [
        // ...
        
        "test" => [
            "driver" => "test",    
        ]    
    ]
];

$logger = new \Foris\Easy\Logger\Logger($factory, $config);
$logger->channel('test')->debug('This is a debug message');

```

## License

MIT License

Copyright (c) 2019-present F.oris <us@f-oris.me>
