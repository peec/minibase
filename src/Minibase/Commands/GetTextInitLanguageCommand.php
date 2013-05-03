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

class GetTextInitLanguageCommand extends Command{
	
	protected function configure() {
		$this
		->setName('mb:lang:new-language')
		->setDescription('Creates a new po file that can be translated for a given gettext domain.')
		->addArgument('domain', InputArgument::REQUIRED, "The gettext domain. Tip: Use mb:lang:domains to get the domains.")
		->addArgument('languages', InputArgument::REQUIRED | InputArgument::IS_ARRAY, "Languages to initialize.")
		->setHelp(<<<EOT
Generates a new language to be translated from the original source POT file.
Domain must be registered.
Example:

mb:lang:new-language myapp en_US,nb_NO
EOT
				);
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$mb = $this->getHelper('mb')->getMb();
		
		$domain = $input->getArgument('domain');
		$languages = $input->getArgument('languages');
		
		
		$domains = $mb->trans->getDomains();
		
		if (!isset($domains[$domain])) {
			throw new \Exception("No such gettext domain {$domain} registered. Run mb:lang:domains to see registered domains.");
		}
		$conf = $domains[$domain];
		
		if (!file_exists($conf['potFile'])) {
			throw new \Exception ("Pot file {$conf['potFile']} does not exist. Use mb:lang:extract {$domain} first.");
		}
		
		
		foreach($languages as $lang) {
			$poPath = "{$conf['path']}/{$lang}/LC_MESSAGES";
			
			if (!is_dir($poPath)) {
				mkdir($poPath, 0777, true);
			}
			
			$poFile = "{$poPath}/{$conf['domain']}.po";
			
			if (!file_exists($poFile)) {
				$options = implode(' ', array(
						"-l $lang",
						"--no-wrap",
						"--no-translator",
						"-i \"{$conf['potFile']}\"",
						"-o \"$poFile\""
						));
				$process = new Process("msginit {$options}");
				
				$process->run();
				
				$out = $process->getOutput();
				
				if ($process->isSuccessful()) {
					$output->writeln("Initialized new language ($lang) to po file: {$poFile}");
				} else {
					$output->writeln("<error>Error: $out</error>");
				}
				
			} else {
				$output->writeln("<info>Skipping $lang, po file already exist for this language: ($poFile)</info>");
			}
			
			
		}
		
		
		
	}	

	
	
}