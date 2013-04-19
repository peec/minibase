<?php
namespace Minibase\Mvc;

use Minibase\MB;

use Minibase\Http\Request;
use Minibase\Wreqr\EventBinder;
use Minibase\InvalidControllerReturnException;
use Minibase\Http\Response;
use Minibase\Http\InvalidJsonRequestException;
use Doctrine\Common\Annotations\AnnotationReader;
	
class Call {
	private $call;
	private $mb;
	
	/**
	 * If this call has routing properties this is populated
	 * @var unknown_type
	 */
	public $config;
	
	public $isReversable = false;
	
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
	
	
	
	
	public function execute () {
		$call = $this->call;
		// Set the current call.
		$this->mb->call = $this;
			
		
		// Controller / method handle.
		if (is_array($call)) {
			list ($controller, $method) = $call;
			$contrInstance = new $controller();
			if (!($contrInstance instanceof Controller)) {
				throw new \Exception("$controller must extend Minibase\\Mvc\\Controller.");
			}
			
			
			
			$annotationReader = new AnnotationReader();
			$annotations = $annotationReader->getMethodAnnotations(new \ReflectionMethod($contrInstance, $method));
			
			
		} else { // Expect closure.
			$contrInstance = new ClosureController($call);
			$method = ClosureController::CALLBACK_NAME;
		}
		$contrInstance->setMB($this->mb);
		$call = array($contrInstance, $method);
		
		
		$resp = call_user_func_array($call, array($this->mb->request->params, $this->mb));
		
		
		
		if (!($resp instanceof Response)){
			throw new InvalidControllerReturnException("Controllers must return instances of a Response.");
		}
		$resp->execute();
		return $resp;
	}
	
}