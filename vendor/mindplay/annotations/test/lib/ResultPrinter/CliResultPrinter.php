<?php
namespace mindplay\test\lib\ResultPrinter;


use mindplay\test\lib\Colors;
use mindplay\test\lib\xTest;
use mindplay\test\lib\xTestRunner;

class CliResultPrinter extends ResultPrinter
{

    /**
     * Colors.
     *
     * @var Colors
     */
    protected $colors;

    /**
     * Creates CLI result printer instance.
     *
     * @param Colors $colors Colors.
     */
    public function __construct(Colors $colors)
    {
        $this->colors = $colors;
    }

    /**
     * Prints the header before the test output.
     *
     * @param xTestRunner $testRunner Test runner.
     * @param string      $pattern    Test filename pattern.
     * @return void
     */
    public function suiteHeader(xTestRunner $testRunner, $pattern)
    {
        echo 'Unit Tests' . PHP_EOL;
        echo 'Codebase: ' . $testRunner->getRootPath() . PHP_EOL;
        echo 'Test Suite: ' . $pattern . PHP_EOL;
    }

    /**
     * Creates code coverage report.
     *
     * @param \PHP_CodeCoverage $coverage Code coverage collector.
     * @return void
     */
    public function createCodeCoverageReport(\PHP_CodeCoverage $coverage = null)
    {
        if (!isset($coverage)) {
            echo 'Code coverage analysis unavailable. To enable code coverage, the xdebug php module must be installed and enabled.' . PHP_EOL;

            return;
        }

        echo PHP_EOL . 'Creating code coverage report in clover format ... ';
        $writer = new \PHP_CodeCoverage_Report_Clover;
        $writer->process($coverage, FULL_PATH . '/clover.xml');
        echo 'done' . PHP_EOL;
    }

    /**
     * Prints test header.
     *
     * @param xTest $test Test.
     * @return void
     */
    public function testHeader(xTest $test)
    {
        $class = get_class($test);

        echo PHP_EOL;
        echo 'Test Class: ' . $this->colors->getColoredString($class, 'blue') . PHP_EOL;
        echo 'Results:' . PHP_EOL;
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
        echo '(' . $testCaseMethod->getStartLine() . ') ' . $this->getTestCaseName($testCaseMethod, true);
        echo ' - ' . $this->colors->getColoredString($resultMessage, $resultColor);
        echo PHP_EOL;
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
        echo $total . ' Tests. ';

        if ($passed == $total) {
            echo $this->colors->getColoredString('All Tests Passed', 'green') . PHP_EOL;
        } else {
            echo $this->colors->getColoredString(($total - $passed) . ' Tests Failed', 'red') . PHP_EOL;
        }
    }
}
