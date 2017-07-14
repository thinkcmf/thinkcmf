<?php

namespace Sabberworm\CSS\Value;

use Sabberworm\CSS\Renderable;

abstract class Value implements Renderable {
	//Methods are commented out because re-declaring them here is a fatal error in PHP < 5.3.9
	//public abstract function __toString();
	//public abstract function render(\Sabberworm\CSS\OutputFormat $oOutputFormat);
}
