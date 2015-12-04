<?php
namespace nlac;

class NLSDownloader {

	public $options = array(
		'curlTimeOut' => 15,
		'curlConnectionTimeOut' => 15,
		'serverBaseUrl' => null,
		'appBaseUrl' => null
	);
	/**
	 * CURL object
	 */
	protected $ch = null;
	
	/**
	 * last error
	 */
	protected $err = null;

	public function __construct($options = array()) {

		$this->options = array_merge($this->options, $options);
		
		if (!@$this->options['serverBaseUrl']) {
			$protocol = @$_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
			$this->options['serverBaseUrl'] = $protocol . '://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'];
		}

		if (!@$this->options['appBaseUrl']) {
			$this->options['appBaseUrl'] = $this->options['serverBaseUrl'] . $_SERVER['REQUEST_URI'];
		}
	}

	public function init() {
		if ($this->ch)
			return;

		$this->ch = curl_init();

		curl_setopt($this->ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($this->ch, CURLOPT_HEADER, false);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_FAILONERROR, true);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->options['curlConnectionTimeOut']);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->options['curlTimeOut']);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($this->ch, CURLOPT_ENCODING, "");//by @le_top, from v7.0

		return $this->ch;
	}
	


	/**
	 *  @brief downloads a content from an absolute url
	 *  
	 *  @param [in] $url absolut url
	 *  @return the downloaded content
	 *  
	 */	
	public function get($url) {
		$this->init();
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$content = curl_exec($this->ch);
		if ($this->err = curl_error($this->ch)) {
			throw new \Exception('Couldn\'t download ' . $url . "\n" . print_r($this->err,true));
		}
		return $content;
	}
	
	public function getLastError() {
		return $this->err;
	}
	
	public function getCurlInfo() {
		return $this->ch ? curl_getinfo($this->ch) : null;
	}
	
	public function close() {
		if ($this->ch) {
			curl_close($this->ch);
			$this->ch = null;
		}
	}
	
	/**
	 *  
	 *
	 * Code based on http://99webtools.com/relative-path-into-absolute-url.php
	 */
	public function toAbsUrl($rel, $base = null) {

		//FB::info(array($rel, $base), 'toAbsUrl() called');
		
		if (!$base)
			$base = $this->options['appBaseUrl'];

		//already absolute URL
		if (preg_match('@^https?://@',$rel))
			return $rel;
		
		if (!$base || !preg_match('@^https?://@',$base)) {
			throw new \Exception('base url with scheme must be provided');
		}
		
		//-> $scheme, $host, $port, $path (+ maybe $query, $fragment)
		$port='';$path='';//make sure that these variables will exist
		extract(parse_url($base));
		if ($port) $port = ':'.$port;
		
		//remove non-directory (file) part from the end of $path
		$path = preg_replace('@/([^/]*\.)+[^/]*$@', '', $path);

		//removing queries + fragments
		$base = $scheme . '://' . $host . $port . $path;

		//prepending scheme to protocol-relative urls
		if (substr($rel,0,2) == '//')
			return $scheme . '://' . preg_replace('@^/+@','',$rel);

		//queries/fragments
		if ($rel[0]=='#' || $rel[0]=='?')
			return $base . $rel;

		//destroy path if relative url points to root
		if ($rel[0] == '/')
			$path = '';

		//dirty absolute URL
		$abs = $host . $port . $path . '/' . $rel;

		//replace '//' or '/./' or '/foo/../' with '/'
		$rg = '@(//)|(/\./)|(/[^/]+/\.\./)@';
		//FB::info($abs, 'cycle starts');
		for($n=1; $n>0; $abs = preg_replace($rg,'/',$abs,-1,$n)) {
			//FB::log($abs,'abs in cycle');
		}

		return $scheme . '://' . $abs;
	}

}