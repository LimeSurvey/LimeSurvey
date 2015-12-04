<?php
namespace nlac;

/**
 * NLSClientScript 7.0.0-beta
 *
 * a Yii CClientScript extension for
 * - preventing multiple loading of javascript files
 * - merging, caching registered javascript and css files
 *
 * The extension is based on the great idea of Eirik Hoem, see
 * http://www.eirikhoem.net/blog/2011/08/29/yii-framework-preventing-duplicate-jscss-includes-for-ajax-requests/
 */

/**
 * @author nlac
 */

class NLSClientScript extends \CClientScript {

/**
 * Public properties
**/

/**
 * @param int $nlsScriptPosition
 * position of the nlsclientscript code
 * If false -> the snippet won't be inserted, duplicates won't be eliminated
 *
 * In parallel, $coreScriptPosition may be set, eg. both can be set to \CClientScript::POS_END
 */
	public $nlsScriptPosition = \CClientScript::POS_HEAD;

/**
 * @param string $includePattern
 * a javascript regex eg. '/\/scripts/' - if set, only the matched URLs will be filtered, defaults to null
 * (can be set to string 'null' also to ignore it)
**/
	public $includePattern = null;

/**
 * @param string $excludePattern
 * a javascript regex eg. '/\/raw/' - if set, the matched URLs won't be filtered, defaults to null
 * (can be set to string 'null' also to ignore it)
**/
	public $excludePattern = null;

/**
 * @param int $mergeAbove
 * - only merges if there are more than mergeAbove file registered to be included at a position
 * - applies for both css and js processing
 * - it doesn't consider @imports inside css files, only counts the top-level files and compares the sum with $mergeAbove
 **/
	public $mergeAbove = 0;

/**
 * @param boolean $mergeJs
 * merge or not the registered script files, defaults to false
**/
	public $mergeJs = false;

/**
 * Merge/compress js on every request - for debug purposes only
 */
	public $forceMergeJs = false;

/**
 * @param boolean $compressMergedJs
 * minify or not the merged js file, defaults to false
**/
	public $compressMergedJs = false;

/**
 * @param boolean $mergeCss
 * merge or not the registered css files, defaults to false
**/
	public $mergeCss = false;

/**
 * Merge/compress css on every request - for debug purposes only
 */
	public $forceMergeCss = false;

/**
 * EXPERIMENTAL
 *
 * @param boolean $downloadCssResources
 * if true, it downloads all resources (web fonts, images etc) into the /assets dir
 * if false (default), the merged css will use absolute urls pointing to the original resources
**/
	public $downloadCssResources = false;

/**
 * @param boolean $compressMergedCss
 * minify or not the merged css file, defaults to false
**/
	public $compressMergedCss = false;

/**
 * @param string $mergeJsExcludePattern
 * regex for php. the matched URLs won't be filtered
 **/
	public $mergeJsExcludePattern = null;

/**
 * @param string $mergeJsIncludePattern
 * regex for php. the matched URLs will be filtered
 **/
	public $mergeJsIncludePattern = null;

/**
 * @param string $mergeCssExcludePattern
 * regex for php. the matched URLs won't be filtered
 **/
	public $mergeCssExcludePattern = null;

/**
 * @param string $mergeCssIncludePattern
 * regex for php. the matched URLs will be filtered
 **/
	public $mergeCssIncludePattern = null;

/**
 * @param boolean $mergeIfXhr
 * if true then js files will be merged even if the request rendering the view is ajax
 * (if $mergeJs and $mergeAbove conds are satisfied)
 * defaults to false - no js merging if the view is requested by ajax
 **/
	public $mergeIfXhr = false;

/**
 * @param string $resMap2Request
 * code of a js function, prepares a get url by adding the script url hashes already in the dom
 * (has effect only if mergeIfXhr is true)
 */
	public $resMap2Request = 'function(url){if (!url.match(/\?/))url += "?";return url + "&nlsc_map=" + $.nlsc.smap();};';

/**
 * @param string $serverBaseUrl - removed
 * used to transform relative urls to absolute (for CURL)
 * you may define the url of the DOCROOT on the server (defaults to a composed value from the $_SERVER members)
 **/
	//public $serverBaseUrl = '';

/**
 * @param string $appVersion
 * Optional, version of the application.
 * If set to not empty, will be appended to the merged js/css urls (helps to handle cached resources).
 **/
	public $appVersion = '';

/**
 * @param int $curlTimeOut
 * see http://php.net/manual/en/function.curl-setopt.php
 **/
	public $curlTimeOut = 15;

/**
 * @param int $curlConnectionTimeOut
 * see http://php.net/manual/en/function.curl-setopt.php
 **/
	public $curlConnectionTimeOut = 15;


/**
 * Protected members
 */

	/**
	 * Base dir to save all stuffs
	 */
	protected $workingDirPath = null;
	protected $workingDirUrl = null;

	/**
	 * CURL resource
	 */
	protected $ch = null;

	/**
	 *
	 */
	protected $downloader = null;

	/**
	 *
	 */
	protected $cssMerger = null;



	public function init() {
		parent::init();

		if (is_numeric($this->nlsScriptPosition))
			//-> we need jquery
			$this->registerCoreScript('jquery');

		//set root working dir
		$this->workingDirPath = rtrim(\Yii::app()->assetManager->basePath, '/') . '/nls';
		$this->workingDirUrl = rtrim(\Yii::app()->assetManager->baseUrl, '/') . '/nls';
		if (!file_exists($this->workingDirPath))
			mkdir($this->workingDirPath);

		//setup downloader
		$serverBase = \Yii::app()->getRequest()->getHostInfo();

		$this->downloader = new NLSDownloader(array(
			'serverBaseUrl' => $serverBase,
			'appBaseUrl' => $serverBase . \Yii::app()->getRequest()->getBaseUrl(),
			'curlConnectionTimeOut' => $this->curlConnectionTimeOut,
			'curlTimeOut' => $this->curlTimeOut,
		));

		//setup css merger
		$this->cssMerger = new NLSCssMerge(array(
			'downloadResources' => $this->downloadCssResources,
			'downloadResourceRootPath' => $this->workingDirPath . '/resources',
			'downloadResourceRootUrl' => $this->workingDirUrl . '/resources',
			'minify' => $this->compressMergedCss,
			'closeCurl' => false
		), $this->downloader);

	}

	protected function addAppVersion($url) {
		if (!empty($this->appVersion) && !NLSUtils::isAbsoluteUrl($url))
			$url = NLSUtils::addUrlParams($url, array('nlsver' => $this->appVersion));
		return $url;
	}

		/**
 * Generates the file name of a resource
 */
	protected function hashedName($name, $ext = 'js') {
		$r = 'nls' . crc32($name);
		if ($ext=='css' && $this->downloadCssResources)
			$r.= '.dcr';
		if (($ext == 'js' && $this->compressMergedJs) || ($ext == 'css' && $this->compressMergedCss))
			$r .= '.min';
		$r .= '.' . $ext;
		if ($this->appVersion)
			$r .= '?' . $this->appVersion;
		return $r;
	}

/**
 * Simple string hash, same implemented also in the js part
 */
	protected function h($s) {
		$h = 0; $len = strlen($s);
		for ($i = 0; $i < $len; $i++) {
			$h = (($h<<5)-$h)+ord($s[$i]);
			$h &= 1073741823;
		}
		return $h;
	}

	protected function _mergeJs($pos) {
		$smap = null;

		if (\Yii::app()->request->isAjaxRequest) {
			//do not merge for ajax requests
			if (!$this->mergeIfXhr)
				return;

			if ($smap = @$_REQUEST['nlsc_map'])
				$smap = @json_decode($smap);
		}

		if ($this->mergeJs && !empty($this->scriptFiles[$pos]) && count($this->scriptFiles[$pos]) > $this->mergeAbove) {
			$finalScriptFiles = array();
			$name = "/** Content:\r\n";
			$scriptFiles = array();

			//from yii 1.1.14 $scriptFile can be an array
			foreach($this->scriptFiles[$pos] as $src=>$scriptFile) {

				$absUrl = $this->addAppVersion($this->downloader->toAbsUrl($src));

				if ($this->mergeJsExcludePattern && preg_match($this->mergeJsExcludePattern, $absUrl)) {
					$finalScriptFiles[$src] = $scriptFile;
					continue;
				}

				if ($this->mergeJsIncludePattern && !preg_match($this->mergeJsIncludePattern, $absUrl)) {
					$finalScriptFiles[$src] = $scriptFile;
					continue;
				}

				$h = $this->h($absUrl);
				if ($smap && in_array($h, $smap))
					continue;

				//storing hash
				$scriptFiles[$absUrl] = $h;

				$name .= $src . "\r\n";
			}

			if (count($scriptFiles) <= $this->mergeAbove)
				return;

			$name .= "*/\r\n";
			$hashedName = $this->hashedName($name,'js');
			$path = $this->workingDirPath . '/' . $hashedName;
			$path = preg_replace('#\\?.*$#','',$path);
			$url = $this->workingDirUrl . '/'. $hashedName;

			if ($this->forceMergeJs || !file_exists($path)) {

				$merged = '';
				$nlsCode = ';if (!$.nlsc) $.nlsc={resMap:{}};' . "\r\n";

				foreach($scriptFiles as $absUrl=>$h) {
					$ret = $this->downloader->get($absUrl);
					$merged .= ($ret . ";\r\n");
					$nlsCode .= '$.nlsc.resMap["' . $absUrl . '"]={h:"' . $h . '",d:1};' . "\r\n";
				}

				$this->downloader->close();

				if ($this->compressMergedJs)
					$merged = \JShrink\Minifier::minify($merged);//JShrink (https://github.com/tedious/JShrink)
					//$merged = \JSMin::minify($merged);//JSMin (https://github.com/rgrove/jsmin-php)

				file_put_contents($path, $name . $merged . $nlsCode);
			}

			$finalScriptFiles[$url] = $url;
			$this->scriptFiles[$pos] = $finalScriptFiles;
		}
	}


	protected function _mergeCss() {

		if ($this->mergeCss && !empty($this->cssFiles)) {

			$newCssFiles = array();
			$names = array();
			$files = array();
			foreach($this->cssFiles as $url=>$media) {

				$absUrl = $this->addAppVersion($this->downloader->toAbsUrl($url));

				if ($this->mergeCssExcludePattern && preg_match($this->mergeCssExcludePattern, $absUrl)) {
					$newCssFiles[$url] = $media;
					continue;
				}

				if ($this->mergeCssIncludePattern && !preg_match($this->mergeCssIncludePattern, $absUrl)) {
					$newCssFiles[$url] = $media;
					continue;
				}

				if (!isset($names[$media]))
					$names[$media] = "/** Content:\r\n";
				$names[$media] .= ($url . "\r\n");

				if (!isset($files[$media]))
					$files[$media] = array();
				$files[$media][$absUrl] = $media;
			}

			//merging css files by "media"
			foreach($names as $media=>$name) {

				if (count($files[$media]) <= $this->mergeAbove) {
					$newCssFiles = array_merge($newCssFiles, $files[$media]);
					continue;
				}

				$name .= "*/\r\n";
				$hashedName = $this->hashedName($name,'css');
				$path = $this->workingDirPath . '/' . $hashedName;
				$path = preg_replace('#\\?.*$#','',$path);
				$url = $this->workingDirUrl . '/'. $hashedName;

				if ($this->forceMergeCss || !file_exists($path)) {

					$merged = '';
					foreach($files[$media] as $absUrl=>$media) {

						$css = "/* $absUrl */\r\n" . $this->cssMerger->process($absUrl);

						$merged .= ($css . "\r\n");
					}

					$this->downloader->close();

					file_put_contents($path, $name . $merged);
				}//if

				$newCssFiles[$url] = $media;
			}//media

			$this->cssFiles = $newCssFiles;
		}
	}


	//If someone needs to access these, can be useful
	public function getScriptFiles() {
		return $this->scriptFiles;
	}
	public function getCssFiles() {
		return $this->cssFiles;
	}






	public function renderHead(&$output) {

		$this->_putnlscode();

		//merging
		if ($this->mergeJs) {
			$this->_mergeJs(self::POS_HEAD);
		}
		if ($this->mergeCss) {
			$this->_mergeCss();
		}

		parent::renderHead($output);
	}

	public function renderBodyBegin(&$output) {

		//merging
		if ($this->mergeJs)
			$this->_mergeJs(self::POS_BEGIN);

		parent::renderBodyBegin($output);
	}

	public function renderBodyEnd(&$output) {

		//merging
		if ($this->mergeJs)
			$this->_mergeJs(self::POS_END);

		parent::renderBodyEnd($output);
	}

	public function registerScriptFile($url, $position = null, array $htmlOptions = array()) {
		$url = $this->addAppVersion($url);
		//\Yii::log('URL regged:' . $url, 'info');
		return parent::registerScriptFile($url, $position, $htmlOptions);
	}

	public function registerCssFile($url, $media = '') {
		return parent::registerCssFile($this->addAppVersion($url), $media);
	}

	protected function _putnlscode() {

		if (!is_numeric($this->nlsScriptPosition) || \Yii::app()->request->isAjaxRequest)
			return;

		//preparing vars for js generation
		if (!$this->excludePattern)
			$this->excludePattern = 'null';
		if (!$this->includePattern)
			$this->includePattern = 'null';
		$this->mergeIfXhr = ($this->mergeIfXhr ? 1 : 0);

		//js code
		$that = $this;
		$js = file_get_contents(__DIR__ . '/nlsc.min.js');
		$js = preg_replace_callback('/_(excludePattern|includePattern|mergeIfXhr|resMap2Request)_/', function ($m) use ($that) {
			$nlscClientParameter = $m[1];
			return trim($that->$nlscClientParameter,';');
		}, $js);

		$this->registerScript('fixDuplicateResources', $js, $this->nlsScriptPosition);
	}
}