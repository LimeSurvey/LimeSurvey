<?php

class InputFilter
{
	function process( $value )
	{
		// No need to filter really simple values
		if( ctype_alnum( $value ) )
			return $value;
		else
			return $this->RemoveXSS( $value );
	}

	/* RemoveXSS initially developped by kallahar - quickwired.com, 
	 * modified for TikiWiki Original code can be found here:
	 * http://quickwired.com/smallprojects/php_xss_filter_function.php
	 * Straightly borrowed from TikiWiki by the LimeSurvey project	 
	 */
	function RemoveXSS($val) {
		static $ra_as_tag_only = NULL;
		static $ra_as_attribute = NULL;
		static $ra_as_content = NULL;
		static $ra_javascript = NULL;

		// now the only remaining whitespace attacks are \t, \n, and \r
		if ( $ra_as_tag_only == NULL ) {
			$ra_as_tag_only = array('style', 'script', 'embed', 'object', 'applet', 'meta', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'base', 'xml', 'import', 'link');
			$ra_as_attribute = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload','ondragdrop', 'dynsrc', 'lowsrc', 'codebase', 'xmlns');
			$ra_as_content = array('vbscript', 'expression', 'blink', 'mocha', 'livescript', 'url', 'alert');
			$ra_javascript = array('javascript');
	///		$ra_style = array('style'); // Commented as it has been considered as a bit too aggressive
		}

		// keep replacing as long as the previous round replaced something
		while ( $this->RemoveXSSchars($val)
			|| $this->RemoveXSSregexp($ra_as_tag_only, $val, '(\<|\[\\\\xC0\]\[\\\\xBC\])\??')
			|| $this->RemoveXSSregexp($ra_as_attribute, $val)
			|| $this->RemoveXSSregexp($ra_as_content, $val, '[\.\\\\+\*\?\[\^\]\$\(\)\{\}\=\!\<\|\:;\-\/`#"\']', '(?!\s*[a-z0-9])', true)
			|| $this->RemoveXSSregexp($ra_javascript, $val, '', ':', true)
	///		|| RemoveXSSregexp($ra_style, $val, '[^a-z0-9]', '=') // Commented as it has been considered as a bit too aggressive
		);

		return $val;
	}

	function RemoveXSSchars(&$val) {
		static $patterns = NULL;
		static $replacements = NULL;
		$val_before = $val;
		$found = true;

		if ( $patterns == NULL ) {
			$patterns = array();
			$replacements = array();

			// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are
			// allowed this prevents some character re-spacing such as <java\0script>
			// note that you have to handle splits with \n, \r, and \t later since they
			// *are* allowed in some inputs
			$patterns[] = '/([\x00-\x08\x0b-\x0c\x0e-\x19])/';
			$replacements[] = '';

			// straight replacements, the user should never need these since they're
			// normal characters this prevents like 
			// <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
			// Calculate the search and replace patterns only once
			$search = 'abcdefghijklmnopqrstuvwxyz';
			$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$search .= '1234567890!@#$%^&*()';
			$search .= '~`";:?+/={}[]-_|\'\\';
			for ($i = 0; $i < strlen($search); $i++) {
				// ;? matches the ;, which is optional
				// 0{0,8} matches any padded zeros,
				// which are optional and go up to 8 chars
				// &#x0040 @ search for the hex values
				$patterns[] = '/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i';
				$replacements[] = $search[$i];
				// &#00064 @ 0{0,8} matches '0' zero to eight times
				// with a ;
				$patterns[] = '/(&#0{0,8}'.ord($search[$i]).';?)/';
				$replacements[] = $search[$i];
			}
		}
		$val = preg_replace($patterns, $replacements, $val);
		if ($val_before == $val) {
			// no replacements were made, so exit the loop
			$found = false;
		}	
		return $found;
	}

	function RemoveXSSregexp(&$ra, &$val, $prefix = '', $suffix = '', $allow_spaces = false) {
		$val_before = $val;
		$found = true;
		$patterns = array();
		$replacements = array();

		$pattern_sep = '('
			.'&#[xX]0{0,8}[9ab];?'
			.'|&#0{0,8}(9|10|13);?'
			.'|(?ms)(\/\*.*?\*\/|\<\!\-\-.*?\-\-\>)'
			.'|(\<\!\[CDATA\[|\]\]\>)'
			.'|\\\\?'
			.( $allow_spaces ? '|\s' : '' )
		.')*';

		$pattern_start = '/';
		if ( $prefix != '' ) {
			$pattern_start .= '(' . $prefix . '\s*' . $pattern_sep . ')';
		}

		$pattern_end = '/i';
		if ( $suffix != '' ) {
			if ( $suffix == '=' || $suffix == ':' ) {
				$replacement_end = $suffix;
				$pattern_end = '(' . $pattern_sep . '\s*' . $suffix . ')' . $pattern_end;
			} else {
				$replacement_end = '';
				$pattern_end = $suffix . $pattern_end;
			}
		} else {
			$replacement_end = '';
		}

		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = $pattern_start;
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= $pattern_sep;
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= $pattern_end;
			$replacement = ( $prefix != '' ) ? '\\1' : '';
			// add in <> to nerf the tag
			$replacement .= substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); 
			$patterns[] = $pattern;
			$replacements[] = $replacement.$replacement_end;
		}
		// filter out the hex tags
		$val = preg_replace($patterns, $replacements, $val);
		if ($val_before == $val) {
			// no replacements were made, so exit the loop
			$found = false;
		}

		return $found;
	}
}

?>
