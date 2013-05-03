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

class POTGeneratorCommand extends Command{
	
	protected function configure() {
		$this
		->setName('mb:lang:extract')
		->setDescription('Updates or generates POT files from the specified domain.')
		->addArgument('domain', InputArgument::REQUIRED, "The gettext domain. Tip: Use mb:lang:domains to get the domains.")
		->setHelp(<<<EOT
The <info>mb:lang:extract</info> command generates one ".pot" file for the specified domain.

Use mb:lang:domains to get the available (registered) domains.

POT files is the first step, the strings that needs to be translated are extracted by this
command.

EOT
				);
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$mb = $this->getHelper('mb')->getMb();
		
		$domain = $input->getArgument('domain');
		
		$domains = $mb->trans->getDomains();
		
		$typeMap = array(
			'php' => function () {
				return new PhpPotFileGenerator();
			}
		);
		
		$mb->events->trigger("mb:generate-po", array(&$typeMap));
		
		if (!isset($domains[$domain])) {
			throw new \Exception("No such gettext domain {$domain} registered. Run mb:lang:domains to see registered domains.");
		}
		
		$conf = $domains[$domain];
		
			
			
			$cleanups = array();
			
			$filesToCheck = array();
			
			foreach($conf['rootDirs'] as $rootDir) {

				
				list ($type, $path) = explode(':', $rootDir);
				
				
				if (!isset($typeMap[$type])) {
					$output->writeln("<error>No parser found for rootDir type ($type).</error>");
					continue;
				}
				
				$generator = $typeMap[$type]();
				
				if (!($generator instanceof PotFileGenerator)) {
					throw new \Exception ("Generator ".get_class($generator)." does not extend Minibase\\I18n\\PotFileGenerator");
				}
				
				$files = array();
				$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY);
				foreach ($iterator as $file) {
					if ($file->isFile()) {
						$files[] = $file;
					}
				}
				// Generate / Filter files.
				$files = $generator->run($files);
				
				
				$cleanups[] = function () use ($generator, $files) {
					$generator->cleanup($files);
				};
				
				$filesToCheck += $files;
				
			}
			
			
			
			if (!is_dir($conf['potPath'])){
				mkdir($conf['potPath'], 0777, true);
			}
			
			
			$options = implode(' ',array(
					'--from-code=' . $conf['charset'],
					'--force-po',
					'--language=PHP',
					'-f -',
					'--no-wrap',
					"-o \"{$conf['potFile']}\"",
			));
			
			
			$process = new Process('xgettext ' . $options);
			$process->setStdin(implode("\n", $filesToCheck));
			
			$process->run();
			$out = $process->getOutput();
			if ($process->isSuccessful()) {
				$output->writeln("<info>Found ".count($filesToCheck)." files to check for gettext expressions for domain {$domain}.</info>");
			} else {
				$output->writeln("<error>{$domain}: {$out}</error>");
			}

			
			foreach ($cleanups as $cleanup) {
				$cleanup();
			}
		
		
		
		
	}	

	
	
}