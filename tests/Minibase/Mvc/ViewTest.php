<?php
namespace Minibase\Mvc;

use Minibase\Wreqr\EventBinder;

class ViewTest extends \PHPUnit_Framework_TestCase{
	
	public function testEventBinderMustBeSet () {
		$view = new View(new EventBinder());
		$this->assertInstanceOf('Minibase\Wreqr\EventBinder', $view->events);
	}
	public function testEscapeMethodShouldEscapeTags () {
		$view = new View(new EventBinder());
		
		$this->assertFalse(strstr($view->escape("<script></script>"), "<"));
	}
	
	
	
}