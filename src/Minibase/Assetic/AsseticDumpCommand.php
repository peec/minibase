<?php
namespace Minibase\Assetic;


use Assetic\Factory\Loader\FunctionCallsFormulaLoader;

use Assetic\Factory\Resource\DirectoryResource;

use Assetic\AssetWriter;

use Assetic\Factory\LazyAssetManager;

use Assetic\AssetManager;


use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Minibase\MB;

class AsseticDumpCommand extends Command{

	protected function configure() {
		$this
		->setName('mb:assetic:dump')
		->setDescription('Dumps assetic resources to the cache.')
		->setHelp(<<<EOT
TODO?
EOT
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$mb = $this->getHelper('mb')->getMb();

		$plugin = $mb->get('Minibase\Assetic\AsseticPlugin');
		

		
		$factory = $plugin->assetFactory;
		
		
		$am = new LazyAssetManager($factory);
		
		
		if (isset($this->mb->cfg[MB::CFG_VIEWPATH])) {
			$am->setLoader('php', new FunctionCallsFormulaLoader($factory));
			$am->addResource(new DirectoryResource($this->mb->cfg[MB::CFG_VIEWPATH], '/\.php$/'), 'php');	
		}
		
		$mb->events->trigger("mb:assetic:am", array($am));
		
		
		$writer = new AssetWriter($plugin->cfg('rootDir'));
		// $writer->writeManagerAssets($am);
		$this->dumpManagerAssets($am, $writer);
		
		$output->writeln("Wrote assets to respective folders in the web directory.");
	}

	
	/**
	 * Dumps the assets of given manager
	 *
	 * Doesn't use AssetWriter::writeManagerAssets since we also want to dump non-combined assets
	 * (for example, when using twig extension in debug mode).
	 *
	 * @param AssetManager $am
	 * @param AssetWriter  $writer
	 */
	protected function dumpManagerAssets(AssetManager $am, AssetWriter $writer)
	{
		foreach ($am->getNames() as $name) {
			$asset   = $am->get($name);
	
			if ($am instanceof LazyAssetManager) {
				$formula = $am->getFormula($name);
			}
	
			$writer->writeAsset($asset);
	
			if (!isset($formula[2])) {
				continue;
			}
	
			$debug   = isset($formula[2]['debug'])   ? $formula[2]['debug']   : $am->isDebug();
			$combine = isset($formula[2]['combine']) ? $formula[2]['combine'] : null;
	
			if (null !== $combine ? !$combine : $debug) {
				foreach ($asset as $leaf) {
					$writer->writeAsset($leaf);
				}
			}
		}
	}


}