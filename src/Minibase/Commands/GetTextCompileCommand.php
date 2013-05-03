<?php
namespace Minibase\Commands;

use Minibase\I18n\PotFileGenerator;

use Minibase\I18n\PhpPotFileGenerator;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetTextCompileCommand extends Command{
	
	protected function configure() {
		$this
		->setName('mb:lang:compile')
		->setDescription('Compiles all PO files to MO files, last step after translation of files.')
		->addArgument('domain', InputArgument::REQUIRED, "The gettext domain. Tip: Use mb:lang:domains to get the domains.")
		->setHelp(<<<EOT
This is the last step, after translation of the PO files, compiles all the po files for a given
gettext domain.
EOT
				);
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$mb = $this->getHelper('mb')->getMb();
		
		$domain = $input->getArgument('domain');
		
		
		$domains = $mb->trans->getDomains();
		
		if (!isset($domains[$domain])) {
			throw new \Exception("No such gettext domain {$domain} registered. Run mb:lang:domains to see registered domains.");
		}
		$conf = $domains[$domain];
		
		
		if (!file_exists($conf['potFile'])) {
			throw new \Exception ("Pot file {$conf['potFile']} does not exist. Use mb:lang:extract {$domain} first.");
		}
		
		
		$dirs = glob($conf['path'] . "/*", GLOB_ONLYDIR);
		
		foreach($dirs as $dir) {
			$lang = str_replace($conf['path'] . '/', '', $dir);
			
			if ($lang !== $conf['locale']) {
				
				$poFile = $dir . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $domain . '.po';
				
				$moFile = $dir . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $domain . '.mo';
				
				if (!file_exists ($poFile)) {
					$output->writeln("<error>Could not find po file for $lang, tried to find: $poFile.</error>");
				} else {
					$options = implode(' ', array(
							"-cv",
							"-o \"$moFile\"",
							"\"$poFile\"",
							));
					$process = new Process("msgfmt {$options}");
					
					$process->run();
					
					$out = $process->getOutput();
					if ($process->isSuccessful()) {
						$output->writeln("<info>Compiled $lang MO file</info>");
					} else {
						$output->writeln("<error>Error Compiling $lang MO file: $out</error>");
					}
					
				}	
			}
			
			
		}
		
		//$process = new Process("msgmerge {$options}");
		
		
	}	

	
	
}