<?php
define('PUSH_ROOT', dirname(__FILE__) . '/');
function PushAutoload($class)
{
    $parts = explode('\\', $class);
    $path = PUSH_ROOT . implode('/', $parts) . '.php';
    if (file_exists($path)) {
        include($path);
    }
}
spl_autoload_register('PushAutoload');