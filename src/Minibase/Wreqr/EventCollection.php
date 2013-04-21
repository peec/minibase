<?php
namespace Minibase\Wreqr;

use Minibase\Annotation\Event;

use Minibase\MB;

abstract class EventCollection {
	protected $mb;
	
	final public function setMB (MB $mb) {
		$this->mb = $mb;
	}
	
	
	final public function unbindAll () {
		$ref = new \ReflectionObject($this);
		$methods = $ref->getMethods();
		foreach($methods as $method) {
			$annotations = $this->mb->annotationReader->getMethodAnnotations($method);
			foreach ($annotations as $anot) {
				if ($anot instanceof Event) {
					$this->mb->events->off($anot->name, array($this,$method->name));
				}
			}
		}
	}
	
	final public function bindAll () {
		$ref = new \ReflectionObject($this);
		$methods = $ref->getMethods();
		foreach($methods as $method) {
			$annotations = $this->mb->annotationReader->getMethodAnnotations($method);
			foreach ($annotations as $anot) {
				if ($anot instanceof Event) {
					$this->mb->events->on($anot->name, array($this,$method->name));
				}
			}
		}
	}
	
	
}