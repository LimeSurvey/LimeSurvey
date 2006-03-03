/* ccSiteStyle Dynamic style sheet
*  Copyright (c) 2001,2002 Chip Chapin <cchapin@chipchapin.com>
*  Visit the ccSiteStyle home page
*    -- http://www.chipchapin.com/WebTools/OtherTools/ccSiteStyle/
*/
<?php
// Dynamically generate style sheet appropriate to user agent. 
// Revision History
// 2002-11-08 cchapin  Initial release (v1.0)
// 2002-11-09 cchapin  Some additions to support ClassBase.
  
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

  
  //
  // Client browser detection
  //
  if (isset($GLOBALS['browser'])) $browser = $GLOBALS['browser'];
  else {
    // Check local directory first, then check system directory.
    // This facilitates local mods and testing.
    if (file_exists( 'ccBrowserInfo.php' ))
      include_once( 'ccBrowserInfo.php' );
    else include_once( $_SERVER['DOCUMENT_ROOT'] . '/bin/ccBrowserInfo.php' );

    $browser = new ccBrowserInfo();
  }
  echo '/* Dynamically generated based on client browser info:'."\n";
  $browser->showMe( false );
  echo "*/\n";
  
  // 
  // Check for Optional Indented Body Style
  //
  if (!isset($GLOBALS['styleBodyIndent'])) $styleBodyIndent = false;
  else $styleBodyIndent = $GLOBALS['styleBodyIndent'];
  if ($styleBodyIndent) echo '/* Indented Body Style */'."\n";
  else echo '/* Non-indented Body Style */'."\n";
  
  //
  // Set font sizes (in pixels)
  //   (Default sizes are for IE5 on Windows)
  //
  $fsize = array(
    'h1' => 28,   // Arial.  CW uses 25px, some use 22px
    'h2' => 19,   // Arial.  large, 118%, 14pt
    'h3' => 16,   // Arial.  medium, 90%, 9.5pt
    'h4' => 13,   // Verdana. small, 80%, 9pt
    'h5' => 12,   // Verdana.
    'tt' => 12,   // small, 10pt
    'largetext' => 14,
    'p'  => 13,
    'smalltext' => 11,    // Verdana, CW uses 10px
    'smallitalic' => 11,  // Verdana, CW uses 10px
    'smallertext' => 10,  // Use same font as smalltext
    'smallerertext' => 9,  // Use same font as smalltext
    'xsmalltext' => 10,   // Use smaller font than smalltext (Arial, not Verdana)
    'xxsmalltext' => 9 ); // Uses Arial, not Verdana
  
  //
  // Set standard font family lists
  //
  $fontlistBody   = 'Verdana, Geneva, Arial, Helvetica, sans-serif';
  $fontlistBodySmall = $fontlistBody;
  $fontlistBodySmaller = $fontlistBody;
  $fontlistBodyXSmall = 'Arial, Helvetica, sans-serif';
  $fontlistBodyXXSmall = 'Tahoma, Arial, Helvetica, sans-serif';
  $fontlistHeader = 'Arial, Helvetica, sans-serif';
  $fontlistSerif  = '"Times New Roman", Times, serif';
  $fontlistFixed  = '"Courier New", Courier, monospace';

  if ($browser->is_winNN()) {
    echo '/* Font sizes adjusted for Windows Netscape Navigator 4 */' . "\n";
    $fsize['largetext']++;
    $fsize['p']++;
    $fsize['smalltext']++;
    $fsize['smallitalic']++;
    $fsize['smallertext']++;
    $fsize['smallerertext']++;
    $fsize['xsmalltext']++;
    $fsize['xxsmalltext']++;
    // I simply cannot get Win NN 4 to render Verdana at the size I want.
    $fontlistBodySmall = 'Arial, Helvetica, sans-serif';  
  }
  else if ($browser->is_moz5up()) {
    echo '/* Font sizes adjusted for Mozilla 5Up */' . "\n";
    $fsize['h1']++;
    $fsize['h2']++;
    $fsize['h4']--;
    $fsize['h5']--;
    $fsize['p']--;
    $fsize['tt'] -= 2;
    $fsize['smalltext']--;	
    $fsize['smallitalic']--; // added 2002-11-02
    $fsize['smallertext']--;	
    $fsize['smallerertext']--;	
    $fsize['xsmalltext']--;	
    $fsize['xxsmalltext']--;	
  }
?>

  A:link    { text-decoration:none; color:#231E88} /* was #505080 */
  A:visited { text-decoration:none; color:#231E88 }
  A:active  { text-decoration:none; color:#881E23 }
  A:hover   { text-decoration:underline; color:#FF0000 }
  
  body {
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['p'] ?>px;
    font-size-adjust: 0.58; /* Verdana */
    color: black;
    margin: 0px;
    <?php 
    if (isset($GLOBALS['styleBodyBGcolor'])) echo 'background-color: '. $GLOBALS['styleBodyBGcolor'] . ';'."\n";
    else echo 'background-color: white;'."\n";
    if (isset($GLOBALS['styleBodyBGImage'])) echo 'background-image: ' . $GLOBALS['styleBodyBGImage'] . ';'."\n";
    if ($styleBodyIndent) echo 'margin-right: 10%;'."\n";
    ?>
  }
  h1 {
    margin-top:0px;
    padding-top:0px;
    font-family: <?php echo $fontlistHeader ?>;
    font-size: <?php echo $fsize['h1'] ?>px;
    font-size-adjust: 0.5; /* A guess for Arial */
    text-align: left; /* center; */
    font-weight: bold;
    font-style: normal;
    padding-bottom: 0px;
    margin-bottom:0px;
  }
  h2 {
    font-family: <?php echo $fontlistHeader ?>;
    font-size: <?php echo $fsize['h2'] ?>px;
    font-size-adjust: 0.5;
    font-style: italic;
    font-weight: bold;
    font-variant: normal;
    margin-bottom: 0px;
    <?php if ($styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
  }
  h3 {
    font-family: <?php echo $fontlistHeader ?>;
    font-size: <?php echo $fsize['h3'] ?>px;
    font-size-adjust: 0.58; /* Verdana */
    font-style: italic;
    font-weight: bold;
    font-variant: normal;
    <?php if ($styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
  }
  h4 {
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['h4'] ?>px;
    font-size-adjust: 0.58; /* Verdana */
    font-style: normal;
    font-weight: bold;
    font-variant: normal;
    <?php if ($styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
  }
  h5 {
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['h5'] ?>px;
    font-size-adjust: 0.58; /* Verdana */
    font-style: normal;
    font-weight: bold;
    font-variant: normal;
    <?php if ($styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
  }
  h6 {
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['smalltext'] ?>px;
    font-size-adjust: 0.58; /* Verdana */
    font-style: normal;
    font-weight: bold;
    font-variant: normal;
    <?php if ($styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
  }
  
  p {
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['p'] ?>px;
    font-size-adjust: 0.58; /* Verdana */
    font-style: normal;
    <?php if ($styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
  }
  dl, dt, dd, td, th, form, input, textarea, select  {
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['p'] ?>px;
    font-size-adjust: 0.58; /* Verdana */
    font-style: normal;
  }
  kbd {
    /* font-size: 110%; */
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['p'] ?>px;
    font-weight: bold;
  }
  tt {
    font-family : <?php echo $fontlistFixed ?>;
    font-size : <?php echo $fsize['tt'] ?>px;
  }
  code {
    font-family : <?php echo $fontlistFixed ?>;
    font-size : <?php echo $fsize['tt'] ?>px;
  }
  samp {
    font-family : <?php echo $fontlistFixed ?>;
    font-size : <?php echo $fsize['tt'] ?>px;
    font-style: italic;
  }
  pre {
    font-family : <?php echo $fontlistFixed ?>;
    font-size : <?php echo $fsize['tt'] ?>px;
  }
  blockquote {
    font-family : <?php echo $fontlistSerif ?>;
    font-size-adjust: 0.46; /* Times New Roman */
  }
  .blockquote {
    font-family : <?php echo $fontlistSerif ?>;
    font-size-adjust: 0.46; /* Times New Roman */
  }
  dl {
    <?php 
    if ($styleBodyIndent) echo 'margin-left: 6%;'."\n";
    else echo 'margin-left: 2%;'."\n";
     ?>
  }
  dd { 
    <?php
    if ($browser->is_moz4dn()) echo 'margin-bottom: 0px; /* NN */' . "\n";
    else echo 'margin-bottom: 0.5em;'."\n";
    ?> 
  }
  ol { /* list text should match p text */
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['p'] ?>px; 
    font-size-adjust: 0.58; /* Verdana */
    font-style: normal;
    list-style-type: decimal; /* upper-roman; */
    list-style-position: outside;
    margin-top: 0em;  /* copied from webtoolsstyle */
    <?php if (!$browser->is_ie() && $styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
  }
  ul { /* list text should match p text */
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['p'] ?>px; 
    font-size-adjust: 0.58; /* Verdana */
    font-style: normal;
    margin-top: 0em; /* copied from webtoolsstyle */
    list-style-type: disc;
    list-style-position: outside;
    <?php if (!$browser->is_ie() && $styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
  }
  li {
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['p'] ?>px; 
    font-size-adjust: 0.58; /* Verdana */
    font-style: normal;
    <?php if ($browser->is_ie() && $styleBodyIndent) echo 'margin-left: 2%;'."\n" ?>
    <?php
    if ($browser->is_moz4dn()) echo 'margin-bottom: 0px; /* NN */' . "\n";
    else echo 'margin-bottom: 0.5em;'."\n";
    ?> 
  }

  hr { 
    <?php 
      if ($styleBodyIndent) echo 'margin-left: 4%;'."\n";
      else echo 'margin-left: -2%;'."\n";
     ?>
  }
  
  /* 
   * Standard text classes for general use 
   */
  .normaltext {
    /* Should match the standard paragraph font */
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['p'] ?>px; /* x-small, 80% */
    font-size-adjust: 0.58; /* Verdana */
  }
  .smalltext {
    /* One size smaller than standard paragraph font */
    font-family:  <?php echo $fontlistBodySmall ?>;
    font-size: <?php echo $fsize['smalltext'] ?>px; /* xx-small, 70% */
    font-size-adjust: 0.58; /* Verdana */
  }
  .smalltextBold { /* Bold version of .smalltext */
    font-family:  <?php echo $fontlistBodySmall ?>;
    font-weight: bold;
    font-size: <?php echo $fsize['smalltext'] ?>px; /* xx-small, 70% */
    font-size-adjust: 0.58; /* Verdana */
  }
  .smallitalic {
    font-family:  <?php echo $fontlistBodySmall ?>;
    font-style: italic;
    font-size: <?php echo $fsize['smallitalic'] ?>px;
  }
  .smallertext {
    font-family: <?php echo $fontlistBodySmaller ?>;
    font-size: <?php echo $fsize['smallertext'] ?>px; /* xx-small, 63%; */
    font-size-adjust: 0.58; /* Verdana */
  }
  .smallerertext {
    font-family: <?php echo $fontlistBodySmaller ?>;
    font-size: <?php echo $fsize['smallerertext'] ?>px; /* xx-small, 63%; */
    font-size-adjust: 0.58; /* Verdana */
  }
  .xsmalltext {
    font-family:  <?php echo $fontlistBodyXSmall ?>;
    font-style: normal;
    font-size: <?php echo $fsize['xsmalltext'] ?>px; /* xx-small, 55%, 63%; for Arial */
    font-size-adjust: 0.58;
  }
  .xxsmalltext {
    font-family:  <?php echo $fontlistBodyXXSmall ?>;
    font-style: normal;
    font-size: <?php echo $fsize['xxsmalltext'] ?>px; /* xx-small, 55%, 63%; for Arial */
    font-size-adjust: 0.58;
  }
  .largetext {
    font-family: <?php echo $fontlistBody ?>;
    font-size: <?php echo $fsize['largetext'] ?>px; /* 100%; */
    font-size-adjust: 0.58;
  }

  /* 
   * Specialized text classes 
   */
  .textbox { /* td class for use as sidebar with highlighted background */
    background-color: #F0F066;
    background-image: none;
    color: black;
    border: none;
    padding: 2px 2px 2px 6px;
    font-family: <?php echo $fontlistBody ?>;
    font-style: normal;
    font-size: <?php echo $fsize['p'] ?>px; /* xx-small, 63%; */
    font-size-adjust: 0.58; /* Verdana */
  }
  .smalltextbox { /* td class for use as sidebar with highlighted background */
    background-color: #F0F066;
    background-image: none;
    color: black;
    border: none;
    padding: 2px 2px 2px 6px;
    font-family: <?php echo $fontlistBody ?>;
    font-style: normal;
    font-size: <?php echo $fsize['smalltext'] ?>px; /* xx-small, 63%; */
    font-size-adjust: 0.58; /* Verdana */
  }
  .counter { /* Use for hit counter -- same as smallitalic */
    font-family:  <?php echo $fontlistBodySmall ?>;
    font-size: <?php echo $fsize['smallitalic'] ?>px;
    font-style: italic;
  }
  .date { /* Use for file mod date in footer -- same as smallitalic */
    font-family:  <?php echo $fontlistBodySmall ?>;
    font-size: <?php echo $fsize['smallitalic'] ?>px;
    font-style: italic;
    text-align: right;
  }
  .footer { /* Use for misc. text in footer -- same as smalltext */
    font-family:  <?php echo $fontlistBodySmall ?>;
    font-size: <?php echo $fsize['smalltext'] ?>px;
    font-style: normal;
    margin-bottom: 0px;
  }
  .highlight {
    color: #FFFF00;
    background-color: #0000CC;
  }
  
  .fixedwidth { width: 640px }
  .outdent { margin-left: 3% }
  .indentsection { margin-left: 2em }

/*******************************************************************
*  HTML_TreeMenuXL style entries
*  The following entries are used by HTML_TreeMenuXL
*  See http://www.chipchapin.com/WebTools/MenuTools/HTML_TreeMenuXL/
********************************************************************/
.tmenu0text { /* Normal paragraph font */
  font-family: <?php echo $fontlistBody ?>;
  font-size: <?php echo $fsize['p'] ?>px; /* x-small, 80% */
  font-weight: bold;
}
.tmenu1text { /* smalltext */
  font-family: <?php echo $fontlistBodySmall ?>;
  font-size: <?php echo $fsize['smalltext'] ?>px; /* xx-small, 70% */
}
.tmenu2text { /* smallitalic */
  font-family:  <?php echo $fontlistBodySmall ?>;
  font-size: <?php echo $fsize['smallitalic'] ?>px;
  font-style: italic;
}
.tmenu3text { /* xsmalltext */
    font-family:  <?php echo $fontlistBodyXSmall ?>;
    font-size: <?php echo $fsize['xsmalltext'] ?>px; /* xx-small, 55%, 63%; for Arial */
    font-style: normal;
}

/* Since all menu items are links, the following can be equally important 
 * to your menu appearance.  
 * The main thing you may want to change are the A:link and A:visited colors.
 */
*.tmenu0text A:link,*.tmenu1text A:link,*.tmenu2text A:link,*.tmenu3text A:link 
  { text-decoration:none; color:#505080 }
*.tmenu0text A:visited,*.tmenu1text A:visited,*.tmenu2text A:visited,*.tmenu3text A:visited 
  { text-decoration:none; color:#505080 }
*.tmenu0text A:active,*.tmenu1text A:active,*.tmenu2text A:active,*.tmenu3text A:active 
  { text-decoration:none; color:#805050 }
*.tmenu0text A:hover,*.tmenu1text A:hover,*.tmenu2text A:hover,*.tmenu3text A:hover 
  { text-decoration:underline; color:#FF0000 }

/* .tmlistbox controls the appearance of Listbox menus */
.tmlistbox {
  font-family: <?php echo $fontlistBodySmall ?>;
  font-size: <?php echo $fsize['smalltext'] ?>px;  /* match 'smalltext' value */
  font-size-adjust: 0.58; /* Verdana */
  margin-bottom: 0px;
}

/* .tmenuSelected is used with linkSelectKey to highlight selected items */
.tmenuSelected { background-color: yellow; }
*.tmenuSelected A:link    { text-decoration:none; color:#2020ff }
*.tmenuSelected A:visited { text-decoration:none; color:#2020ff }
*.tmenuSelected A:active  { text-decoration:none; color:#ff2020 }
*.tmenuSelected A:hover   { text-decoration:underline; color:#FF0000 }

/* -- End of ccSiteStyle Dynamic CSS -- */
