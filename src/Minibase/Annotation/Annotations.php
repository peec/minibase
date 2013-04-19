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