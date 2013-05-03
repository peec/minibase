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

class GetTextDomainListCommand extends Command{
	
	protected function configure() {
		$this
		->setName('mb:lang:domains')
		->setDescription('Lists all available domains from your app/plugins.')
		->setHelp(<<<EOT
Lists all the registered gettext domains, if plugins are added these are also listed.
EOT
				);
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$mb = $this->getHelper('mb')->getMb();
		
		$domains = $mb->trans->getDomains();

		if (empty($domains)) {
			$output->writeln("<info>No registered gettext domains for this app.</info>");
		} else {
			foreach ($domains as $domain => $conf) {
				$output->writeln("<fg=green>{$domain}</fg=green>");
				$output->writeln("\t Original locale: {$conf['locale']}");
				$output->writeln("\t Charset: {$conf['charset']}");
				$output->writeln("\t Locale path: {$conf['path']}");
				
			}
		}
		
	}	

	
	
}