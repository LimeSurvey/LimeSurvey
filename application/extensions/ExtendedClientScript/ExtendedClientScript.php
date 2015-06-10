<?php
/**
 * Compress and cache used JS and CSS files.
 * Needs jsmin and cssmin
 *
 * Ties into the 1.0.4 and up Yii CClientScript functions
 *
 * 0.9.0 Now using CssMin code.google.com/p/cssmin/ for PHP 5.3.x compatibility
 *
 * Now checking and excluding remote files automatically
 *
 * @author Maxximus <maxximus007@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Kir <>
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011
 * @license htp://www.yiiframework.com/license/
 * @version 0.9.0
 *
 */

class ExtendedClientScript extends CClientScript
{
	/**
	* Compress all Javascript files with JSMin. JSMin must be installed as an extension in $jssminPath.
	* github.com/rgrove/jsmin-php/
	*/
	public $compressJs = false;
	/**
	* Compress all CSS files with CssMin. CssMin must be installed as an extension in $cssMinPath.
	* Specific browserhacks will be removed, so don't add them in to be compressed CSS files.
	* code.google.com/p/cssmin/
	*/
	public $compressCss = false;
	/**
	* DEPRECATED/LEGACY
	* Combine all JS and CSS files into one. Be careful with relative paths in CSS.
	*/
	public $combineFiles = false;
	/**
	* Combine all non-remote JS files into one.
	*/
	public $combineJs = false;
	/**
	* Combine all non-remote CSS files into one. Be careful with relative paths in CSS.
	*/
	public $combineCss = false;
	/**
	* Exclude certain files from inclusion. array('/path/to/excluded/file') Useful for fixed base
	* and incidental additional JS.
	*/
	public $excludeFiles = array();
	/**
	* Path where the combined/compressed file will be stored. Will use coreScriptUrl if not defined
	*/
	public $filePath;
	/**
	* If true, all files to be included will be checked if they are modified.
	* To enhance speed (eg production) set to false.
	*/
	public $autoRefresh = true;
	/**
	* Relative Url where the combined/compressed file can be found
	*/
	public $fileUrl;
	/**
	* Path where files can be found
	*/
	public $basePath;
	/**
	* Used for garbage collection. If not accessed during that period: remove.
	*/
	public $ttlDays = 1;
	/**
	* prefix for the combined/compressed files
	*/
	public $prefix = 'c_';
	/**
	* path to JsMin
	*/
	public $jsMinPath = 'ext.ExtendedClientScript.jsmin.*';
	/**
	* path to CssMin
	*/
	public $cssMinPath = 'ext.ExtendedClientScript.cssmin.*';
	/**
	* CssMin filter options. Default values according cssMin doc.
	*/
	public $cssMinFilters = array
	(
        'ImportImports'                 => false,
        'RemoveComments'                => true,
        'RemoveEmptyRulesets'           => true,
        'RemoveEmptyAtBlocks'           => true,
        'ConvertLevel3AtKeyframes'      => false,
        'ConvertLevel3Properties'       => false,
        'Variables'                     => true,
        'RemoveLastDelarationSemiColon' => true
	);
	/**
	* CssMin plugin options. Maximum compression and conversion.
	*/
	public $cssMinPlugins = array
	(
		'Variables'                => true,
		'ConvertFontWeight'        => true,
		'ConvertHslColors'         => true,
		'ConvertRgbColors'         => true,
		'ConvertNamedColors'       => true,
		'CompressColorValues'      => true,
		'CompressUnitValues'       => true,
		'CompressExpressionValues' => true,
	);

	private $_changesHash = '';
   private $_renewFile;

	/**
	* Will combine/compress JS and CSS if wanted/needed, and will continue with original
	* renderHead afterwards
	*
	* @param <type> $output
	*/
	public function renderHead(&$output)
	{
		if ($this->combineFiles)
			$this->combineJs = $this->combineCss = true;

		$this->renderJs($output, parent::POS_HEAD);

		if ($this->combineCss)
		{
			if (count($this->cssFiles) !== 0)
			{
				foreach ($this->cssFiles as $url => $media)
					if(! $this->isRemoteFile($url))
						$cssFiles[$media][$url] = $url; // Exclude remote files

				foreach ($cssFiles as $media => $url)
					$this->combineAndCompress('css', $url, $media);
			}
		}
		parent::renderHead($output);
	}

	/**
	* Will combine/compress JS if wanted/needed, and will continue with original
	* renderBodyEnd afterwards
	*
	* @param <type> $output
	*/
	public function renderBodyBegin(&$output)
	{
		$this->renderJs($output, parent::POS_BEGIN);
		parent::renderBodyBegin($output);
	}

	/**
	* Will combine/compress JS if wanted/needed, and will continue with original
	* renderBodyEnd afterwards
	*
	* @param <type> $output
	*/
	public function renderBodyEnd(&$output)
	{
		$this->renderJs($output, parent::POS_END);
		parent::renderBodyEnd($output);
	}


	/**
	 *
	 *
	 * @param <type> $output
	 * @param <type> $pos
	 */
	private function renderJs($output, $pos)
	{
		if ($this->combineJs)
		{
			if (isset($this->scriptFiles[$pos]) && count($this->scriptFiles[$pos]) !==  0)
			{
				$jsFiles = $this->scriptFiles[$pos];

				foreach ($jsFiles as &$fileName)
					(!empty($this->excludeFiles) && in_array($fileName, $this->excludeFiles) || $this->isRemoteFile($fileName)) AND $fileName = false;

				$jsFiles = array_filter($jsFiles);
				$this->combineAndCompress('js', $jsFiles, $pos);
			}
		}
	}

	/**
	* Performs the actual combining and compressing
	*
	* @param <type> $type
	* @param <type> $urls
	* @param <type> $pos
	*/
	private function combineAndCompress($type, $urls, $pos)
	{
		$this->fileUrl or $this->fileUrl = $this->getCoreScriptUrl();
		$this->basePath or $this->basePath = realpath($_SERVER['DOCUMENT_ROOT']);
		$this->filePath or $this->filePath = $this->basePath.$this->fileUrl;

		$optionsHash = ($type == 'js') ? md5($this->basePath . $this->compressJs . $this->ttlDays . $this->prefix)
												 : md5($this->basePath . $this->compressCss . $this->ttlDays . $this->prefix . serialize($this->cssMinFilters) . serialize($this->cssMinPlugins));

		if ($this->autoRefresh)
		{
			$mtimes = array();

			foreach ($urls as $file)
			{
				$fileName = $this->basePath.'/'.trim($file,'/');

				if(file_exists($fileName))
				{
					$mtimes[] = filemtime($fileName);
				}
			}
			$this->_changesHash = md5(serialize($mtimes));
		}

		$combineHash = md5(implode('',$urls));

		$fileName = $this->prefix.md5($combineHash.$optionsHash.$this->_changesHash).".$type";

		$this->_renewFile = (file_exists($this->filePath.'/'.$fileName)) ? false : true;

		if ($this->_renewFile)
		{
			$this->garbageCollect($type);
			$combinedFile = '';

			foreach ($urls as $key => $file)
				$combinedFile .= file_get_contents($this->basePath.'/'.$file);

			if ($type == 'js' && $this->compressJs)
				$combinedFile = $this->minifyJs($combinedFile);

			if ($type == 'css' && $this->compressCss)
				$combinedFile = $this->minifyCss($combinedFile);

			file_put_contents($this->filePath.'/'.$fileName, $combinedFile);
		}

		foreach ($urls as $url)
			$this->scriptMap[basename($url)] = $this->fileUrl.'/'.$fileName;

		$this->remapScripts();
	}

	private function garbageCollect($type)
	{
		$files = CFileHelper::findFiles($this->filePath, array('fileTypes' => array($type), 'level'=> 0));

		foreach($files as $file)
		{
			if (strpos($file, $this->prefix) !== false && $this->fileTTL($file))
				unlink($file);
		}
	}

	/**
	* See if file is ready for deletion
	*
	* @param <type> $file
	*/
	private function fileTTL($file)
	{
		if(!file_exists($file)) return false;
		$ttl = $this->ttlDays * 60 * 60 * 24;
		return ((fileatime($file) + $ttl) < time()) ? true : false;
	}

	/**
	* Minify javascript with JSMin
	*
	* @param <type> $js
	*/
	private function minifyJs($js)
	{
		Yii::import($this->jsMinPath);
		return JSMin::minify($js);
	}

	/**
	* Minify css with cssmin
	*
	* @param <type> $css
	*/
	private function minifyCss($css)
	{
		Yii::import($this->cssMinPath);
		return cssmin::minify($css, $this->cssMinFilters, $this->cssMinPlugins);
	}

	/**
	* See if file is on remote server
	*
	* @param <type> $file
	*/
	private function isRemoteFile($file) {
		return (strpos($file, 'http://') === 0 || strpos($file, 'https://') === 0) ? true : false;
	}
}