<?php
namespace mindplay\test\lib\ResultPrinter;


use mindplay\test\lib\xTest;
use mindplay\test\lib\xTestRunner;

abstract class ResultPrinter
{
    /**
     * Prints the header before the test output.
     *
     * @param xTestRunner $testRunner Test runner.
     * @param string      $pattern    Test filename pattern.
     * @return void
     */
    public function suiteHeader(xTestRunner $testRunner, $pattern)
    {

    }

    /**
     * Prints the footer after the test output.
     *
     * @param xTestRunner $testRunner Test runner.
     * @return void
     */
    public function suiteFooter(xTestRunner $testRunner)
    {

    }

    /**
     * Creates code coverage report.
     *
     * @param \PHP_CodeCoverage $coverage Code coverage collector.
     * @return void
     */
    public function createCodeCoverageReport(\PHP_CodeCoverage $coverage = null)
    {

    }

    /**
     * Prints test header.
     *
     * @param xTest $test Test.
     * @return void
     */
    public function testHeader(xTest $test)
    {

    }

    /**
     * Prints test footer.
     *
     * @param xTest   $test   Test.
     * @param integer $total  Total test case count.
     * @param integer $passed Passed test case count.
     * @return void
     */
    public function testFooter(xTest $test, $total, $passed)
    {

    }

    /**
     * Test case result.
     *
     * @param \ReflectionMethod $testCaseMethod Test case method.
     * @param string            $resultColor    Result color.
     * @param string            $resultMessage  Result message.
     * @return void
     */
    public function testCaseResult(\ReflectionMethod $testCaseMethod, $resultColor, $resultMessage)
    {

    }

    /**
     * Returns test case name.
     *
     * @param \ReflectionMethod $testCaseMethod Test case method.
     * @param boolean           $humanFormat    Use human format.
     * @return string
     */
    protected function getTestCaseName(\ReflectionMethod $testCaseMethod, $humanFormat = false)
    {
        $ret = substr($testCaseMethod->name, 4);

        return $humanFormat ? ltrim(preg_replace('/([A-Z])/', ' \1', $ret)) : $ret;
    }
}
