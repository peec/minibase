<?php
namespace Minibase\Plugin;

class PluginTest extends \PHPUnit_Framework_TestCase{
	
	public function testThatMBGetsSetAndConfigurationWorks () {
		$plugin = $this->getMockForAbstractClass('Minibase\Plugin\Plugin', array(null));
		$plugin->setApp($this->getMock('Minibase\MB'));
		$plugin->config = array('arg1' => true);
		
		$this->assertInstanceOf('Minibase\MB', $plugin->mb);
		$this->assertEquals(true, $plugin->cfg('arg1', 'default value ok..'));
	}
	
}