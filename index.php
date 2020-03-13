<?php
header('content-type:text/html;charset=utf-8');
ini_set('memory_limit', '256M');

require __DIR__ . '/autoload.php';
spl_autoload_register('loadClassLoader');


// exception example
$class = new Exception\ExampleException();
$example = $class::EXAMPLE();
var_dump(json_encode($example));die;

// TODO : Method invocation in different modes

















