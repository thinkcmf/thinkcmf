<?php
namespace mindplay\test;

use Composer\Autoload\ClassLoader;
use mindplay\test\lib\xTestRunner;

define('FULL_PATH', realpath(__DIR__ . '/..'));

$vendor_path = FULL_PATH . '/vendor';

if (!is_dir($vendor_path)) {
    echo 'Install dependencies first' . PHP_EOL;
    exit(1);
}

require_once($vendor_path . '/autoload.php');

$auto_loader = new ClassLoader();
$auto_loader->addPsr4("mindplay\\test\\", FULL_PATH . '/test');
$auto_loader->addPsr4("mindplay\\test\\Sample\\", FULL_PATH . '/test/suite/Sample');
$auto_loader->register();

$runner = new xTestRunner(dirname(__DIR__) . '/src/annotations', xTestRunner::createResultPrinter());
exit($runner->run(__DIR__.'/suite', '.test.php') ? 0 : 1);
