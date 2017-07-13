<?php

namespace Sabberworm\CSS\Value;


class URL extends PrimitiveValue {

	private $oURL;

	public function __construct(String $oURL) {
		$this->oURL = $oURL;
	}

	public function setURL(String $oURL) {
		$this->oURL = $oURL;
	}

	public function getURL() {
		return $this->oURL;
	}

	public function __toString() {
		return $this->render(new \Sabberworm\CSS\OutputFormat());
	}

	public function render(\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		return "url({$this->oURL->render($oOutputFormat)})";
	}

}