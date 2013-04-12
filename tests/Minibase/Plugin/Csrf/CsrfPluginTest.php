<?php
namespace Minibase\Plugin\Csrf;


use Minibase\Wreqr\EventBinder;

use Minibase\MB;

class CsrfPluginTest extends \PHPUnit_Framework_TestCase {
	
	
	public function testCsrfPluginConfiguration () {
		$csrf = new CsrfPlugin(null);
		$csrf->setApp($this->getMock('Minibase\MB'));
		
		$this->assertEquals('csrfToken', $csrf->tokenName());
		
		
	}
	
	public function testCsrfAddsEventListenersAndRemovesOnStop () {
		$mb = new MB();
		$mb->events = new EventBinder();	
		$csrf = new CsrfPlugin(null);
		$csrf->setApp($mb);
		
		$csrf->start();
		
		$refEvent = new \ReflectionObject($mb->events);
		$bindings = $refEvent->getProperty('bindings');
		$bindings->setAccessible(true);
		
		$refCsrf = new \ReflectionObject($csrf);
		
		$routeBefore = $refCsrf->getProperty('routeBefore');
		$routeBefore->setAccessible(true);
		
		$beforeRender = $refCsrf->getProperty('beforeRender');
		$beforeRender->setAccessible(true);
		
		$callback = $bindings->getValue($mb->events)["mb:route:before"][0][0];
		$this->assertEquals($routeBefore->getValue($csrf), $callback );
		
		
		$callback = $bindings->getValue($mb->events)["before:render"][0][0];
		$this->assertEquals($beforeRender->getValue($csrf), $callback );

		
		$csrf->stop();
		
		$this->assertEmpty($bindings->getValue($mb->events)["mb:route:before"]);
		$this->assertEmpty($bindings->getValue($mb->events)["before:render"]);
		
		
	}
	
}