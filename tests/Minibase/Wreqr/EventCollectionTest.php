<?php
namespace Minibase\Wreqr;

class EventCollectionTest extends \PHPUnit_Framework_TestCase{
	
	public function testAddEvent () {
		$mb = $this->getMock('Minibase\MB');
		$binder = $this->getMock('Minibase\Wreqr\EventBinder');
		$mb->events = $binder;
		
		$mock = $this->getMockForAbstractClass(
				'Minibase\Wreqr\EventCollection', 
				array(),
				'someEventCOllection',
				true,
				true,
				true,
				array('sad'));
		$mock->setMB($mb);
		$mb->addEventCollection($mock);
	}
	
}