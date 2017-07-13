<?php

namespace Sabberworm\CSS;

use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\Property\AtRule;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\CSSNamespace;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\RuleValueList;
use Sabberworm\CSS\Value\Size;
use Sabberworm\CSS\Value\Color;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\String;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;

/**
 * Parser class parses CSS from text into a data structure.
 */
class Parser {

	private $sText;
	private $iCurrentPosition;
	private $oParserSettings;
	private $sCharset;
	private $iLength;
	private $peekCache = null;
	private $blockRules;
	private $aSizeUnits;

	public function __construct($sText, Settings $oParserSettings = null) {
		$this->sText = $sText;
		$this->iCurrentPosition = 0;
		if ($oParserSettings === null) {
			$oParserSettings = Settings::create();
		}
		$this->oParserSettings = $oParserSettings;
		$this->blockRules = explode('/', AtRule::BLOCK_RULES);

		foreach (explode('/', Size::ABSOLUTE_SIZE_UNITS.'/'.Size::RELATIVE_SIZE_UNITS.'/'.Size::NON_SIZE_UNITS) as $val) {
			$iSize = strlen($val);
			if(!isset($this->aSizeUnits[$iSize])) {
				$this->aSizeUnits[$iSize] = array();
			}
			$this->aSizeUnits[$iSize][strtolower($val)] = $val;
		}
		ksort($this->aSizeUnits, SORT_NUMERIC);
	}

	public function setCharset($sCharset) {
		$this->sCharset = $sCharset;
		$this->iLength = $this->strlen($this->sText);
	}

	public function getCharset() {
		return $this->sCharset;
	}

	public function parse() {
		$this->setCharset($this->oParserSettings->sDefaultCharset);
		$oResult = new Document();
		$this->parseDocument($oResult);
		return $oResult;
	}

	private function parseDocument(Document $oDocument) {
		$this->consumeWhiteSpace();
		$this->parseList($oDocument, true);
	}

	private function parseList(CSSList $oList, $bIsRoot = false) {
		while (!$this->isEnd()) {
			if ($this->comes('@')) {
				$oList->append($this->parseAtRule());
			} else if ($this->comes('}')) {
				$this->consume('}');
				if ($bIsRoot) {
					throw new \Exception("Unopened {");
				} else {
					return;
				}
			} else {
				if($this->oParserSettings->bLenientParsing) {
					try {
						$oList->append($this->parseSelector());
					} catch (UnexpectedTokenException $e) {}
				} else {
					$oList->append($this->parseSelector());
				}
			}
			$this->consumeWhiteSpace();
		}
		if (!$bIsRoot) {
			throw new \Exception("Unexpected end of document");
		}
	}

	private function parseAtRule() {
		$this->consume('@');
		$sIdentifier = $this->parseIdentifier();
		$this->consumeWhiteSpace();
		if ($sIdentifier === 'import') {
			$oLocation = $this->parseURLValue();
			$this->consumeWhiteSpace();
			$sMediaQuery = null;
			if (!$this->comes(';')) {
				$sMediaQuery = $this->consumeUntil(';');
			}
			$this->consume(';');
			return new Import($oLocation, $sMediaQuery);
		} else if ($sIdentifier === 'charset') {
			$sCharset = $this->parseStringValue();
			$this->consumeWhiteSpace();
			$this->consume(';');
			$this->setCharset($sCharset->getString());
			return new Charset($sCharset);
		} else if ($this->identifierIs($sIdentifier, 'keyframes')) {
			$oResult = new KeyFrame();
			$oResult->setVendorKeyFrame($sIdentifier);
			$oResult->setAnimationName(trim($this->consumeUntil('{', false, true)));
			$this->consumeWhiteSpace();
			$this->parseList($oResult);
			return $oResult;
		} else if ($sIdentifier === 'namespace') {
			$sPrefix = null;
			$mUrl = $this->parsePrimitiveValue();
			if (!$this->comes(';')) {
				$sPrefix = $mUrl;
				$mUrl = $this->parsePrimitiveValue();
			}
			$this->consume(';');
			if ($sPrefix !== null && !is_string($sPrefix)) {
				throw new \Exception('Wrong namespace prefix '.$sPrefix);
			}
			if (!($mUrl instanceof String || $mUrl instanceof URL)) {
				throw new \Exception('Wrong namespace url of invalid type '.$mUrl);
			}
			return new CSSNamespace($mUrl, $sPrefix);
		} else {
			//Unknown other at rule (font-face or such)
			$sArgs = trim($this->consumeUntil('{', false, true));
			$this->consumeWhiteSpace();
			$bUseRuleSet = true;
			foreach($this->blockRules as $sBlockRuleName) {
				if($this->identifierIs($sIdentifier, $sBlockRuleName)) {
					$bUseRuleSet = false;
					break;
				}
			}
			if($bUseRuleSet) {
				$oAtRule = new AtRuleSet($sIdentifier, $sArgs);
				$this->parseRuleSet($oAtRule);
			} else {
				$oAtRule = new AtRuleBlockList($sIdentifier, $sArgs);
				$this->parseList($oAtRule);
			}
			return $oAtRule;
		}
	}

	private function parseIdentifier($bAllowFunctions = true, $bIgnoreCase = true) {
		$sResult = $this->parseCharacter(true);
		if ($sResult === null) {
			throw new UnexpectedTokenException($sResult, $this->peek(5), 'identifier');
		}
		$sCharacter = null;
		while (($sCharacter = $this->parseCharacter(true)) !== null) {
			$sResult .= $sCharacter;
		}
		if ($bIgnoreCase) {
			$sResult = $this->strtolower($sResult);
		}
		if ($bAllowFunctions && $this->comes('(')) {
			$this->consume('(');
			$aArguments = $this->parseValue(array('=', ' ', ','));
			$sResult = new CSSFunction($sResult, $aArguments);
			$this->consume(')');
		}
		return $sResult;
	}

	private function parseStringValue() {
		$sBegin = $this->peek();
		$sQuote = null;
		if ($sBegin === "'") {
			$sQuote = "'";
		} else if ($sBegin === '"') {
			$sQuote = '"';
		}
		if ($sQuote !== null) {
			$this->consume($sQuote);
		}
		$sResult = "";
		$sContent = null;
		if ($sQuote === null) {
			//Unquoted strings end in whitespace or with braces, brackets, parentheses
			while (!preg_match('/[\\s{}()<>\\[\\]]/isu', $this->peek())) {
				$sResult .= $this->parseCharacter(false);
			}
		} else {
			while (!$this->comes($sQuote)) {
				$sContent = $this->parseCharacter(false);
				if ($sContent === null) {
					throw new \Exception("Non-well-formed quoted string {$this->peek(3)}");
				}
				$sResult .= $sContent;
			}
			$this->consume($sQuote);
		}
		return new String($sResult);
	}

	private function parseCharacter($bIsForIdentifier) {
		if ($this->peek() === '\\') {
			$this->consume('\\');
			if ($this->comes('\n') || $this->comes('\r')) {
				return '';
			}
			if (preg_match('/[0-9a-fA-F]/Su', $this->peek()) === 0) {
				return $this->consume(1);
			}
			$sUnicode = $this->consumeExpression('/^[0-9a-fA-F]{1,6}/u');
			if ($this->strlen($sUnicode) < 6) {
				//Consume whitespace after incomplete unicode escape
				if (preg_match('/\\s/isSu', $this->peek())) {
					if ($this->comes('\r\n')) {
						$this->consume(2);
					} else {
						$this->consume(1);
					}
				}
			}
			$iUnicode = intval($sUnicode, 16);
			$sUtf32 = "";
			for ($i = 0; $i < 4; ++$i) {
				$sUtf32 .= chr($iUnicode & 0xff);
				$iUnicode = $iUnicode >> 8;
			}
			return iconv('utf-32le', $this->sCharset, $sUtf32);
		}
		if ($bIsForIdentifier) {
			$peek = ord($this->peek());
			// Ranges: a-z A-Z 0-9 - _
			if (($peek >= 97 && $peek <= 122) ||
				($peek >= 65 && $peek <= 90) ||
				($peek >= 48 && $peek <= 57) ||
				($peek === 45) ||
				($peek === 95) ||
				($peek > 0xa1)) {
				return $this->consume(1);
			}
		} else {
			return $this->consume(1);
		}
		return null;
	}

	private function parseSelector() {
		$oResult = new DeclarationBlock();
		$oResult->setSelector($this->consumeUntil('{', false, true));
		$this->consumeWhiteSpace();
		$this->parseRuleSet($oResult);
		return $oResult;
	}

	private function parseRuleSet($oRuleSet) {
		while ($this->comes(';')) {
			$this->consume(';');
			$this->consumeWhiteSpace();
		}
		while (!$this->comes('}')) {
			$oRule = null;
			if($this->oParserSettings->bLenientParsing) {
				try {
					$oRule = $this->parseRule();
				} catch (UnexpectedTokenException $e) {
					try {
						$sConsume = $this->consumeUntil(array("\n", ";", '}'), true);
						// We need to “unfind” the matches to the end of the ruleSet as this will be matched later
						if($this->streql($this->substr($sConsume, $this->strlen($sConsume)-1, 1), '}')) {
							--$this->iCurrentPosition;
							$this->peekCache = null;
						} else {
							$this->consumeWhiteSpace();
							while ($this->comes(';')) {
								$this->consume(';');
							}
						}
					} catch (UnexpectedTokenException $e) {
						// We’ve reached the end of the document. Just close the RuleSet.
						return;
					}
				}
			} else {
				$oRule = $this->parseRule();
			}
			if($oRule) {
				$oRuleSet->addRule($oRule);
			}
			$this->consumeWhiteSpace();
		}
		$this->consume('}');
	}

	private function parseRule() {
		$oRule = new Rule($this->parseIdentifier());
		$this->consumeWhiteSpace();
		$this->consume(':');
		$oValue = $this->parseValue(self::listDelimiterForRule($oRule->getRule()));
		$oRule->setValue($oValue);
		if ($this->comes('!')) {
			$this->consume('!');
			$this->consumeWhiteSpace();
			$this->consume('important');
			$oRule->setIsImportant(true);
		}
		while ($this->comes(';')) {
			$this->consume(';');
			$this->consumeWhiteSpace();
		}
		return $oRule;
	}

	private function parseValue($aListDelimiters) {
		$aStack = array();
		$this->consumeWhiteSpace();
		//Build a list of delimiters and parsed values
		while (!($this->comes('}') || $this->comes(';') || $this->comes('!') || $this->comes(')'))) {
			if (count($aStack) > 0) {
				$bFoundDelimiter = false;
				foreach ($aListDelimiters as $sDelimiter) {
					if ($this->comes($sDelimiter)) {
						array_push($aStack, $this->consume($sDelimiter));
						$this->consumeWhiteSpace();
						$bFoundDelimiter = true;
						break;
					}
				}
				if (!$bFoundDelimiter) {
					//Whitespace was the list delimiter
					array_push($aStack, ' ');
				}
			}
			array_push($aStack, $this->parsePrimitiveValue());
			$this->consumeWhiteSpace();
		}
		//Convert the list to list objects
		foreach ($aListDelimiters as $sDelimiter) {
			if (count($aStack) === 1) {
				return $aStack[0];
			}
			$iStartPosition = null;
			while (($iStartPosition = array_search($sDelimiter, $aStack, true)) !== false) {
				$iLength = 2; //Number of elements to be joined
				for ($i = $iStartPosition + 2; $i < count($aStack); $i+=2, ++$iLength) {
					if ($sDelimiter !== $aStack[$i]) {
						break;
					}
				}
				$oList = new RuleValueList($sDelimiter);
				for ($i = $iStartPosition - 1; $i - $iStartPosition + 1 < $iLength * 2; $i+=2) {
					$oList->addListComponent($aStack[$i]);
				}
				array_splice($aStack, $iStartPosition - 1, $iLength * 2 - 1, array($oList));
			}
		}
		return $aStack[0];
	}

	private static function listDelimiterForRule($sRule) {
		if (preg_match('/^font($|-)/', $sRule)) {
			return array(',', '/', ' ');
		}
		return array(',', ' ', '/');
	}

	private function parsePrimitiveValue() {
		$oValue = null;
		$this->consumeWhiteSpace();
		if (is_numeric($this->peek()) || ($this->comes('-.') && is_numeric($this->peek(1, 2))) || (($this->comes('-') || $this->comes('.')) && is_numeric($this->peek(1, 1)))) {
			$oValue = $this->parseNumericValue();
		} else if ($this->comes('#') || $this->comes('rgb', true) || $this->comes('hsl', true)) {
			$oValue = $this->parseColorValue();
		} else if ($this->comes('url', true)) {
			$oValue = $this->parseURLValue();
		} else if ($this->comes("'") || $this->comes('"')) {
			$oValue = $this->parseStringValue();
		} else {
			$oValue = $this->parseIdentifier(true, false);
		}
		$this->consumeWhiteSpace();
		return $oValue;
	}

	private function parseNumericValue($bForColor = false) {
		$sSize = '';
		if ($this->comes('-')) {
			$sSize .= $this->consume('-');
		}
		while (is_numeric($this->peek()) || $this->comes('.')) {
			if ($this->comes('.')) {
				$sSize .= $this->consume('.');
			} else {
				$sSize .= $this->consume(1);
			}
		}

		$sUnit = null;
		foreach ($this->aSizeUnits as $iLength => &$aValues) {
			if(($sUnit = @$aValues[strtolower($this->peek($iLength))]) !== null) {
				$this->consume($iLength);
				break;
			}
		}
		return new Size(floatval($sSize), $sUnit, $bForColor);
	}

	private function parseColorValue() {
		$aColor = array();
		if ($this->comes('#')) {
			$this->consume('#');
			$sValue = $this->parseIdentifier(false);
			if ($this->strlen($sValue) === 3) {
				$sValue = $sValue[0] . $sValue[0] . $sValue[1] . $sValue[1] . $sValue[2] . $sValue[2];
			}
			$aColor = array('r' => new Size(intval($sValue[0] . $sValue[1], 16), null, true), 'g' => new Size(intval($sValue[2] . $sValue[3], 16), null, true), 'b' => new Size(intval($sValue[4] . $sValue[5], 16), null, true));
		} else {
			$sColorMode = $this->parseIdentifier(false);
			$this->consumeWhiteSpace();
			$this->consume('(');
			$iLength = $this->strlen($sColorMode);
			for ($i = 0; $i < $iLength; ++$i) {
				$this->consumeWhiteSpace();
				$aColor[$sColorMode[$i]] = $this->parseNumericValue(true);
				$this->consumeWhiteSpace();
				if ($i < ($iLength - 1)) {
					$this->consume(',');
				}
			}
			$this->consume(')');
		}
		return new Color($aColor);
	}

	private function parseURLValue() {
		$bUseUrl = $this->comes('url', true);
		if ($bUseUrl) {
			$this->consume('url');
			$this->consumeWhiteSpace();
			$this->consume('(');
		}
		$this->consumeWhiteSpace();
		$oResult = new URL($this->parseStringValue());
		if ($bUseUrl) {
			$this->consumeWhiteSpace();
			$this->consume(')');
		}
		return $oResult;
	}

	/**
	* Tests an identifier for a given value. Since identifiers are all keywords, they can be vendor-prefixed. We need to check for these versions too.
	*/
	private function identifierIs($sIdentifier, $sMatch) {
		return (strcasecmp($sIdentifier, $sMatch) === 0)
			?: preg_match("/^(-\\w+-)?$sMatch$/i", $sIdentifier) === 1;
	}

	private function comes($sString, $bCaseInsensitive = false) {
		$sPeek = $this->peek(strlen($sString));
		return ($sPeek == '')
			? false
			: $this->streql($sPeek, $sString, $bCaseInsensitive);
	}

	private function peek($iLength = 1, $iOffset = 0) {
		if (($peek = (!$iOffset && ($iLength === 1))) &&
			!is_null($this->peekCache)) {
			return $this->peekCache;
		}
		$iOffset += $this->iCurrentPosition;
		if ($iOffset >= $this->iLength) {
			return '';
		}
		$iLength = min($iLength, $this->iLength-$iOffset);
		$out = $this->substr($this->sText, $iOffset, $iLength);
		if ($peek) {
			$this->peekCache = $out;
		}
		return $out;
	}

	private function consume($mValue = 1) {
		if (is_string($mValue)) {
			$iLength = $this->strlen($mValue);
			if (!$this->streql($this->substr($this->sText, $this->iCurrentPosition, $iLength), $mValue)) {
				throw new UnexpectedTokenException($mValue, $this->peek(max($iLength, 5)));
			}
			$this->iCurrentPosition += $this->strlen($mValue);
			$this->peekCache = null;
			return $mValue;
		} else {
			if ($this->iCurrentPosition + $mValue > $this->iLength) {
				throw new UnexpectedTokenException($mValue, $this->peek(5), 'count');
			}
			$sResult = $this->substr($this->sText, $this->iCurrentPosition, $mValue);
			$this->iCurrentPosition += $mValue;
			$this->peekCache = null;
			return $sResult;
		}
	}

	private function consumeExpression($mExpression) {
		$aMatches = null;
		if (preg_match($mExpression, $this->inputLeft(), $aMatches, PREG_OFFSET_CAPTURE) === 1) {
			return $this->consume($aMatches[0][0]);
		}
		throw new UnexpectedTokenException($mExpression, $this->peek(5), 'expression');
	}

	private function consumeWhiteSpace() {
		do {
			while (preg_match('/\\s/isSu', $this->peek()) === 1) {
				$this->consume(1);
			}
			if($this->oParserSettings->bLenientParsing) {
				try {
					$bHasComment = $this->consumeComment();
				} catch(UnexpectedTokenException $e) {
					// When we can’t find the end of a comment, we assume the document is finished.
					$this->iCurrentPosition = $this->iLength;
					return;
				}
			} else {
				$bHasComment = $this->consumeComment();
			}
		} while($bHasComment);
	}

	private function consumeComment() {
		if ($this->comes('/*')) {
			$this->consume(1);
			while ($this->consume(1) !== '') {
				if ($this->comes('*/')) {
					$this->consume(2);
					return true;
				}
			}
		}
		return false;
	}

	private function isEnd() {
		return $this->iCurrentPosition >= $this->iLength;
	}

	private function consumeUntil($aEnd, $bIncludeEnd = false, $consumeEnd = false) {
		$aEnd = is_array($aEnd) ? $aEnd : array($aEnd);
		$out = '';
		$start = $this->iCurrentPosition;

		while (($char = $this->consume(1)) !== '') {
			$this->consumeComment();
			if (in_array($char, $aEnd)) {
				if ($bIncludeEnd) {
					$out .= $char;
				} elseif (!$consumeEnd) {
					$this->iCurrentPosition -= $this->strlen($char);
				}
				return $out;
			}
			$out .= $char;
		}

		$this->iCurrentPosition = $start;
		throw new UnexpectedTokenException('One of ("'.implode('","', $aEnd).'")', $this->peek(5), 'search');
	}

	private function inputLeft() {
		return $this->substr($this->sText, $this->iCurrentPosition, -1);
	}

	private function substr($sString, $iStart, $iLength) {
		if ($this->oParserSettings->bMultibyteSupport) {
			return mb_substr($sString, $iStart, $iLength, $this->sCharset);
		} else {
			return substr($sString, $iStart, $iLength);
		}
	}

	private function strlen($sString) {
		if ($this->oParserSettings->bMultibyteSupport) {
			return mb_strlen($sString, $this->sCharset);
		} else {
			return strlen($sString);
		}
	}

	private function streql($sString1, $sString2, $bCaseInsensitive = true) {
		if($bCaseInsensitive) {
			return $this->strtolower($sString1) === $this->strtolower($sString2);
		} else {
			return $sString1 === $sString2;
		}
	}

	private function strtolower($sString) {
		if ($this->oParserSettings->bMultibyteSupport) {
			return mb_strtolower($sString, $this->sCharset);
		} else {
			return strtolower($sString);
		}
	}

	private function strpos($sString, $sNeedle, $iOffset) {
		if ($this->oParserSettings->bMultibyteSupport) {
			return mb_strpos($sString, $sNeedle, $iOffset, $this->sCharset);
		} else {
			return strpos($sString, $sNeedle, $iOffset);
		}
	}

}
