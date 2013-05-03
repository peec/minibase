<?php
namespace Minibase;

use Minibase\Commands\MinibaseHelper;

use Symfony\Component\Console\Application;

use Minibase\Commands as Command;

class MBConsole{
	public $mb;
	
	public function __construct (MB $mb) {
		$this->mb = $mb;
		$this->console = new Application('Minibase', MB::VERSION);
		$this->console->setCatchExceptions(true);
		$helperSet = new \Symfony\Component\Console\Helper\HelperSet();
		$this->console->setHelperSet($helperSet);
		
		
		$helperSet->set(new MinibaseHelper($this->mb), 'mb');
		$helperSet->set(new \Symfony\Component\Console\Helper\DialogHelper(), 'dialog');
		$helperSet->set(new \Symfony\Component\Console\Helper\FormatterHelper(), 'formatter');
		
		$this->console->addCommands(array(
			new Command\POTGeneratorCommand(),
			new Command\GetTextDomainListCommand(),
			new Command\GetTextInitLanguageCommand(),
			new Command\GetTextMergeCommand(),
			new Command\GetTextCompileCommand()
		));
		
		
	}
	
	
	public function run () {
		$this->mb->events->trigger("mb:console", array($this->console));
		$this->console->run();
	}
	
}