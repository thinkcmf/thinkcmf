<?php
namespace mindplay\test\lib;

/**
 * The xTest::fail() method throws and catches this exception, in order to
 * interrupt the execution of a failed test.
 */
class xTestException extends \Exception
{
    const FAIL = 0;
    const PHP_ERROR = 1;
}
