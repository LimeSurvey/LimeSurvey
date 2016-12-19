<?php
/**
 * Cint Link API
 *
 * @author Jon Swope
 */


/**
 * Main class for accessing the CintLink API.
 *
 */
class CintLinkAPI {
  /**
   * API Key
   */
  private $apiKey = "";

  /**
   * API Shared Secret
   */
  private $apiSecret = "";

  /**
   * Constructor
   *
   * @param string $apiKey Your API key
   * @param string $apiSecret Your API shared secret
   */
  function __construct($apiKey, $apiSecret) {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
  }

  /**
   * Creates an HMAC signature suitable for sending requests to the Cint Link backend
   *
   * This is made available as a utility, but authorizationHeader should probably be used in most cases.
   *
   * @param string $url The URL to be requested
   * @param string $body The body of the request, if any
   */
  public function createSignature($url, $body = "") {
    $baseString = $url . " " . $body;
    $hash = hash_hmac('sha1', $baseString, $this->apiSecret, true);
    $signature = base64_encode($hash);
    return base64_encode($this->apiKey . ':' . $signature);
  }

  /**
   * Creates the content of an Authorization header suitable for sending requests to the Cint Link backend
   *
   * The output of this function should be used in an Authorization header along with your request. The details
   * on how to do this vary based on your framework.
   *
   * @param string $url The URL to be requested
   * @param string $body The body of the request, if any
   */
  public function authorizationHeader($url, $body = "") {
    return "CDS " . $this->createSignature($url, $body);
  }

  /**
   * Setup a Curl request ready for authorized use
   *
   * @param string $url Url to use for authorization
   */
  public function setupAuthorizedRequest($url) {
    $auth = $this->authorizationHeader($url);
    $curl = new Curl;
    $curl->headers["Authorization"] = $auth;
    $curl->headers["Accept"] = "application/xml";
    $curl->headers["Content-Length"] = "0";
    $curl->headers["Expect"] = "";
    return $curl;
  }

  /**
   * Creates an order from a purchase request URL
   *
   * The purchase request URL should be either the hold or create URL passed to your page from the widget.
   *
   * @param string $purchaseRequestUrl The URL to be acted upon
   * @return CintLinkOrder The resulting order
   */
  public function createOrder($purchaseRequestUrl) {
    $curl = $this->setupAuthorizedRequest($purchaseRequestUrl);
    $resp = $curl->post($purchaseRequestUrl);
    if ($resp->statusCode < 200 || $resp->statusCode >= 300)
      throw new Exception($resp->body, $resp->statusCode);

    return $this->getOrder($resp->headers['Location']);
  }

  /**
   * Retrieves an order from a given URL
   *
   * @param string $orderUrl The URL of the order to be retrieved
   * @return CintLinkOrder The resulting order
   */
  public function getOrder($orderUrl) {
    $curl = $this->setupAuthorizedRequest($orderUrl);
    $resp = $curl->get($orderUrl);

    if ($resp->statusCode < 200 || $resp->statusCode >= 300)
      throw new Exception($resp->body, $resp->statusCode);
    
    $parsedResp = new SimpleXmlElement($resp->body);
    return new CintLinkOrder($parsedResp, $this);
  }

  /**
   * Retrieves an href by relation
   *
   * Retrieves an href from the passed link block by the given relation
   *
   * @param string $rel The name of the relation to search for
   * @param array $links Array of links to search through
   * @return string The href that was found or false if none found
   */
  public static function getLink($rel, $links) {
    foreach($links as $link)
      if ($link['rel'] == $rel)
        return ((string) $link['href']);
    return false;
  }
}


class Curl {
    
    /**
     * An associative array of headers to send along with requests
     *
     * @var array
    **/
    public $headers = array();
    
    /**
     * An associative array of CURLOPT options to send along with requests
     *
     * @var array
    **/
    public $options = array();
    
    /**
     * The referer header to send along with requests
     *
     * @var string
    **/
    public $referer;
    
    /**
     * The user agent to send along with requests
     *
     * @var string
    **/
    public $user_agent;
    
    /**
     * Stores an error string for the last request if one occurred
     *
     * @var string
     * @access protected
    **/
    protected $error = '';
    
    /**
     * Stores resource handle for the current CURL request
     *
     * @var resource
     * @access protected
    **/
    protected $request;
    
    /**
     * Initializes a Curl object
     *
     * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)' otherwise
    **/
    function __construct() {
        $this->user_agent = 'Curl/PHP '.PHP_VERSION.' (Cint PHP Library)';
    }
    
    /**
     * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse object
    **/
    function delete($url, $vars = array()) {
        return $this->request('DELETE', $url, $vars);
    }
    
    /**
     * Returns the error string of the current request if one occurred
     *
     * @return string
    **/
    function error() {
        return $this->error;
    }
    
    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse
    **/
    function get($url, $vars = array()) {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }
        return $this->request('GET', $url);
    }
    
    /**
     * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse
    **/
    function head($url, $vars = array()) {
        return $this->request('HEAD', $url, $vars);
    }
    
    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse|boolean
    **/
    function post($url, $vars = array()) {
        return $this->request('POST', $url, $vars);
    }
    
    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse|boolean
    **/
    function put($url, $vars = array()) {
        return $this->request('PUT', $url, $vars);
    }
    
    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $method
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse|boolean
    **/
    function request($method, $url, $vars = array()) {
        $this->error = '';
        $this->request = curl_init();
        if (is_array($vars)) $vars = http_build_query($vars, '', '&');

        Yii::log('url = ' . $url, CLogger::LEVEL_TRACE, 'cintlink');
        Yii::log('vars = ' . print_r($vars, true), CLogger::LEVEL_TRACE, 'cintlink');
        
        $this->set_request_method($method);
        $this->set_request_options($url, $vars);
        $this->set_request_headers();
        
        $response = curl_exec($this->request);
        
        if ($response) {
            $response = new CurlResponse($response);
        } else {
            $this->error = curl_errno($this->request).' - '.curl_error($this->request);
        }
        
        curl_close($this->request);
        
        return $response;
    }
    
    /**
     * Formats and adds custom headers to the current request
     *
     * @return void
     * @access protected
    **/
    protected function set_request_headers() {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key.': '.$value;
        }
        Yii::log('headers = ' . print_r($headers, true), CLogger::LEVEL_TRACE, 'cintlink');
        curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
    }
    
    /**
     * Set the associated CURL options for a request method
     *
     * @param string $method
     * @return void
     * @access protected
    **/
    protected function set_request_method($method) {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->request, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->request, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }
    
    /**
     * Sets the CURLOPT options for the current request
     *
     * @param string $url
     * @param string $vars
     * @return void
     * @access protected
    **/
    protected function set_request_options($url, $vars) {
        curl_setopt($this->request, CURLOPT_URL, $url);
        if (!empty($vars)) curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        
        # Set some default CURL options
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->referer) curl_setopt($this->request, CURLOPT_REFERER, $this->referer);
        
        # Set any custom CURL options
        foreach ($this->options as $option => $value) {
            curl_setopt($this->request, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
    }

}

class CurlResponse {
    
    /**
     * The body of the response without the headers block
     *
     * @var string
    **/
    public $body = '';
    
    /**
     * An associative array containing the response's headers
     *
     * @var array
    **/
    public $headers = array();

    /**
     * The status of the last request
     *
     * @var int
    **/
    public $statusCode = 0;

    
    /**
     * Accepts the result of a curl request as a string
     *
     * <code>
     * $response = new CurlResponse(curl_exec($curl_handle));
     * echo $response->body;
     * echo $response->headers['Status'];
     * </code>
     *
     * @param string $response
    **/
    function __construct($response) {
        Yii::log('CurlResponse response = ' . $response, CLogger::LEVEL_TRACE, 'cintlink');
        # Headers regex
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
        
        # Extract headers from response
        preg_match_all($pattern, $response, $matches);
        $headers_string = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));
        
        # Remove headers from the response body
        $this->body = str_replace($headers_string, '', $response);
        
        # Extract the version and status from the first header
        $version_and_status = array_shift($headers);
        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
        $this->headers['Http-Version'] = $matches[1];
        $this->statusCode = intval($matches[2]);
        $this->headers['Status'] = $matches[2].' '.$matches[3];
        
        # Convert headers into an associative array
        foreach ($headers as $header) {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
        }
    }
    
    /**
     * Returns the response body
     *
     * <code>
     * $curl = new Curl;
     * $response = $curl->get('google.com');
     * echo $response;  # => echo $response->body;
     * </code>
     *
     * @return string
    **/
    function __toString() {
        return $this->body;
    }
}
