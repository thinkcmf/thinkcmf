<?php

namespace FontLib\Tests;

use FontLib\Font;

class FontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Fontlib\Exception\FontNotFoundException
     */
    public function testLoadFileNotFound()
    {
        Font::load('non-existing/font.ttf');
    }

    public function testLoadTTFFontSuccessfully()
    {
        $trueTypeFont = Font::load('sample-fonts/IntelClear-Light.ttf');

        $this->assertInstanceOf('FontLib\TrueType\File', $trueTypeFont);
    }
}