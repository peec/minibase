<?php
namespace Minibase\Http;


use Minibase\Wreqr\EventBinder;

class RedirectResponseTest extends \PHPUnit_Framework_TestCase{
	
	public function testRedirectShouldAddHeaderToResponse () {
		
		$r = new RedirectResponse(new EventBinder());
		
		$r->to("http://google.com");
		
		$this->assertEquals("http://google.com", $r->headers["Location"]);
		
		
	}
	
}
