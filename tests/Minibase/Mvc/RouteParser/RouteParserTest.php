<?php
namespace Minibase\Mvc\RouteParser;

class RouteParserTest extends \PHPUnit_Framework_TestCase{
	
	public function setUp () {
		$this->mb = $this->getMock('Minibase\MB');
	}
	
	public function testFromFile () {
		$router = RouteParser::fromFile(MB_RESOURCE_DIR . 'routes.json' , $this->mb);
		$router->parse();
	}
	
	public function testFromJson () {
		$router = new RouteParser('[["get","/test","Ok.lol"]]', $this->mb);
		$router->parse();
	}
	
	
}