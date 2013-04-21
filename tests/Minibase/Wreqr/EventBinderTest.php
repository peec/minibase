<?php
namespace Minibase\Wreqr;

class EventBinderTest extends \PHPUnit_Framework_TestCase{
	
	
	public function testEventShouldFireOnce () {
		$e = new EventBinder();

		$mock = $this->getMock('stdClass', array('myCallback'));
		$mock->expects($this->once())
			->method('myCallback');
		
		$e->on("test", array($mock, 'myCallback'));
			
		$e->trigger("test");
	}
	
	public function testEventShouldNotFireIfEventIsDestroyed () {
		$e = new EventBinder();

		$mock = $this->getMock('stdClass', array('myCallback'));
		$mock->expects($this->never())
		->method('myCallback')
		->will($this->returnValue(function () {
		}));
		
		$e->on("test", array($mock, 'myCallback'));
		$e->off("test", array($mock, 'myCallback'));
		
		$e->trigger("test");
	}
	
	
	public function testDestroyOnlyOneCallbackWhenSpecified () {
		$e = new EventBinder();
		
		$mock = $this->getMock('stdClass', array('myCallback'));
		$mock->expects($this->once())
		->method('myCallback')
		->will($this->returnValue(function () {}));
		
		
		$mock2 = $this->getMock('stdClass', array('myCallback'));
		$mock2->expects($this->never())
		->method('myCallback');
		
		$e->on("test", array($mock, 'myCallback'));
		$e->on("test", array($mock2, 'myCallback'));
		
		$e->off("test", array($mock2, 'myCallback'));
		
		$e->trigger("test");		
	}
	
	public function testArgumentsPassedToEvent () {
		$e = new EventBinder();
		
		$arg1 = "some-value";
		$arg2 = 12312;
				
		$mock = $this->getMock('stdClass', array('myCallback'));
		$mock->expects($this->once())
		->method('myCallback')
		->with($arg1, $arg2);
		
		$e->on("test", array($mock, 'myCallback'));
		
		$e->trigger("test", array($arg1, $arg2));		
	}
	
	
	
	
}