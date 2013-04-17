<?php
namespace Minibase\Http;


use Minibase\Wreqr\EventBinder;

class ResponseTest extends \PHPUnit_Framework_TestCase{
	
	public function testResponseHasGoodDefaults () {

		$r = $this->getMockForAbstractClass('Minibase\Http\Response');
		$r->setEvents(new EventBinder());
		$this->assertEquals(null, $r->body);
		$this->assertInstanceOf('Minibase\Wreqr\EventBinder', $r->events);
		$this->assertEmpty($r->headers);
		$this->assertEquals(200, $r->statusCode);
	}
	
	public function setShouldSetResponseCode () {
		$r = $this->getMockForAbstractClass('Minibase\Http\Response', array(new EventBinder()));
		
		$r->with(404);
		
		$this->assertEquals(404, $r->statusCode);
	}
	public function setShouldSetContentType () {
		$r = $this->getMockForAbstractClass('Minibase\Http\Response', array(new EventBinder()));
		$r->asType("application/xml");
		
		$this->assertEquals("application/xml", $r->headers['content-type']);
	}
}
