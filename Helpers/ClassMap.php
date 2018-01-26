<?php



return [
    'namespaces' => [
        'FastRoute'=>dirname(__DIR__).DS.'Library'.DS.'nikic'.DS.'FastRoute'.DS.'src',
        'Psr\Log'=>dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'log'.DS.'Psr'.DS.'Log',
        'Fig\Http\Message'=>dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'http-message-util'.DS.'src',
        'Psr\Http\Message'=>dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'http-message'.DS.'src',
        'Fig\Link'=>dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'link-util'.DS.'src',
        'Psr\Container'=>dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'container'.DS.'src',
        'Fig\Cache'=>dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'cache-util'.DS.'src',
        'Psr\Link'=>dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'link'.DS.'src',
        'Psr\Cache'=>dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'cache'.DS.'src',
        'Monolog'=>dirname(__DIR__).DS.'Library'.DS.'Seldaek'.DS.'monolog'.DS.'src'.DS.'Monolog',
        'Noodlehaus'=>dirname(__DIR__).DS.'Library'.DS.'hassankhan'.DS.'config'.DS.'src',
        'MongoDB'=>dirname(__DIR__).DS.'Library'.DS.'mongodb'.DS.'mongo-php-library'.DS.'src',
        'Curl'=>dirname(__DIR__).DS.'Library'.DS.'php-curl-class'.DS.'php-curl-class'.DS.'src'.DS.'Curl',
        'GuzzleHttp\Ring'=>dirname(__DIR__).DS.'Library'.DS.'guzzle'.DS.'RingPHP'.DS.'src',
        'GuzzleHttp\Stream'=>dirname(__DIR__).DS.'Library'.DS.'guzzle'.DS.'streams'.DS.'src',
        'React\Promise'=>dirname(__DIR__).DS.'Library'.DS.'reactphp'.DS.'promise'.DS.'src',
        'Elasticsearch'=>dirname(__DIR__).DS.'Library'.DS.'elastic'.DS.'elasticsearch-php'.DS.'src'.DS.'Elasticsearch',
        'Flexihash'=>dirname(__DIR__).DS.'Library'.DS.'pda'.DS.'flexihash'.DS.'src',
        'PhpAmqpLib'=>dirname(__DIR__).DS.'Library'.DS.'php-amqplib'.DS.'php-amqplib'.DS.'PhpAmqpLib',
        'Ds'=>dirname(__DIR__).DS.'Library'.DS.'php-ds'.DS.'polyfill'.DS.'src',//need ext-ds
        'Kernel'=>dirname(__DIR__),
    ],
    'files' => [
        dirname(__DIR__).DS.'Library'.DS.'nikic'.DS.'FastRoute'.DS.'src'.DS.'functions.php',
        dirname(__DIR__).DS.'Library'.DS.'mongodb'.DS.'mongo-php-library'.DS.'src'.DS.'functions.php',
        dirname(__DIR__).DS.'Library'.DS.'reactphp'.DS.'promise'.DS.'src'.DS.'functions_include.php',
        dirname(__DIR__).DS.'Helpers'.DS.'Common.php',
    ],
];
