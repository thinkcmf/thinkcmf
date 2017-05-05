<?php

## This script generates formatted documentation for the demo, in RST format.

header('Content-type: text/plain');
echo 'File: **demo/index.php**' . PHP_EOL;

$codeStart = 1;

foreach (file('index.php') as $lineNumber => $line) {
    if (substr(ltrim($line), 0, 2) == '##') {
        if ($codeStart !== false) {
            echo PHP_EOL;
            echo '.. literalinclude:: ../demo/index.php' . PHP_EOL;
            echo '   :lines: ' . ($codeStart + 1) . '-' . $lineNumber . PHP_EOL;
            echo PHP_EOL;
            $codeStart = false;
        }

        echo str_replace('`', '``', rtrim(substr(ltrim($line), 3))) . PHP_EOL;
    } else {
        // it's code
        if ($codeStart === false) {
            $codeStart = $lineNumber;
        }
    }
}

echo PHP_EOL;
echo '.. literalinclude:: ../demo/index.php' . PHP_EOL;
echo '   :lines: ' . ($codeStart + 1) . '-' . ($lineNumber + 1) . PHP_EOL;
echo PHP_EOL;
