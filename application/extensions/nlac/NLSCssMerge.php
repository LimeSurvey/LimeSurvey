<?php
namespace nlac;

class NLSCssMerge {
	
	protected $options = array(
		'downloadResources' => true,
		'downloadResourceRootPath' => null,
		'downloadResourceRootUrl' => null,
		'minify' => false,
		'closeCurl' => true,
	);
	
	protected $downloader = array();
	
	public function __construct($options = array(), $downloader = array()) {
		
		$this->downloader = $downloader instanceof NLSDownloader ? $downloader : 
			new NLSDownloader(array_merge($this->downloader,$downloader));

		$this->options = array_merge($this->options, $options);

		if ($this->options['downloadResources'] && !file_exists($this->options['downloadResourceRootPath']))
			mkdir($this->options['downloadResourceRootPath']);
	}
	
	public function getDownloader() {
		return $this->downloader;
	}
	
	protected function replaceUrls($content, $baseUrl) {
		
		$content = preg_replace_callback('/(url\s*\(\s*[\'"]?\s*)([^\)\'"]+)/i', function($m) use ($baseUrl) {
			if (preg_match('@^data:@i',$m[2]))
				return $m[0];
			$absUrl = $this->downloader->toAbsUrl($m[2], $baseUrl);
			if ($this->options['downloadResources']) {
				$resource = $this->downloader->get($absUrl);
				$hash = crc32($absUrl);
				$ext = array();preg_match('/\.(\w+)[^.]*$/', $absUrl, $ext);
				$fn = $hash . '.' . $ext[1];
				file_put_contents($this->options['downloadResourceRootPath'] . '/' . $fn, $resource);
				return $m[1] . $this->options['downloadResourceRootUrl'] . '/' . $fn;
			} else {
				return $m[1] . $absUrl;
			}
		}, $content);

		return $content;
	}

	protected function replaceImports($content, $baseUrl, $level) {
		$content = preg_replace_callback('/^\s*@import[\'"\s]*([^\)\'"]+)[\'";\s]*/m', function($m) use ($baseUrl, $level) {
			
			return $this->process($m[1], $baseUrl, null, $level+1);

		}, $content);
		
		return $content;
	}

	//Simple css minifier script
	//code based on: http://www.lateralcode.com/css-minifier/
	protected static function minify($css) {
		$css = preg_replace( '#/\*.*?\*/#s', '', $css);
		$css = preg_replace('/\s+/', ' ', $css);
		return trim(
			str_replace(
				array('; ', ': ', ' {', '{ ', ', ', '} ', ';}'), 
				array(';',  ':',  '{',  '{',  ',',  '}',  '}' ), 
				$css
			)
		);
	}
	
	/**
	 * Processes a css recursively
	 */
	public function process($cssUrl, $baseUrl = null, $cssContent = null, $level = 0) {

		if (!$cssUrl && $cssContent===null)
			throw new \Exception('Either the content or the url of the css must be given');

		if (!$baseUrl)
			$baseUrl = $this->downloader->options['appBaseUrl'];

		if ($cssContent === null) {
			$cssUrl = $this->downloader->toAbsUrl($cssUrl, $baseUrl);			
			$cssContent = $this->downloader->get($cssUrl);
		}

		$cssContent = $this->replaceUrls($cssContent, $cssUrl);
		$cssContent = $this->replaceImports($cssContent, $cssUrl, $level);

		if ($this->options['closeCurl'] && $level==0) {
			$this->downloader->close();
		}

		if ($this->options['minify'])
			$cssContent = self::minify($cssContent);

		return $cssContent;
	}
	
	public static function processUrl($cssUrl, $options = array(), $downloader = array()) {
		$merger = new self($options, $downloader);
		return $merger->process($cssUrl);
	}

	public static function processContent($content, $cssUrl = null, $options = array(), $downloader = array()) {
		$merger = new self($options, $downloader);
		return $merger->process($cssUrl, null, $content);
	}

}