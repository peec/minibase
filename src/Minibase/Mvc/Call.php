<?php
namespace Minibase\Mvc;


use Minibase\Http\CachedResponse;

use Minibase\MB;

use Minibase\Http\Request;
use Minibase\Wreqr\EventBinder;
use Minibase\InvalidControllerReturnException;
use Minibase\Http\Response;
use Minibase\Http\InvalidJsonRequestException;
use Minibase\Annotation\CachedCall;

class Call {
	private $call;
	private $mb;
	
	/**
	 * If this call has routing properties this is populated
	 * @var unknown_type
	 */
	public $config;
	
	public $isReversable = false;
	
	public $realObject;
	public $cacheSettings;
	
	public function __construct($call, $config = null) {
		$this->call = $call;
		if ($config) {
			$this->config = array();
			$this->config['method'] = $config[0];
			$this->config['uri'] = $config[1];
			$this->config['reverseKey'] = $config[2];
			$this->isReversable = $this->config['reverseKey'] ? true : false;
		}
	}
	
	/**
	 * Returns the reverse key
	 * @throws \Exception If this Call is not reversable.
	 */
	public function key () {
		if (!$this->isReversable) {
			throw new \Exception ("This Call has no reverse key.");
		}
		return $this->config['reverseKey'];
	}
	
	
	public function setMB (MB $mb) {
		$this->mb = $mb;
	}
	
	
	/**
	 * Reverses this Call to a URL (Reverse Route)
	 * @param array $params Array of arguments to the route, if the route has regexp parameters.
	 * @throws \Exception If Call can not be reversed or illegal number of parameters supplied.
	 * @return Minibase\Mvc\ReversedCall Object representing the reversed call. 
	 */
	public function reverse ($params = array()) {
		if (!$this->config){
			throw new \Exception("This call can not be reversed. No configuration array set.");
		}
		
		$i = 0;
		$uri = $this->config['uri'];
		
		$callName = $this->config['reverseKey'] ?: "Callback route";
		$pattern = '#(\(.*?\))#i';
		$urlComponent = preg_replace_callback($pattern, function ($matches) use (&$i, $params, $uri, $pattern, $callName) {
			if (!isset($params[$i])) {
				preg_match($pattern, $uri, $arguments);
				$argCount = count($arguments);
				throw new \Exception("Could not reverse route ({$callName}), index {$i} not found in parameters to reverse method. Should be {$argCount} parameters supplied to this reverse route.");
			}
			$replacement = $params[$i];
			$i++;
			return $replacement;
		}, $uri);
		
		$request = $this->mb->request;
		
		$url = $request->basePath;
		if ($request->isRewriteEnabled === false) {
			$url .= $request->scriptName . ($urlComponent != '/' ? '/' : '');
		}
		
		$url .= substr($urlComponent, 1);
		
		return new ReversedCall($url, $urlComponent, $this->mb->request);
	}
	
	
	/**
	 * If annotations are on the controller / methods these are checked here.
	 * Interception is possible, if this returns a response the response should
	 * be sent instead of the original super call.
	 * 
	 * @throws \Exception
	 */
	public function getBeforeResponseIfAny () {
		
		list ($contrInstance, $method) = $this->getControllerMethod();
		
		$annotations = array();
		// Controller / method handle.
		if (is_array($this->call)) {
				
				
			$annotations = $this->mb->annotationReader->getMethodAnnotations(new \ReflectionMethod($contrInstance, $method));
			// after add class annotations.
			$annotations = array_merge($annotations, $this->mb->annotationReader->getClassAnnotations(new \ReflectionClass($contrInstance)));
		
				
			foreach($annotations as $anot) {
				$customAnotationReturns = $this->mb->events->trigger("mb:call:execute:annotation", array($anot, $contrInstance), function () {
						
				});
				foreach($customAnotationReturns as $customAnotationReturn) {
					// If the event returns a `Minibase\Http\Response` object, execution of the
					// current call is stopped and the specific Response is returned instead.
					if ($customAnotationReturn && $customAnotationReturn instanceof Response) {
						$customAnotationReturn->execute();
						return $customAnotationReturn;
					}
				}	
		
				if ($anot instanceof CachedCall) {
					if (!$anot->key) {
						throw new \Exception ("$controller.$method: Annotation CachedCall must have a key parameter defined.");
					} else {
						$this->cacheSettings = $anot;
					}
				}
			}
			// Get cache.
			if ($this->cacheSettings) {
				if ($this->mb->cache->contains($this->cacheSettings->key)) {
					$respCache = $this->mb->cache->fetch($this->cacheSettings->key);
					$respCache->execute();
					return $respCache;
				}
			}
			
			
			
		}
		
		// Last resort to override ( After all annotations are set. )
		$overrideCalls = $this->mb->events->trigger("mb:call:execute", array($this->mb->request, $annotations));
		foreach($overrideCalls as $oCall) {
			if ($oCall && ($oCall instanceof Response || is_callable($oCall))) {
				$oCall = $this->mb->getCall($oCall);
				$oCall->execute();
				return $oCall;
			}
		}
		
	}
	
	public function getControllerMethod () {
		if ($this->realObject) {
			return $this->realObject;
		}
		
		$call = $this->call;
		if (is_array($call)) {
			list ($controller, $method) = $call;
			$contrInstance = new $controller();
		} else { // Expect closure.
			$contrInstance = new ClosureController($call);
			$method = ClosureController::CALLBACK_NAME;	
		}
		if (!($contrInstance instanceof Controller)) {
			throw new \Exception("$controller must extend Minibase\\Mvc\\Controller.");
		}
		$contrInstance->setMB($this->mb);
			
		$this->realObject = array($contrInstance, $method);
		
		return $this->realObject;
	}
	
	
	/**
	 * Executes this call and response.
	 * Returns the response afterwards.
	 * @throws InvalidControllerReturnException
	 */
	public function execute () {
		$call = $this->call;
		
		list ($contrInstance, $method) = $this->getControllerMethod();
		
		$call = array($contrInstance, $method);
		
		$resp = call_user_func_array($call, array($this->mb->request->params, $this->mb));
		
		if (!($resp instanceof Response)){
			throw new InvalidControllerReturnException("Controllers must return instances of a Response.");
		}
		$resp->execute();
		
		// Save cache
		if ($this->cacheSettings) {
			$cachedResponse = new CachedResponse($resp->headers, $resp->body, $resp->statusCode);
			$this->mb->cache->save($this->cacheSettings->key, $cachedResponse, $this->cacheSettings->expire);
		}
		
		return $resp;
	}
	
}