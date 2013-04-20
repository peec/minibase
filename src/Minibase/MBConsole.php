<?php
namespace Minibase;

use Symfony\Component\Console\Application;


class MBConsole{
	public $mb;
	
	public function __construct (MB $mb) {
		$this->mb = $mb;
		$this->console = new Application('Minibase', MB::VERSION);
		$this->console->setCatchExceptions(true);
	}
	
	
	public function run () {
		$this->mb->events->trigger("mb:console", array($this->console));
		$this->console->run();
	}
	
}