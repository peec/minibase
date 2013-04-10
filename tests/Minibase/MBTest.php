<?php
namespace Minibase;


use Minibase\Http\Request;

use Minibase\Wreqr\EventBinder;

class MBTest extends \PHPUnit_Framework_TestCase{

	private function buildMB () {
		$mb = new MB();
		$mb->events = new EventBinder();
		$mb->request = new Request();
		return $mb;
	}
	
	
	private function mockRoute ($method, $uri) {
		$mb = $this->buildMB();
		$mb->request->uri = $uri;
		$mb->request->method = $method;
		return $mb;
	}
	
	
	public function testHasGoodDefaults () {
		$mb = new MB();
		$this->assertEquals(null, $mb->request);
		$this->assertEquals(null, $mb->events);
		
	}
	
	/**
	 * @expectedException Minibase\InvalidControllerReturnException
	 */
	public function testShouldThrowExceptionIfNotReturnResponse () {
		$mb = $this->mockRoute("get", "/");
		$mb->on("get", "/", function () {
			
		});
		
		
	}
	
	

	public function testPluginIsAccessibleAfterInit () {
		$mb = $this->buildMB();
		
		$mb->plugin("test", function () {
			return array(1,2,3,4);
		});
		
		$this->assertNotNull($mb->test);
		
		$this->assertEquals(3, $mb->test[2]);
		
		
	}
	
}