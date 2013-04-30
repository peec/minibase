<?php
namespace Minibase\Commands;

use Symfony\Component\Console\Helper\Helper;
use Minibase\MB;

class MinibaseHelper extends Helper{
	/**
	 * Minibase instance
	 * @var MB
	 */
	protected $_mb;
	
	/**
	 * Constructor
	 *
	 * @param MB $mb Minibase instance
	 */
	public function __construct(MB $mb)
	{
		$this->_mb = $mb;
	}
	
	/**
	 * Retrieves Minibase instance
	 *
	 * @return Connection
	 */
	public function getMB()
	{
		return $this->_mb;
	}
	
	/**
	 * @see Helper
	 */
	public function getName()
	{
		return 'mb';
	}
	
}