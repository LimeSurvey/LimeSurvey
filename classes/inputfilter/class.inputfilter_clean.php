<?php
/** @class: InputFilter (PHP4 & PHP5, without comments)
  * @project: PHP Input Filter
  * @date: 10-05-2005
  * @version: 1.2.2_php4/php5
  * @author: Daniel Morris
  * @contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
  * @copyright: Daniel Morris
  * @email: dan@rootcube.com
  * @license: GNU General Public License (GPL)
  */
class InputFilter {
	var $tagsArray;
	var $attrArray;
	var $tagsMethod;
	var $attrMethod;
	var $xssAuto;
	var $tagBlacklist = array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');
	var $attrBlacklist = array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');
	function inputFilter($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1) {		
		if (is_array($tagsArray)) {for ($i = 0; $i < count($tagsArray); $i++) $tagsArray[$i] = strtolower($tagsArray[$i]);}
		if (is_array($tagsArray)) {for ($i = 0; $i < count($attrArray); $i++) $attrArray[$i] = strtolower($attrArray[$i]);}
		$this->tagsArray = (array) $tagsArray;
		$this->attrArray = (array) $attrArray;
		$this->tagsMethod = $tagsMethod;
		$this->attrMethod = $attrMethod;
		$this->xssAuto = $xssAuto;
	}
	function process($source) {
		if (is_array($source)) {
			foreach($source as $key => $value)
				if (is_string($value)) $source[$key] = $this->remove($this->decode($value));
			return $source;
		} else if (is_string($source)) {
			return $this->remove($this->decode($source));
		} else return $source;	
	}
	function remove($source) {
		$loopCounter=0;
		while($source != $this->filterTags($source)) {
			$source = $this->filterTags($source);
			$loopCounter++;
		}
		return $this->RemoveXSS($source);
	}	
    
    function filterTags($source)
    {
        /*
         * In the beginning we don't really have a tag, so everything is
         * postTag
         */
        $preTag        = null;
        $postTag    = $source;

        /*
         * Is there a tag? If so it will certainly start with a '<'
         */
        $tagOpen_start    = strpos($source, '<');

        while ($tagOpen_start !== false)
        {

            /*
             * Get some information about the tag we are processing
             */
            $preTag           .= substr($postTag, 0, $tagOpen_start);
            $postTag        = substr($postTag, $tagOpen_start);
            $fromTagOpen    = substr($postTag, 1);
            $tagOpen_end    = strpos($fromTagOpen, '>');

            /*
             * Let's catch any non-terminated tags and skip over them
             */
            if ($tagOpen_end === false)
            {
                $postTag        = substr($postTag, $tagOpen_start +1);
                $tagOpen_start    = strpos($postTag, '<');
                continue;
            }

            /*
             * Do we have a nested tag?
             */
            $tagOpen_nested = strpos($fromTagOpen, '<');
            $tagOpen_nested_end    = strpos(substr($postTag, $tagOpen_end), '>');
            if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end))
            {
                $preTag           .= substr($postTag, 0, ($tagOpen_nested +1));
                $postTag        = substr($postTag, ($tagOpen_nested +1));
                $tagOpen_start    = strpos($postTag, '<');
                continue;
            }


            /*
             * Lets get some information about our tag and setup attribute pairs
             */
            $tagOpen_nested    = (strpos($fromTagOpen, '<') + $tagOpen_start +1);
            $currentTag        = substr($fromTagOpen, 0, $tagOpen_end);
            $tagLength        = strlen($currentTag);
            $tagLeft        = $currentTag;
            $attrSet        = array ();
            $currentSpace    = strpos($tagLeft, ' ');

            /*
             * Are we an open tag or a close tag?
             */
            if (substr($currentTag, 0, 1) == "/")
            {
                // Close Tag
                $isCloseTag        = true;
                list ($tagName)    = explode(' ', $currentTag);
                $tagName        = substr($tagName, 1);
            } else
            {
                // Open Tag
                $isCloseTag        = false;
                list ($tagName)    = explode(' ', $currentTag);
            }

            /*
             * Exclude all "non-regular" tagnames
             * OR no tagname
             * OR remove if xssauto is on and tag is blacklisted
             */
            if ((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $this->tagBlacklist)) && ($this->xssAuto)))
            {
                $postTag        = substr($postTag, ($tagLength +2));
                $tagOpen_start    = strpos($postTag, '<');
                // Strip tag
                continue;
            }

            /*
             * Time to grab any attributes from the tag... need this section in
             * case attributes have spaces in the values.
             */
            while ($currentSpace !== false)
            {
                $fromSpace        = substr($tagLeft, ($currentSpace +1));
                $nextSpace        = strpos($fromSpace, ' ');
                $openQuotes        = strpos($fromSpace, '"');
                $closeQuotes    = strpos(substr($fromSpace, ($openQuotes +1)), '"') + $openQuotes +1;

                /*
                 * Do we have an attribute to process? [check for equal sign]
                 */
                if (strpos($fromSpace, '=') !== false)
                {
                    /*
                     * If the attribute value is wrapped in quotes we need to
                     * grab the substring from the closing quote, otherwise grab
                     * till the next space
                     */
                    if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes +1)), '"') !== false))
                    {
                        $attr = substr($fromSpace, 0, ($closeQuotes +1));
                    } else
                    {
                        $attr = substr($fromSpace, 0, $nextSpace);
                    }
                } else
                {
                    /*
                     * No more equal signs so add any extra text in the tag into
                     * the attribute array [eg. checked]
                     */
                    $attr = substr($fromSpace, 0, $nextSpace);
                }

                // Last Attribute Pair
                if (!$attr)
                {
                    $attr = $fromSpace;
                }

                /*
                 * Add attribute pair to the attribute array
                 */
                $attrSet[] = $attr;

                /*
                 * Move search point and continue iteration
                 */
                $tagLeft        = substr($fromSpace, strlen($attr));
                $currentSpace    = strpos($tagLeft, ' ');
            }

            /*
             * Is our tag in the user input array?
             */
            $tagFound = in_array(strtolower($tagName), $this->tagsArray);

            /*
             * If the tag is allowed lets append it to the output string
             */
            if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod))
            {
                /*
                 * Reconstruct tag with allowed attributes
                 */
                if (!$isCloseTag)
                {
                    // Open or Single tag
                    $attrSet = $this->filterAttr($attrSet);
                    $preTag .= '<'.$tagName;
                    for ($i = 0; $i < count($attrSet); $i ++)
                    {
                        $preTag .= ' '.$attrSet[$i];
                    }

                    /*
                     * Reformat single tags to XHTML
                     */
                    if (strpos($fromTagOpen, "</".$tagName))
                    {
                        $preTag .= '>';
                    } else
                    {
                        $preTag .= ' />';
                    }
                } else
                {
                    // Closing Tag
                    $preTag .= '</'.$tagName.'>';
                }
            }

            /*
             * Find next tag's start and continue iteration
             */
            $postTag        = substr($postTag, ($tagLength +2));
            $tagOpen_start    = strpos($postTag, '<');
            //print "T: $preTag\n";
        }

        /*
         * Append any code after the end of tags and return
         */
        if ($postTag != '<')
        {
            $preTag .= $postTag;
        }
        return $preTag;
    }
    
	function filterAttr($attrSet) {	
		$newSet = array();
		for ($i = 0; $i <count($attrSet); $i++) {
			if (!$attrSet[$i]) continue;
            $attrSubSet=array();
            if (strpos($attrSet[$i],'=')===false)
            {
                $attrSubSet[] = $attrSet[$i];
            }
            else
            {
			    $attrSubSet[] = trim(substr($attrSet[$i],0,strpos($attrSet[$i],'=')));
                $attrSubSet[] = trim(substr($attrSet[$i],strpos($attrSet[$i],'=')+1));
            }
			list($attrSubSet[0]) = explode(' ', $attrSubSet[0]);
			if ((!eregi("^[a-z]*$",$attrSubSet[0])) || (($this->xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->attrBlacklist)) || (substr($attrSubSet[0], 0, 2) == 'on')))) 
				continue;
			if ($attrSubSet[1]) {
				$attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
				$attrSubSet[1] = preg_replace('/\s+/', '', $attrSubSet[1]);
				$attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);
				if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
					$attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
				$attrSubSet[1] = stripslashes($attrSubSet[1]);
                if (strtolower($attrSubSet[0])=='style')
                {
                    $attrSubSet[1]=$this->__remove_css_comments($attrSubSet[1]);
                }
                
			}
			if (	((strpos(strtolower($attrSubSet[1]), 'expression') !== false) &&	(strtolower($attrSubSet[0]) == 'style')) ||
					(strpos(strtolower($attrSubSet[1]), 'javascript:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'behaviour:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'vbscript:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'mocha:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'livescript:') !== false) 
			) continue;
			$attrFound = in_array(strtolower($attrSubSet[0]), $this->attrArray);
			if ((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod)) {
				if ($attrSubSet[1]) $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
				else if ($attrSubSet[1] == "0") $newSet[] = $attrSubSet[0] . '="0"';
				else $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[0] . '"';
			}	
		}
		return $newSet;
	}
	function decode($source) {
		$source = html_entity_decode_php4($source, ENT_QUOTES, "UTF-8");
		$source = preg_replace('/&#(\d+);/me',"chr(\\1)", $source);
		$source = preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)", $source);
		return $source;
	}
	function safeSQL($source, &$connection) {
		if (is_array($source)) {
			foreach($source as $key => $value)
				if (is_string($value)) $source[$key] = $this->quoteSmart($this->decode($value), $connection);
			return $source;
		} else if (is_string($source)) {
			if (is_string($source)) return $this->quoteSmart($this->decode($source), $connection);
		} else return $source;	
	}
	function quoteSmart($source, &$connection) {
		if (get_magic_quotes_gpc()) $source = stripslashes($source);
		$source = $this->escapeString($source, $connection);
		return $source;
	}
	function escapeString($string, &$connection) {
		if (version_compare(phpversion(),"4.3.0", "<")) mysql_escape_string($string);
		else mysql_real_escape_string($string);
		return $string;
	}
    
     function __remove_css_comments($code)  
     {  
         $mq = $sq = $mc = $sc = false;  
         $output = "";  
         for($i = 0; $i < strlen($code); $i++)  
         {  
             $l = $code{$i};  
             $n = $i+1;  
             if ($n<strlen($code))
             {
                          $ll = $code{$i}.$code{$n};  
             }
       
             switch($l)  
             {  
                 case "\n":  
                     $sc = false;  
                 break;  
                 case "/":  
                     if($code{$n} == "/")  
                     {  
                         if(!$sc && !$mc && !$sq && !$mq)  
                             $sc = true;  
                         $i++;  
                     }  
                     else if($code{$n} == "*")  
                     {  
                         if(!$sc && !$mc && !$sq && !$mq)  
                             $mc = true;  
                         $i++;  
                     }  
                     continue 2;  
                 break;  
                 case "'":  
                     if(!$sc && !$mc && !$mq)  
                         $sq = !$sq;  
                 break;  
                 case "\"":  
                     if(!$sc && !$mc && !$sq)  
                         $mq = !$mq;  
                 break;  
                 case "*":  
                     if($code{$n} == "/")  
                     {  
                         if(!$sc && !$sq && !$mq)  
                             $mc = false;  
                         $i++;  
                     }  
                     continue 2;  
                 break;  
             }  
             if(!$sc && !$mc)  
                 $output .= $l;  
         }  
         return $output;  
     }

    function RemoveXSS($val) {
       // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
       // this prevents some character re-spacing such as <java\0script>
       // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
       $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);
       
       // straight replacements, the user should never need these since they're normal characters
       // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
       $search = 'abcdefghijklmnopqrstuvwxyz';
       $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $search .= '1234567890!@#$%^&*()';
       $search .= '~`";:?+/={}[]-_|\'\\';
       for ($i = 0; $i < strlen($search); $i++) {
          // ;? matches the ;, which is optional
          // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
       
          // &#x0040 @ search for the hex values
          $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
          // &#00064 @ 0{0,7} matches '0' zero to seven times
          $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
       }
       
       // now the only remaining whitespace attacks are \t, \n, and \r
       $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
       $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
       $ra = array_merge($ra1, $ra2);
       
       $found = true; // keep replacing as long as the previous round replaced something
       while ($found == true) {
          $val_before = $val;
          for ($i = 0; $i < sizeof($ra); $i++) {
             $pattern = '/';
             for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                   $pattern .= '(';
                   $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                   $pattern .= '|';
                   $pattern .= '|(&#0{0,8}([9|10|13]);)';
                   $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
             }
             $pattern .= '/i';
             $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
             $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
             if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
             }
          }
       }
       return $val;
    }            
}
?>
