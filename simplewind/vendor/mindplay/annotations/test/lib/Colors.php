<?php
namespace mindplay\test\lib;


class Colors
{
    private $foregroundColors = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
    );

    private $backgroundColors = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    );

    /**
     * Returns colored string
     *
     * @param string $text            Text to color.
     * @param string $foregroundColor Foreground color.
     * @param string $backgroundColor Background color.
     * @return string
     */
    public function getColoredString($text, $foregroundColor = null, $backgroundColor = null)
    {
        $coloredString = '';

        // check if given foreground color found
        if (isset($this->foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . $this->foregroundColors[$foregroundColor] . 'm';
        }

        // check if given background color found
        if (isset($this->backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . $this->backgroundColors[$backgroundColor] . 'm';
        }

        // Add string and end coloring
        $coloredString .= $text . "\033[0m";

        return $coloredString;
    }
}
