<?php
namespace Minibase\Http;


use Minibase\Wreqr\EventBinder;

class JsonResponseTest extends \PHPUnit_Framework_TestCase{
	
	public function testJsonResponseShouldSetValues () {
		
		$r = new JsonResponse(new EventBinder());
		
		$r->data(array(1,2));
		
		$this->assertEquals("[1,2]", $r->body);
		
		
	}
	
}
