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

    public function __construct() {
        $this->initRequest();
    }

    protected function initRequest() {
        $this->_request = Zend\Diactoros\ServerRequestFactory::fromGlobals();

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


}