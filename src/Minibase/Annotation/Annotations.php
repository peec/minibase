<?php
namespace Minibase\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Used for caching calls.
 * @Annotation
 * @Target("METHOD")
 */
final class CachedCall {
	public $key;
	public $expire = 0;
}


/**
 * Used for EventCollection class methods to bind a method to event.
 * @Annotation
 * @Target("METHOD")
 */
final class Event {
	public $name;
	
}
