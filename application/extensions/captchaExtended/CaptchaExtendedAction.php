<?php
/**
* Yii extended captcha supporting more complex formulas and masking techniques.
*
* Features:
*    - supported modes: logical, words, mathverbal, math, default
*    - supported extended characters latin1, latin2 (utf-8) including middle- east- european and cyrillyc characters
*    - implements masking elements: dots density, through lines, fillSections, font color varying
*
* Installation:
* =============
*
* 	  1) unzip CaptchaExtended.zip files into ../protected/extensions/captchaExtended/*.*
*
*     2) Register class paths to [CaptchaExtendedAction] and [CaptchaExtendedValidator], e.g. in components/controller.php:
*
*			public function init(){
*				// import class paths for captcha extended
*				Yii::$classMap = array_merge( Yii::$classMap, array(
*					'CaptchaExtendedAction' => Yii::getPathOfAlias('ext.captchaExtended').DIRECTORY_SEPARATOR.'CaptchaExtendedAction.php',
*					'CaptchaExtendedValidator' => Yii::getPathOfAlias('ext.captchaExtended').DIRECTORY_SEPARATOR.'CaptchaExtendedValidator.php'
*				));
*			}
*
* 	  3) Define action in controller, e.g. SiteController:
*
*			public function actions(){
*				return array(
*					'captcha'=>array(
*						'class'=>'CaptchaExtendedAction',
*					),
*				);
*			}
*
* 	  4) Define client validation in model::rules():
*
*			public function rules(){
*				return array(
*					array('verifyCode', 'CaptchaExtendedValidator', 'allowEmpty'=>!CCaptcha::checkRequirements()),
*				);
*			}
*
* 	   5) If needed, collect localized strings via CLI command "yiic message messages/config.php" and translate captcha related strings.
*
* 	   6) If needed, you can tune captcha modes and visibility options:
* 		  - In "words" mode, you can place your own file [words.txt] or [words.yourlanguage.txt]
* 		  - If needed, set the dots density [0-100], the number of through lines [0-] or fillSections [0-], font and background colors
*
* 	   7) Test & enjoy!
*/
class CaptchaExtendedAction extends CCaptchaAction{

	const
		MODE_MATH = 'math',
		MODE_MATHVERBAL	= 'mathverbal',
		MODE_DEFAULT = 'default',
		MODE_LOGICAL = 'logical',
		MODE_WORDS = 'words';

	/**
	 * @var integer padding around the text. Defaults to 2.
	 */
	public $offset = 2;

	/**
	* Captcha mode, supported values are [logical, words, mathverbal, math, default].
	* Default value is [default], which uses native frameworks implementation.
	* Captcha mode examples:
	*  - logical e.g. min(5, one, 9) or max (two, three, 3)
	*  - words e.g. [localized random words] (supports translated strings in UTF-8 including latin2 and cyrillic)
	*  - mathverbal e.g. How much is 12 plus 8 ?
	*  - math e.g. 93 - 3 =
	*  - default e.g. random latin1 characters
	*/
	public $mode = self::MODE_DEFAULT;

	/**
	* Path to the file to be used for generating random words in "words" mode
	*/
	public $fileWords;

	/**
	* Dots density around characters 0 - 100 [%], defaults 5.
	*/
	public $density = 5; // dots density 0 - 100%

	/**
	* The number of lines drawn through the generated captcha picture, default 3.
	*/
	public $lines = 3;

	/**
	* The number of sections to be filled with random flood color, default 10.
	*/
	public $fillSections = 10;

	/**
	* Run action
	*/
	public function run(){
		if(!extension_loaded('mbstring')){
			throw new CHttpException(500, Yii::t('main','Missing extension "{ext}"', array('{ext}' => 'mbstring')));
		}

		// set font file with all extended UTF-8 characters
		// Duality supplied with the framework does not support some extended characters like ščťžôäě...
		$this->fontFile = dirname(__FILE__).'/fonts/nimbus.ttf';

		// set captcha mode
		$this->mode = strtolower($this->mode);

		// set image size
		switch ($this->mode){
			case self::MODE_LOGICAL:
			case self::MODE_WORDS:
				$this->width = 300;
				$this->height = 50;
				break;
			case self::MODE_MATHVERBAL:
				$this->width = 400;
				$this->height = 50;
				break;
			case self::MODE_MATH:
			case self::MODE_DEFAULT:
			default:
				$this->width = 120;
				$this->height = 50;
		}

		if($this->mode == self::MODE_DEFAULT){
			// default framework implementation
			parent::run();
		}else{
			// we hash result value rather than the displayed code
			if(isset($_GET[self::REFRESH_GET_VAR])){
				$result=$this->getVerifyResult(true);
				echo CJSON::encode(array(
					'hash1'=>$this->generateValidationHash($result),
					'hash2'=>$this->generateValidationHashCI($result),
					'url'=>$this->getController()->createUrl($this->getId(),array('v' => uniqid())),
				));
			}else{
				$this->renderImage($this->getVerifyCode());
			}
		}
		Yii::app()->end();
	}

	/**
	* Return hash for case insensitive result (converted to lowercase)
	* @param string $result
	*/
	protected function generateValidationHashCI($result){
		$result = preg_replace('/\s/', '', $result);
		$result = mb_convert_case($result, MB_CASE_LOWER, 'utf-8');
		$result = urlencode($result);
		return $this->generateValidationHash($result);
	}

	/**
	 * Generates a new verification code.
	 * @return string the generated verification code
	 */
	protected function generateVerifyCode(){
		switch (strtolower($this->mode)){
			case self::MODE_MATH:
				return $this->getCodeMath();
			case self::MODE_MATHVERBAL:
				return $this->getCodeMathVerbal();
			case self::MODE_LOGICAL:
				return $this->getCodeLogical();
			case self::MODE_WORDS:
				return $this->getCodeWords();
			case self::MODE_DEFAULT:
			default:
				$code = parent::generateVerifyCode();
				return array('code' => $code, 'result' => $code);
		}
	}

	/**
	* Return code for random words from text file.
	* First we'll try to load file for current language, like [words.de.txt]
	* If not found, we will try to load generic file like [words.txt]
	*/
	protected function getCodeWords(){
		if($this->fileWords === null){
			// guess words file upon current language like [words.de.txt], [words.ru.txt]
			$this->fileWords = dirname(__FILE__).DIRECTORY_SEPARATOR.'words.'.Yii::app()->language.'.txt';
			if(!is_file($this->fileWords)){
				// take fallback file without language specification
				$this->fileWords = dirname(__FILE__).DIRECTORY_SEPARATOR.'words.txt';
			}
		}
		if(!file_exists($this->fileWords)){
			throw new CHttpException(500, Yii::t('main','File not found in "{path}"', array('{path}' => $this->fileWords)));
		}
		$words = file_get_contents($this->fileWords);
		$words = explode(' ', $words);
		$found = array();
		for($i=0;$i<count($words);++$i){
			// select random word
			$w = array_splice($words, mt_rand(0,count($words)),1);
			if(!isset($w[0])){
				continue;
			}
			// purify word
			$w = $this->purifyWord($w[0]);
			if(strlen($w)>3){
				// accept only word with at least 3 characters
				$found[] = $w;
				if(strlen(implode('', $found))>10){
					// words must have at least 10 characters together
					break;
				}
			}
		}
		$code = implode('', $found); // without whitespaces
		return array('code' => $code, 'result' => $code);
	}

	/**
	* Return captcha word without dirty characters like *,/,{,},.. Retain diacritics if unicode supported.
	* @param string $w The word to be purified
	*/
	protected function purifyWord($w){
		if(@preg_match('/\pL/u', 'a')){
			// unicode supported, we remove everything except for accented characters
			$w = preg_replace('/[^\p{L}]/u', '', (string) $w);
		}else{
			// Unicode is not supported. Cannot validate utf-8 characters, we keep only latin1
			$w = preg_replace('/[^a-zA-Z0-9]/','',$w);
		}
		return $w;
	}

	/**
	* Return code for math mode like 9+1= or 95-5=
	*/
	protected function getCodeMath(){
		$n2 = mt_rand(1,9);
		if(mt_rand(1,100) > 50){
			$n1 = mt_rand(1,9)*10+$n2;
			$code = $n1.'-'.$n2.'=';
			$r = $n1-$n2;
		}else{
			$n1 = mt_rand(1,10)*10-$n2;
			$code = $n1.'+'.$n2.'=';
			$r = $n1+$n2;
		}
		return array('code' => $code, 'result' => $r);
	}

	/**
	* Return numbers 0..9 translated into word
	*/
	protected static function getNumbers(){
		return array(
			'0' => Yii::t('main','zero'),
			'1' => Yii::t('main','one'),
			'2' => Yii::t('main','two'),
			'3' => Yii::t('main','three'),
			'4' => Yii::t('main','four'),
			'5' => Yii::t('main','five'),
			'6' => Yii::t('main','six'),
			'7' => Yii::t('main','seven'),
			'8' => Yii::t('main','eight'),
			'9' => Yii::t('main','nine'),
		);
	}

	/**
	* Return verbal representation for supplied number, like 1 => one
	* @param int $n The number to be translated
	*/
	protected static function getNumber($n){
		static $nums;
		if(empty($nums)){
			$nums = self::getNumbers();
		}
		return array_key_exists($n, $nums) ? $nums[$n] : '';
	}

	/**
	* Return code for logical formula like min(one,7,four)
	*/
	protected function getCodeLogical(){
		$t = mt_rand(2,4);
		$a = array();
		for($i=0;$i<$t;++$i){
			// we dont use zero
			$a[] = mt_rand(1,9);
		}
		if(mt_rand(0,1)){
			$r = max($a);
			$code = array();
			for($i=0;$i<count($a);++$i){
				$code[] = mt_rand(1,100)>30 ? self::getNumber($a[$i]) : $a[$i];
			}
			$code = Yii::t('main','max').' ( '.implode(', ',$code).' )';
		}else{
			$r = min($a);
			$code = array();
			for($i=0;$i<count($a);++$i){
				$code[] = mt_rand(1,100)>30 ? self::getNumber($a[$i]) : $a[$i];
			}
			$code = Yii::t('main','min').' ( '.implode(', ',$code).' )';
		}
		return array('code' => $code, 'result' => $r);
	}

	/**
	* Return code for verbal math mode like "How much is 1 plus 1 ?"
	*/
	protected function getCodeMathVerbal(){
		$n2 = mt_rand(1,9);
		if(mt_rand(1,100) > 50){
			switch(mt_rand(0,2)){
				case 0:
					$op = Yii::t('main','minus');
					break;
				case 1:
					$op = Yii::t('main','deducted by');
					break;
				case 2:
					$op = '-';
					break;
			}
			$n1 = mt_rand(1,9)*10+$n2;
			$code = $n1.' '.$op.' '. ( mt_rand(1,10)>3 ? self::getNumber($n2) : $n2);
			$r = $n1-$n2;
		}else{
			switch(mt_rand(0,2)){
				case 0:
					$op = Yii::t('main','plus');
					break;
				case 1:
					$op = Yii::t('main','and');
					break;
				case 2:
					$op = '+';
					break;
			}
			$n1 = mt_rand(1,10)*10-$n2;
			$code = $n1.' '.$op.' '.( mt_rand(1,10)>3 ? self::getNumber($n2) : $n2);
			$r = $n1+$n2;
		}
		switch (mt_rand(0,2)){
			case 0:
				$question = Yii::t('main','How much is');
				break;
			case 1:
				$question = Yii::t('main','What\'s result for');
				break;
			case 2:
				$question = Yii::t('main','Give result for');
				break;
		}

		switch (mt_rand(0,2)){
			case 0:
				$equal = '?';
				break;
			case 1:
				$equal = '=';
				break;
			case 2:
				$equal = str_repeat('.', mt_rand(2,5));
				break;
		}

		$code = $question.' '.$code.' '.$equal;
		return array('code' => $code, 'result' => $r);
	}


	/**
	 * Validates the input to see if it matches the generated code.
	 * @param string $input user input
	 * @param boolean $caseSensitive whether the comparison should be case-sensitive
	 * @return whether the input is valid
	 */
	public function validate($input,$caseSensitive){
		// open session, if necessary generate new code
		$this->getVerifyCode();
		// read result
		$session = Yii::app()->session;
		$name = $this->getSessionKey();
		$result = $session[$name . 'result'];
		// input always taken without whitespaces
		$input = preg_replace('/\s/','',$input);
		$valid = $caseSensitive ? strcmp($input, $result)===0 : strcasecmp($input, $result)===0;
		// increase attempts counter, but not in case of ajax-client validation (that is always POST request having variable 'ajax')
		// otherwise captcha would be silently invalidated after entering the number of fields equaling to testlimit number
		if(empty($_POST['ajax'])){
			$name = $this->getSessionKey() . 'count';
			$session[$name] = $session[$name] + 1;
			if($valid || $session[$name] > $this->testLimit && $this->testLimit > 0){
				// generate new code also each time correctly entered
				$this->getVerifyCode(true);
			}
		}
		return $valid;
	}

	/**
	 * Gets the verification code.
	 * @param boolean $regenerate whether the verification code should be regenerated.
	 * @return string the verification code.
	 */
	public function getVerifyCode($regenerate=false){
		if($this->fixedVerifyCode !== null){
			return $this->fixedVerifyCode;
		}
		$session = Yii::app()->session;
		$session->open();
		$name = $this->getSessionKey();
		if(empty($session[$name]) || $regenerate){
			$code = $this->generateVerifyCode();
			$session[$name] = $code['code'];
			$session[$name . 'result'] = $code['result'];
			$session[$name . 'count'] = 1;
		}
		return $session[$name];
	}

	/**
	* Return verification result expected by user
	* @param bool $regenerate
	*/
	public function getVerifyResult($regenerate=false){
		if($this->fixedVerifyCode !== null){
			return $this->fixedVerifyCode;
		}
		$session = Yii::app()->session;
		$session->open();
		$name = $this->getSessionKey();
		if(empty($session[$name . 'result']) || $regenerate){
			$code = $this->generateVerifyCode();
			$session[$name] = $code['code'];
			$session[$name . 'result'] = $code['result'];
			$session[$name . 'count'] = 1;
		}
		return $session[$name . 'result'];
	}

	/**
	 * Renders the CAPTCHA image based on the code.
	 * @param string $code the verification code
	 * @return string image content
	 */
	protected function renderImage($code){
		$image = imagecreatetruecolor($this->width,$this->height);
		$backColor = imagecolorallocate($image,
				(int)($this->backColor % 0x1000000 / 0x10000),
				(int)($this->backColor % 0x10000 / 0x100),
				$this->backColor % 0x100);
		imagefilledrectangle($image,0,0,$this->width,$this->height,$backColor);
		imagecolordeallocate($image,$backColor);

		if($this->transparent){
			imagecolortransparent($image,$backColor);
		}

		if($this->fontFile === null){
			$this->fontFile = realname(Yii::app()->basePath."/../assets/fonts/font-src/lato-v11-latin-700.ttf");
			
		}

		$length = strlen($code);
		$box = imagettfbbox(25,0,$this->fontFile,$code);
		$w = $box[4] - $box[0] + $this->offset * ($length - 1);
		$h = $box[1] - $box[5];
		$scale = min(($this->width - $this->padding * 2) / $w,($this->height - $this->padding * 2) / $h);
		$x = 10;
		$y = round($this->height * 27 / 40);

		$r = (int)($this->foreColor % 0x1000000 / 0x10000);
		$g = (int)($this->foreColor % 0x10000 / 0x100);
		$b = $this->foreColor % 0x100;
		$foreColor = imagecolorallocate($image, mt_rand($r-50,$r+50), mt_rand($g-50,$g+50),mt_rand($b-50,$b+50));

		for($i = 0; $i < $length; ++$i){
			$fontSize = (int)(rand(26,32) * $scale * 0.8);
			$angle = rand(-10,10);
			$letter = $code[$i];

			// UTF-8 characters above > 127 are stored in two bytes
			if(ord($letter)>127){
				++$i;
				$letter .= $code[$i];
			}

			// randomize font color
			if(mt_rand(0,10)>7){
				$foreColor = imagecolorallocate($image, mt_rand($r-50,$r+50), mt_rand($g-50,$g+50),mt_rand($b-50,$b+50));
			}

			$box = imagettftext($image,$fontSize,$angle,$x,$y,$foreColor,$this->fontFile,$letter);
			$x = $box[2] + $this->offset;
		}

		// add density dots
		$this->density = intval($this->density);
		if($this->density > 0){
			$length = intval($this->width*$this->height/100*$this->density);
			$c = imagecolorallocate($image, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
			for($i=0;$i<$length;++$i){
				$x = mt_rand(0,$this->width);
				$y = mt_rand(0,$this->height);
				imagesetpixel($image, $x, $y, $c);
			}
		}

		// add lines
		$this->lines = intval($this->lines);
		if($this->lines > 0){
			for($i=0; $i<$this->lines; ++$i){
				imagesetthickness($image, mt_rand(1,2));
				// gray lines only to save human eyes:-)
				$c = imagecolorallocate($image, mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
				$x = mt_rand(0, $this->width);
				$y = mt_rand(0, $this->width);
				imageline($image, $x, 0, $y, $this->height, $c);
			}
		}

		// filled flood section
		$this->fillSections = intval($this->fillSections);
		if($this->fillSections > 0){
			for($i = 0; $i < $this->fillSections; ++$i){
				$c = imagecolorallocate($image, mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
				$x = mt_rand(0, $this->width);
				$y = mt_rand(0, $this->width);
				imagefill($image, $x, $y, $c);
			}
		}

        imagecolordeallocate($image,$foreColor);

		header('Expires:0');
        header("Cache-Control: must-revalidate, no-store, no-cache");
		header('Content-Transfer-Encoding:binary');
        header("Content-type:image/png");
        imagepng($image); //This will normally output the image, but because of ob_start(), it won't.
		imagedestroy($image);
	}

}
