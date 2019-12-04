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
        'PhpAmqpLib'=>dirname(__DIR__).DS.'Library'.DS.'php-amqplib'.DS.'php-amqplib'.DS.'PhpAmqpLib',
        'Ds'=>dirname(__DIR__).DS.'Library'.DS.'php-ds'.DS.'polyfill'.DS.'src',//'.DS.'//need ext-ds
        'GuzzleHttp\Psr7'=>dirname(__DIR__).DS.'Library'.DS.'guzzle'.DS.'psr7'.DS.'src',
        'Intervention\Image'=>dirname(__DIR__).DS.'Library'.DS.'Intervention'.DS.'image'.DS.'src'.DS.'Intervention'.DS.'Image',
        'Whoops'=>dirname(__DIR__).DS.'Library'.DS.'filp'.DS.'whoops'.DS.'src'.DS.'Whoops',

        // 二维码相关库
        'BaconQrCode' => dirname(__DIR__).DS.'Library'.DS.'Bacon'.DS.'BaconQrCode'.DS.'src'.DS.'BaconQrCode',
        'Endroid\QrCode' => dirname(__DIR__).DS.'Library'.DS.'endroid'.DS.'qr-code'.DS.'src',
        'Zxing' => dirname(__DIR__).DS.'Library'.DS.'khanamiryan'.DS.'php-qrcode-detector-decoder'.DS.'lib',
        'MyCLabs\Enum' => dirname(__DIR__).DS.'Library'.DS.'myclabs'.DS.'php-enum'.DS.'src',
        'Symfony\Component\Inflector' => dirname(__DIR__).DS.'Library'.DS.'symfony'.DS.'inflector',
        'Symfony\Component\OptionsResolver' => dirname(__DIR__).DS.'Library'.DS.'symfony'.DS.'options-resolver',
        'Symfony\Component\PropertyAccess' => dirname(__DIR__).DS.'Library'.DS.'symfony'.DS.'property-access',

        'Overtrue\Pinyin' =>  dirname(__DIR__).DS.'Library'.DS.'overtrue'.DS.'pinyin'.DS.'src',

        'Symfony\Component\Process' =>  dirname(__DIR__).DS.'Library'.DS.'symfony'.DS.'process',
        'Spatie\ImageOptimizer' => dirname(__DIR__).DS.'Library'.DS.'spatie'.DS.'image-optimizer'.DS.'src',

        'Psr\SimpleCache' => dirname(__DIR__).DS.'Library'.DS.'php-fig'.DS.'simple-cache'.DS.'src',

        'Complex' => dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src',

        'PhpOffice\PhpSpreadsheet'=> dirname(__DIR__).DS.'Library'.DS.'PHPOffice'.DS.'PhpSpreadsheet'.DS.'src'.DS.'PhpSpreadsheet',

        'MiladRahimi\Jwt' => dirname(__DIR__).DS.'Library'.DS.'miladrahimi'.DS.'php-jwt'.DS.'src',

        'Kernel'=>dirname(__DIR__),
    ],
    'files' => [
        dirname(__DIR__).DS.'Library'.DS.'nikic'.DS.'FastRoute'.DS.'src'.DS.'functions.php',
        dirname(__DIR__).DS.'Library'.DS.'mongodb'.DS.'mongo-php-library'.DS.'src'.DS.'functions.php',
        dirname(__DIR__).DS.'Library'.DS.'reactphp'.DS.'promise'.DS.'src'.DS.'functions_include.php',
        dirname(__DIR__).DS.'Library'.DS.'guzzle'.DS.'psr7'.DS.'src'.DS.'functions_include.php',
        dirname(__DIR__).DS.'Library'.DS.'khanamiryan'.DS.'php-qrcode-detector-decoder'.DS.'lib'.'Common'.DS.'customFunctions.php',
        dirname(__DIR__).DS.'Library'.DS.'khanamiryan'.DS.'php-qrcode-detector-decoder'.DS.'lib'.DS.'QrReader.php',
        dirname(__DIR__).DS.'Utilities'.DS.'PHPExcel.php',
        dirname(__DIR__).DS.'Helpers'.DS.'Common.php',



        //Complex
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'abs.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'acos.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'acosh.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'acot.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'acoth.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'acsc.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'acsch.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'argument.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'asec.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'asech.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'asin.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'asinh.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'atan.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'atanh.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'conjugate.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'cos.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'cosh.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'cot.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'coth.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'csc.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'csch.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'exp.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'inverse.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'ln.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'log2.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'log10.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'negative.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'pow.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'rho.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'sec.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'sech.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'sin.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'sinh.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'sqrt.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'tan.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'tanh.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'functions'.DS.'theta.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'operations'.DS.'add.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'operations'.DS.'subtract.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'operations'.DS.'multiply.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'operations'.DS.'divideby.php',
        dirname(__DIR__).DS.'Library'.DS.'MarkBaker'.DS.'PHPComplex'.DS.'classes'.DS.'src'.DS.'operations'.DS.'divideinto.php'





    ],
];
