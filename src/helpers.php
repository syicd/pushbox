<?php

if (!function_exists('xiaomi')) {
    define('PUSH_ROOT', dirname(__FILE__) . '/xiaomi/');
}

/**
 * 自动加载
 * @param $class
 */
function PushAutoload($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $path = str_replace('xiaomi' . DIRECTORY_SEPARATOR, '', $path);
    $file = __DIR__ . '/sdk/' . $path . '.php';
    if (file_exists($file)) {
        include($path);
    }
}
spl_autoload_register('PushAutoload');