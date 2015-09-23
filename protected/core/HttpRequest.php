<?php

/**
 * Class HttpRequest
 * This class instantiates a PSR-7 compliant request and makes it available.
 * @property \Psr\Http\Message\ServerRequestInterface $psr7
 */
class HttpRequest extends \CHttpRequest
{


    /**
     * A request object implementing the PSR-7 specification.
     * Using specific implementation: \Zend\Diactoros\ServerRequest
     * @var \Psr\Http\Message\ServerRequestInterface
     *
     */
    protected $_request;


    public $noCsrfValidationRoutes = [];

    public function __construct() {
        $this->initRequest();
    }

    protected function initRequest() {
        $this->_request = Zend\Diactoros\ServerRequestFactory::fromGlobals();
        // Support _method.
        if (isset($this->_request->getParsedBody()['_method'])) {
            $this->_request = $this->_request->withMethod($this->_request->getParsedBody()['_method']);
        }


        // Add support for JSON requests.
        if ($this->_request->getHeaderLine('Content-Type') == 'application/json'
        && (strcasecmp('get' ,$this->_request->getMethod()) != 0)) {
            $this->_request = $this->_request->withParsedBody(
                json_decode($this->_request->getBody()->getContents(), true)
            );
        }
    }

    public function setPsr7($value)
    {
        $this->_request = $value;
    }
    public function getPsr7()
    {
        return $this->_request;
    }
    public function setRequest(\Psr\Http\Message\ServerRequestInterface $request) {
        $this->_request = $request;
    }

    public function getPost($name, $defaultValue = null)
    {
        $body = $this->_request->getParsedBody();
        return isset($body[$name]) ? $body[$name] : $defaultValue;
    }


    protected function normalizeRequest(){
//        if (strcasecmp('post', $this->psr7->getMethod()) === 0) return;

        $route = Yii::app()->getUrlManager()->parseUrl($this);
        if($this->enableCsrfValidation){
            foreach($this->noCsrfValidationRoutes as $cr){
                if(preg_match('#'.$cr.'#', $route)){
                    Yii::app()->detachEventHandler('onBeginRequest',
                        array($this,'validateCsrfToken'));
                    Yii::trace('Route "'.$route.' passed without CSRF validation');
                    break; // found first route and break
                }
            }
        }
    }


}