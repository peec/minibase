<?php
namespace Minibase\I18n;

class PhpPotFileGenerator extends PotFileGenerator {

	public function getExtensions () {
		return array('php','php5');
	}
	
	public function getFiles (array $files) {
		// No processing needed for php files. We're ok.
		return $files;
	}
	
	
}