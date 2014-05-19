<?php

function includeIfExists($file)
{
    return file_exists($file) ? include $file : false;
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
    echo 'Yanlis bir sürüm kullanıyor olmalısınız. Lütfen tekrar indirin!'.PHP_EOL.
    exit(1);
}

return $loader;
