<?php
namespace Minibase\Assetic;


use Assetic\AssetManager;

use Assetic\FilterManager;

use Assetic\Factory\LazyAssetManager;
use Assetic\Factory\AssetFactory;
use Minibase\Plugin\Plugin;

class AsseticPlugin extends Plugin{
	/**
	 * The asset factory.
	 * @var Assetic\Factory\AssetFactory
	 */
	public $assetFactory;
	
	
	public $consoleEvent;
	
	public function setup () {
		
		if (!$this->cfg('rootDir')) {
			throw new \Exception ("rootDir must be defined. Should be where the public directory available by forexample Apache is (the www dir).");	
		}
		
		$this->config['writeTo'] = $this->cfg('writeTo') ? $this->cfg('writeTo') : $this->cfg('rootDir');
		
		
		
		
		$filters = $this->cfg('filters', array());
		
		$this->assetFactory = new AssetFactory($this->cfg('rootDir'), $this->mb->isDevelopment());
		
		$am = new AssetManager();
	
		$this->mb->events->trigger('mb:assetic:am_bare', $am);
		
		$this->assetFactory->setAssetManager($am);
		
		$fm = new FilterManager();
		
		
		foreach($filters as $name => $config) {
			$reflector = new \ReflectionClass(str_replace('/', '\\', $config['filter']));
			if (!isset($config['args'])){
				$config['args'] = array();
			}
			$filter = $reflector->newInstanceArgs($config['args']);
			$fm->set($name, $filter);
		}
		
		$this->mb->events->trigger("mb:assetic:filters", array($fm));
		
		$this->assetFactory->setFilterManager($fm);
		
		
		
		assetic_init($this->assetFactory);
		
		
		$this->consoleEvent = function ($console) {
			$console->addCommands(array(new AsseticDumpCommand()));
		};
		
	}
	
	
	public function start () {
		$this->mb->events->on("mb:console", $this->consoleEvent);
		
	}
	
}