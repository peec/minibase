<?php
namespace Minibase\Commands;

use Minibase\I18n\PHPExtractor\Extractor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class POGeneratorCommand extends Command{
	
	protected function configure() {
		$this
		->setName('mb:generate-po')
		->setDescription('Updates or generates PO files from the application source code.')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$mb = $this->getHelper('mb')->getMb();
		
		$domains = $mb->trans->getDomains();
		
		$typeMap = array(
			'php' => function ($file) {
				$parser = new Extractor($file);
				return $parser->getStrings();
			}
		);
		
		$mb->events->trigger("mb:generate-po", array(&$typeMap));
		
		foreach ($domains as $domain => $conf) {
			$catalog    = new \Kunststube\POTools\Catalog;
			
			
			$someStringsParsed = 0;
			foreach($conf['rootDirs'] as $rootDir) {

				list ($type, $path) = explode(':', $rootDir);
				
				if (!isset($typeMap[$type])) {
					$output->writeln("<error>No parser found for rootDir type ($type).</error>");
					continue;
				}
				
				$parser = $typeMap[$type];
				
				$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY);
				foreach ($iterator as $file) {
					if ($file->isFile()) {
						try{
							$strings = $parser($file);
							
							if ($strings){
								foreach ($strings as $string) {
									$someStringsParsed++;
									$catalog->add($string);
								}
							}
						} catch (\Exception $e) {
							$output->writeln("<error>{$e->getMessage()}</error>");
						} 
						
					}
				}
			}
			if ($someStringsParsed) {
				$output->writeln("<info>{$someStringsParsed} strings found for domain {$domain}.</info>");
				
				$dir = $conf['path'] . DIRECTORY_SEPARATOR . $conf['locale'];
				if (!is_dir($dir)) {
					mkdir($dir);
				}
				$catalog->writeToDirectory($dir);
				
			}
		}
		
		
		
	}	

	
	
}