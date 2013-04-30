<?php
namespace Minibase\I18n\PHPExtractor;

use Kunststube\POTools\POString;

class Extractor {
	
	const S_ORD = 1;
	const S_FUNC_CALL_START = 2;
	const S_FUNC_ARGS = 3;
	
	private $fileContent;
	private $file;
	
	private $PARSE_FUNCS = array(
			'_dc',
			'dcgettext',
			
			'_dcn',
			'dcngettext',
			
			'_d',
			'dgettext',
			
			'_dn',
			'dngettext',
			
			'_',
			'gettext',
			
			'_n',
			'ngettext'
			);
	
	public function __construct ($file) {
		$this->file = $file;
		$this->fileContent = file_get_contents($file);
	}
	
	public function getStrings () {
		
		$tokens = token_get_all($this->fileContent);
		
		
		
		$state = self::S_ORD;
		
		$build = array();
		$builds = array();
		
		$skip = 0;
		$buildArg = null;
		foreach($tokens as $tokKey =>  $tok) {
			if ($skip > 0) {
				$skip--;
				continue;
			}
			if (is_array($tok)) {
				list ($tokIndex, $tokString, $lineNum) = $tok;
			} else {
				$tokIndex = $tok;
				$tokString = null;
				$lineNum = null;
			}
			
			switch($state){
				case self::S_ORD:
					if ($tokIndex === T_STRING && $tokString && in_array(strtolower($tokString), $this->PARSE_FUNCS)) {
						$state = self::S_FUNC_CALL_START;
						$build['func'] = $tokString;
						$build['args'] = array();
					} elseif ($tokIndex === T_WHITESPACE) {
						
					} else {
						$state = self::S_ORD;
					}
					break;
				case self::S_FUNC_CALL_START:
					if ($tokIndex === '(') {
						$state = self::S_FUNC_ARGS;
					} elseif ($tokIndex === T_WHITESPACE) {
						
					} else {
						$state = self::S_ORD;
					}
					break;
				case self::S_FUNC_ARGS:
					if ($tokIndex === ')') {
						$state = self::S_ORD;
						$build['args'][] = $buildArg;
						$builds[] = $build;
						$build = array();
						$buildArg = null;
						
					} elseif ($tokIndex === ',') {
						$build['args'][] = $buildArg;
						$buildArg = null;
					} elseif (($tokIndex === T_STRING || $tokIndex === T_CONSTANT_ENCAPSED_STRING) && $tokString) {
						$buildArg .= $tokString;
					} elseif ($tokIndex === T_WHITESPACE) {
						
					} else {
						$state = self::S_ORD;
					}
						
					break;
			}
				
				
				
		}
		
		$pos = array();
		foreach($builds as $build) {
			
			
			$func = $build['func'];
			$args = $build['args'];
			foreach($args as $k => $v) {
				$args[$k] = trim($v, '"\'');
 			}
 			
 			switch ($func) {
 				case '_':
 				case 'gettext':
 					$po = new POString($args[0]);
 					break;
 				
 				case '_d':
 				case 'dgettext':
 					$po = new POString($args[1]);
 					$po->setDomain($args[0]);
 					break;
 				case '_n':	
 				case 'ngettext':
 					$po = new POString($args[0]);
 					$po->setMsgidPlural($args[1]);
 					break;
 			}

 			$pos[] = $po;
		}
		
		
		return $pos;
	}
	
	
	
	
	
}

