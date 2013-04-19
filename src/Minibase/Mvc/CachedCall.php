<?php
namespace Minibase\Mvc;

/**
 * @Annotation
 */
class CachedCall {
	public $key;
	public $expire = 0;
}