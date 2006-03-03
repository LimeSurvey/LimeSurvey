<?php
  // ccBrowserInfo.php -- A PHP class for HTTP client user agent detection.
  // by Chip Chapin <cchapin@chipchapin.com> 
  // Visit my home page at http://www.chipchapin.com
  // Visit the ccBrowserInfo home page:
  //     http://www.chipchapin.com/WebTools/OtherTools/ccBrowserInfo/
  
  // ccBrowserInfo.php was inspired by browser.php in SourceForge 2.5.
  // Other important influences:
  //   sniffer.js from Netscape and 
  //   phpSniff (http://phpsniff.sourceforge.net/), which offers more
  //   functions and greater control, but is too elaborate for my taste.

  // ccBrowserInfo does NOT try to be rigorous.  Our primary goal is to distinguish
  // between older NN and IE, and between Windows and Mac (and Unix).
  // A useful list of older browser strings is here: 
  //   http://www.browserlist.browser.org/browser_mappings_list_big.html
  // See also the built-in pull-down list in the phpSniff example: 
  //   http://phpsniff.sourceforge.net/
  
  // Revision History  
  // 2002-01-01  cchapin  Creation
  // 2002-11-02  cchapin  Initial Public release (v1.0)
  
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002 Chip Chapin <cchapin@chipchapin.com>               |
// |                    http://www.chipchapin.com                          |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.| 
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Chip Chapin <cchapin@chipchapin.com>                          |
// +-----------------------------------------------------------------------+

class ccBrowserInfo
{
  var $agent='unknown';
  var $ver=0;
  var $majorver=0;
  var $minorver=0;
  var $platform='unknown';
  var $debug=false;

  function get_agent()    { return $this->agent; }
  function get_version()  { return $this->ver; }	
  function get_platform() { return $this->platform; }
  function is_mac()       { return $this->platform == 'Mac'; }
  // function is_windows()   { return $this->platform == 'Win'; }
  function is_win()       { return $this->platform == 'Win'; }
  function is_ie()        { return $this->agent == 'IE'; }
  function is_ie3()       { return $this->is_ie() && $this->majorver < 4; }
  function is_ie4()       { return $this->is_ie() && $this->majorver == 4; }
  function is_ie5up()     { return $this->is_ie() && $this->majorver >= 5; }
  // function is_netscape()  { return $this->agent == 'MOZILLA'; }
  function is_moz()       { return $this->agent == 'MOZILLA'; }
  // function is_netscape4() { return $this->agent == 'MOZILLA' && $this->majorver <= 4; }
  function is_moz4dn()    { return $this->agent == 'MOZILLA' && $this->majorver <= 4; }
  function is_moz5()      { return $this->agent == 'MOZILLA' && $this->majorver == 5; }
  function is_moz5up()    { return $this->agent == 'MOZILLA' && $this->majorver >= 5; }
  // function is_winNN()     { return ($this->is_windows() && $this->is_netscape());}
  function is_winNN()     { return ($this->is_win() && $this->is_moz4dn());}
  function is_macNN()     { return ($this->is_mac() && $this->is_moz4dn());}
	
  // This item relates to browser functionality, not identification.  
  // It really belongs elsewhere.
  function transparentFlashOK() {
    return $this->is_ie5up() && $this->is_windows();
  }

  // Constructor
  // Determine client browser type, version and platform using
  // heuristic examination of user agent string.
  // @param $ua allows override of user agent string for testing.
  function ccBrowserInfo( $ua=0 )
  {
    if (empty($ua)) $ua = $_SERVER['HTTP_USER_AGENT'];
    $useragent = strtolower($ua);

    // Determine browser and version
    // The order in which we test the agents patterns is important
    // Intentionally ignore Konquerer.  It should show up as Mozilla.
    // post-Netscape Mozilla versions using Gecko show up as Mozilla 5.0
    if (preg_match( '/(opera |opera\/)([0-9]*).([0-9]{1,2})/', $useragent, $matches)) ;
    elseif (preg_match( '/(msie )([0-9]*).([0-9]{1,2})/', $useragent, $matches)) ;
    elseif (preg_match( '/(mozilla\/)([0-9]*).([0-9]{1,2})/', $useragent, $matches)) ;
    else {
      $matches[1] = 'unknown'; $matches[2]=0; $matches[3]=0;
    }
    switch ($matches[1]) {
      case 'opera/':
      case 'opera ': $this->agent='OPERA'; break;
      case 'msie ': $this->agent='IE'; break;
      case 'mozilla/': $this->agent='MOZILLA'; break;
      case 'unknown': $this->agent='OTHER'; break;
      default: $this->agent='Oops!';
    }			
    $this->majorver=$matches[2];
    $this->minorver=$matches[3];
    $this->ver=$matches[2].'.'.$matches[3];
    
    // Determine platform
    // This is very incomplete for platforms other than Win/Mac
    if (preg_match( '/(win|mac|linux|unix)/', $useragent, $matches)) ;
    else $matches[1] = 'unknown';
    switch ($matches[1]) {
      case 'win': $this->platform='Win'; break;
      case 'mac': $this->platform='Mac'; break;
      case 'linux': $this->platform='Linux'; break;
      case 'unix': $this->platform='Unix'; break;
      case 'unknown': $this->platform='Other'; break;
      default: $this->platform='Oops!';
    }
    
    if ($this->debug) $this-showMe();
  } // ccBrowserInfo constructor


  function showMe( $usehtml=true )
  {
    $newline = $usehtml ? "<br>\n" : "\n";
    echo "\nAgent String: " . $_SERVER['HTTP_USER_AGENT'] . $newline;
    echo "is Windows: " . ($this->is_win()?'true':'false') . $newline;
    echo "is Mac: " . ($this->is_mac()?'true':'false') . $newline;
    echo "is IE: " . ($this->is_ie()?'true':'false') . $newline;
    echo "is IE5up: " . ($this->is_ie5up()?'true':'false') . $newline;
    echo "is NN: " . ($this->is_moz4dn()?'true':'false') . $newline;
    echo "is Moz5up: " . ($this->is_moz5up()?'true':'false') . $newline;
    echo "Platform: " . $this->get_platform() . $newline;
    echo "Version: " . $this->get_version() . $newline;
    echo "Agent: " . $this->get_agent() . $newline;
  } // showMe
  
} // class ccBrowserInfo

?>
