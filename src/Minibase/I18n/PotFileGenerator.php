<?php
namespace Minibase\I18n;

abstract class PotFileGenerator {
	
	/**
	 * Gets extensions assoicated with this generator
	 * @return array Array of extensions to be assosicated with this generator
	 */
	abstract function getExtensions();
	
	
	/**
	 * Should return array of files to be added to xgettext. Must be PHP files.
	 * @param array $files Array of files
	 */
	abstract function getFiles(array $files);
	
	final public function run (array $files) {
		
		$exts = $this->getExtensions();
		// Make exts lowercase.
		array_walk($exts, function (&$ext, $key) {
			$ext = strtolower($ext);
		});
		
		// Filter out all exts.
		$files = array_filter($files, function (\SplFileInfo $file) use($exts) {
			return in_array(strtolower($file->getExtension()), $exts);
		});
		
		
		$returnFiles = $this->getFiles($files);
		
		return $returnFiles;
	}
	
	
	public function cleanup (array $files) {
		
	}
	
	
}