<?php
namespace mindplay\test\lib;
use mindplay\test\lib\ResultPrinter\ResultPrinter;

/**
 * A base class to support simple unit tests.
 *
 * To define a test, declare a method with no arguments, prefixing it's name with "test",
 * for example: function testCanReadXmlFeed().
 *
 * If you declare an init() method, this will be run once before proceeding with the tests.
 *
 * If you declare a setup() and/or teardown() method, these will be run before/after each test.
 *
 * @todo document missing parameters and return-types
 */
abstract class xTest
{
    private $result;

    /**
     * Test runner.
     *
     * @var xTestRunner
     */
    private $testRunner;

    /**
     * Result printer.
     *
     * @var ResultPrinter
     */
    private $resultPrinter;

    /**
     * The name of the expected Exception.
     *
     * @var mixed
     */
    private $expectedException = null;

    /**
     * The message of the expected Exception.
     *
     * @var string
     */
    private $expectedExceptionMessage = '';

    /**
     * The code of the expected Exception.
     *
     * @var integer
     */
    private $expectedExceptionCode;

    /**
     * Sets result printer.
     *
     * @param ResultPrinter $resultPrinter Result printer.
     * @return void
     */
    public function setResultPrinter(ResultPrinter $resultPrinter)
    {
        $this->resultPrinter = $resultPrinter;
    }

    /**
     * Run this test.
     *
     * @param xTestRunner $testRunner Test runner.
     * @return boolean
     */
    public function run(xTestRunner $testRunner)
    {
        $this->testRunner = $testRunner;
        $this->resultPrinter->testHeader($this);

        $reflection = new \ReflectionClass(get_class($this));
        $methods = $reflection->getMethods();

        $passed = $count = 0;

        if (method_exists($this, 'init')) {
            try {
                $this->init();
            } catch (\Exception $exception) {
                echo '<tr style="color:white; background:red;"><td>init() failed</td><td><pre>' . $exception . '</pre></td></tr></table>';
                return false;
            }
        }

        foreach ($methods as $method) {
            if (substr($method->name, 0, 4) == 'test') {
                $this->result = null;

                $test = $method->name;
                $name = substr($test, 4);

                if (count($_GET) && isset($_GET[$name]) && $_GET[$name] !== '') {
                    continue;
                }

                $this->testRunner->startCoverageCollector($test);

                if (method_exists($this, 'setup')) {
                    $this->setup();
                }

                $exception = null;

                try {
                    $this->$test();
                } catch (\Exception $exception) {

                }

                try {
                    $this->assertException($exception);
                } catch (xTestException $subException) {

                }

                $count++;

                if ($this->result === true) {
                    $passed++;
                }

                if (method_exists($this, 'teardown')) {
                    $this->teardown();
                }

                $this->setExpectedException(null, '', null);

                $this->testRunner->stopCoverageCollector();
                $this->resultPrinter->testCaseResult($method, $this->getResultColor(), $this->getResultMessage());
            }
        }

        $this->resultPrinter->testFooter($this, $count, $passed);

        return $passed == $count;
    }

    /**
     * Checks that given exception matches expected one.
     *
     * @param \Exception $e Exception.
     * @return void
     */
    private function assertException(\Exception $e = null)
    {
        if (!is_string($this->expectedException)) {
            if ($e && !(($e instanceof xTestException) && $e->getCode() == xTestException::FAIL)) {
                $this->result = (string)$e;
            }
            return;
        }

        $this->check(
            $e instanceof \Exception,
            'Exception of "' . $this->expectedException . '" class was not thrown'
        );

        $this->check(
            get_class($e) == $this->expectedException,
            'Exception with "' . get_class($e) . '" class thrown instead of "' . $this->expectedException . '"'
        );

        if (is_string($this->expectedExceptionMessage) && !empty($this->expectedExceptionMessage)) {
            $this->check(
                $e->getMessage() == $this->expectedExceptionMessage,
                'Exception with "' . $e->getMessage() . '" message thrown instead of "' . $this->expectedExceptionMessage . '"'
            );
        }

        if ($this->expectedExceptionCode !== null) {
            $this->check(
                $e->getCode() == $this->expectedExceptionCode,
                'Exception with "' . $e->getCode() . '" code thrown instead of "' . $this->expectedExceptionCode . '"'
            );
        }

        $this->pass();
    }

    /**
     * Returns test result color.
     *
     * @return string
     */
    private function getResultColor()
    {
        if ($this->result !== true) {
            $color = 'red';
        } elseif ($this->result === null) {
            $color = 'blue';
        } else {
            $color = 'green';
        }

        return $color;
    }

    /**
     * Returns test result message.
     *
     * @return string
     */
    private function getResultMessage()
    {
        if ($this->result === true) {
            $result = 'PASS';
        } elseif ($this->result === null) {
            $result = 'FAIL: Incomplete Test';
        } else {
            $result = 'FAIL' . (is_string($this->result) ? ': ' . $this->result : '');
        }

        return $result;
    }

    /**
     * Calling this method during a test flags a test as passed or failed.
     *
     * @param bool        $pass   bool If this expression evaluates as true, the test is passed
     * @param bool|string $result string Optional - if supplied, should contain a brief description of why the test failed
     */
    protected function check($pass, $result = false)
    {
        if ($pass) {
            $this->pass();
        } else {
            $this->fail($result);
        }
    }

    /**
     * Calling this method during a test manually flags a test as passed
     */
    protected function pass()
    {
        if ($this->result === null) {
            $this->result = true;
        }
    }

    /**
     * Calling this method during a test manually flags a test as failed
     *
     * @param bool|string $result string Optional - if supplied, should contain a brief description of why the test failed
     *
     * @throws xTestException
     */
    protected function fail($result = false)
    {
        $this->result = $result;
        throw new xTestException();
    }

    /**
     * Calling this method during a test flags a test as passed if two values are exactly (===) the same.
     *
     * @param mixed       $a    mixed Any value
     * @param mixed       $b    mixed Any value - if exactly the same as $a, the test is passed
     * @param bool|string $fail string Optional - if supplied, should contain a brief description of why the test failed
     */
    protected function eq($a, $b, $fail = false)
    {
        if ($a === $b) {
            $this->pass();
        } else {
            $this->fail($fail === false ? var_export($a, true) . ' !== ' . var_export($b, true) : $fail);
        }
    }

    /**
     * Sets expected exception.
     *
     * @param mixed   $exceptionName    Exception class name.
     * @param string  $exceptionMessage Exception message.
     * @param integer $exceptionCode    Exception code.
     */
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = null)
    {
        $this->expectedException = $exceptionName;
        $this->expectedExceptionMessage = $exceptionMessage;
        $this->expectedExceptionCode = $exceptionCode;
    }

}
