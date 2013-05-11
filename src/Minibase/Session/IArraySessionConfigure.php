<?php
namespace Minibase\Session;

interface IArraySessionConfigure {

	/**
	 * Should configure a driver based on a custom array of configuration.
	 * @param array $setup Array to configure the handler.
	 */
	public function setup(array $setup);
	

	/**
	 * @return \SessionHandlerInterface
	 */
	public function getHandler();
	
}
