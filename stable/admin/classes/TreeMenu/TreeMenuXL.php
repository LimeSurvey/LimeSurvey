<?php
  // TreeMenuXL.php
  // Chip Chapin <cchapin@chipchapin.com>
  // Defines classes HTML_TreeMenuXL and HTML_TreeNodeXL
  // An extension of Richard Heyes' HTML_TreeMenu
	// 2002-12-02 cc Restore lost bug fix in static printNode
	// 2002-11-14 cc Additional tweaks.
  // 2002-11-12 cc Major update for release 1.1.0/XL2.0
  // 2002-11-10 cc Updated
  
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

  // Check local directory first, then check system directory.
  // This facilitates local mods and testing.
  $PHPLIBPATH=$homedir."/classes/";  // Where do you keep your PHP libraries?
  if (file_exists( $homedir.'/classes/TreeMenu/TreeMenu.php' ))
    include_once( $homedir.'/classes/TreeMenu/TreeMenu.php' );
  else include_once( $_SERVER['DOCUMENT_ROOT'] . $PHPLIBPATH . 'TreeMenu.php' );

  /* Not-quite Obsolete as of HTML_TreeMenu 1.1 */
  if (file_exists( $homedir.'/classes/TreeMenu/ccBrowserInfo.php' ))
    include_once( $homedir.'/classes/TreeMenu/ccBrowserInfo.php' );
  else include_once( $_SERVER['DOCUMENT_ROOT'] . $PHPLIBPATH . 'ccBrowserInfo.php' );
  
  // Browser detection determines whether we generate DHTML menus
  static $tmsDoesMenus = 'maybe';
	/**/
  

////////////////////////////////////////////////////////////////////////////////////
//  class HTML_TreeMenuXL extends HTML_TreeMenu
//  As of 1.1, the only remaining changes in this class are
//    1. Support setProperties( array ) and unsetProperties( array ) member functions
//       These can probably be removed now.
////////////////////////////////////////////////////////////////////////////////////
class HTML_TreeMenuXL extends HTML_TreeMenu
{
	
  // setProperties -- update the property list for this object.
  function setProperties( $pl=null )
  {
    if (empty($pl) || !is_array($pl)) return false;
    foreach($pl as $pname => $pval) {
      //$this->properties[$pname] = $pval;
      $this->$pname = $pval;
    }
    return true;
  } // setProperties
  

  // unsetProperties -- remove property list entries for this object.
  function unsetProperties( $pl=null )
  {
    if (empty($pl) || !is_array($pl)) return false;
    foreach($pl as $pname) {
      // unset($this->properties[$pname]);
      unset($this->$pname);
      // Hack
      if ($pname == 'expanded' || $pname == 'selected') {
        // Propagate removal to all child nodes.
        for ($i=0; $i<count($this->items); $i++) $this->items[$i]->unsetChildProperties( array($pname) );
      }
    }
    return true;
  } // unsetProperties


} // class HTML_TreeMenuXL
////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////
// class HTML_TreeNodeXL extends HTML_TreeNode
//   Provides the following extensions over TreeNode 1.1
//   1. Flexible interface (mix of positional and property list args)
//   2. Some additional property setting functions
////////////////////////////////////////////////////////////////////////////////////
class HTML_TreeNodeXL extends HTML_TreeNode
{

  // Constructor
  // Supports a flexible interface:
  //  HTML_TreeNodeXL( [$text [, $link [, $icon [, $expanded [, $isDynamic [, $cssClass]]]]]] [array] )
  function HTML_TreeNodeXL( )
  {
    HTML_TreeNode::HTML_TreeNode();
    $numargs = func_num_args();
    $numargs = min(6, $numargs);
    $arglist = array('text', 'link', 'icon', 'expanded', 'isDynamic', 'cssClass');
    for ($i=0; $i<$numargs; $i++) {
      $a =& func_get_arg($i);
      if (is_array( $a )) {
        // Array is always the last argument.  Ignore anything else.
        $this->setProperties( $a );
        break;
      }
      $this->$arglist[$i] = $a;
    }
    $this->_checkProperties();
  } // HTML_TreeNodeXL constructor
  
    
  // setProperties -- update the property list for this object.
  function setProperties( $pl=null )
  {
    if (empty($pl) || !is_array($pl)) return false;
    foreach ($pl as $pname => $pval) $this->$pname = $pval; 
    return true;
  } // setProperties


  // setChildProperties -- update the property list for this object and its children
  function setChildProperties( $pl=null )
  {
    if (!$this->setProperties( $pl )) return false;
    for ($i=0; $i<count($this->items); $i++) $this->items[$i]->setChildProperties( $pl );
    return true;
  } // setChildProperties


  // unsetChildProperties -- remove property list entries for this object and its children
	// 2002-11-12 Probably no longer necessary
  function unsetChildProperties( $pl=null )
  {
    if (empty($pl) || !is_array($pl)) return false;
    foreach($pl as $pname) {
      //unset($this->properties[$pname]);
      unset($this->$pname);
    }
    for ($i=0; $i<count($this->items); $i++) $this->items[$i]->unsetChildProperties( $pl );
    return true;
  } // unsetChildProperties


  ///////////////////////////////////
  // HTML_TreeNodeXL Helper Functions
  ///////////////////////////////////
  
  // Check object for required properties.  Set them to defaults if not present.
  function _checkProperties()
  {
    $pdefaults = array('text'=>'[item]',
                       'icon'=>null,
                       'link'=>'#',
                       'expanded'=>false,
                       'isDynamic'=>true );
    foreach($pdefaults as $pname => $pval) {
      if (!isset($this->$pname)) $this->$pname = $pval;
    }
    return true;
  } // _checkProperties


} // class HTML_TreeNodeXL
////////////////////////////////////////////////////////////////////////////////////



////////////////////////////////////////////////////////////////////////////////////
//  class HTML_TreeMenu_DHTMLXL extends HTML_TreeMenu_DHTML
//  Presentation class new for 1.1.  Most of the XL functionality no longer in the 
//  base classes is here.  Browser detection for static menus has been removed.
//    1. Alternate implementation of "expanded" (TODO: check this against 1.1 changes,
//       may no longer be necessary).
//    2. Implements auto-expansion and highlighting of selected nodes.
////////////////////////////////////////////////////////////////////////////////////
class HTML_TreeMenu_DHTMLXL extends HTML_TreeMenu_DHTML
{

  // Constructor
  function HTML_TreeMenu_DHTMLXL($structure, $options = array())
	{
	  $this->defaultProperties['selectedStyle'] = 'tmenuSelected';
		$this->defaultProperties['brOK'] = null;
		HTML_TreeMenu_DHTML::HTML_TreeMenu_DHTML($structure, $options);
	} // HTML_TreeMenu_DHTMLXL
			       

	// toHTML -- Returns HTML string for the menu
  function toHTML()
  {
	  // I'm Still having erratic problems with NN4
		// Hence this check.
    if ($GLOBALS['tmsDoesMenus'] == 'maybe') {
      $tmb = new ccBrowserInfo();
      $GLOBALS['tmsDoesMenus'] = ($tmb->is_ie5up() || $tmb->is_moz5up());
		}
		if (!$GLOBALS['tmsDoesMenus']) {
		  $this_sucks = &new HTML_TreeMenu_RigidXL( $this->menu, 
                     array('images'=>$this->images,
										       'defaultClass'=>$this->defaultClass,
		                       'autostyles'=>$this->autostyles,
					                 'linkSelectKey'=>$this->linkSelectKey,
		                       'lineImageWidth'=>$this->lineImageWidth,
		                       'lineImageHeight'=>$this->lineImageHeight,
                           'iconImageWidth'=>$this->iconImageWidth,
                           'iconImageHeight'=>$this->iconImageHeight,
                           'linkTarget'=>$this->linkTarget,
													 'selectedStyle'=>$this->selectedStyle,
													 'brOK'=>$this->brOK));
		  return $this_sucks->toHTML();
		}

    // Expand the branch(es), if any, that contain nodes with matching links.
		if (!empty($this->linkSelectKey)) {
		  $keys = $this->linkSelectKey;
		  if (!is_array($keys)) $keys = array($keys);
			foreach ($keys as $key) $this->_expandSelected( $this->menu, $key );
    }
		    
    // Is the entire menu "expanded"?  Then make it so...
    if (isset($this->expanded) && $this->expanded) {
      for ($i=0; $i<count($this->menu->items); $i++) 
        $this->menu->items[$i]->setChildProperties( array('expanded'=>true) );
    }
		
		return HTML_TreeMenu_DHTML::toHTML(); 
  } // toHTML

  /////////////////////////////////////////
  // HTML_TreeMenu_DHTMLXL Helper Functions
  /////////////////////////////////////////
  
  // _expandSelected -- Expand the branch(es), if any that contain nodes 
  // with matching links.
	// Note: when called the first time $node is the menu object
  function _expandSelected( &$node, $key )
  {
    if ($key == (!empty($node->link) ? $node->link : null)) {
      $node->_ensureVisible();
      $node->selected = true;
    }
    for ($i=0; $i<count($node->items); $i++) $this->_expandSelected( $node->items[$i], $key );
  } // _expandSelected


  // _nodeToHTML -- Generate JavaScript for this node.
  function _nodeToHTML($nodeObj, $prefix, $return = 'newNode', $treelevel = 0)
  {
    $html = HTML_TreeMenu_DHTML::_nodeToHTML($nodeObj, $prefix, $return, $treelevel);

    // Set javascript values for image sizes for this node
    $nodeprops = array('iconImageWidth', 'iconImageHeight', 'lineImageWidth', 'lineImageHeight');
    foreach ($nodeprops as $nprop) {
      // Image sizes: set from node or presentation object.
      if (isset($nodeObj->$nprop)) $$nprop = $nodeObj->$nprop;
      else $$nprop = $this->$nprop;
      $html .= "\t$return." . $nprop . '=' . $$nprop . ";\n";
    }
    
		// Node may be selected
    if (!empty($nodeObj->selected)) {
      $html .= "\t$return.selected = '" . $this->selectedStyle . "';\n";
    }
		
    return $html;
  } // _nodeToHTML
  
} // class HTML_TreeMenu_DHTMLXL
////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////
// class HTML_TreeMenu_RigidXL
//   Statically generate a "rigid" tree menu
////////////////////////////////////////////////////////////////////////////////////
class HTML_TreeMenu_RigidXL extends HTML_TreeMenu_Presentation
{
		
	// toHTML -- Returns HTML string for the menu
  function toHTML()
  {
    // Generate Static Menu
		$html = '';
    for ($i=0; $i < count($this->menu->items); $i++) 
      $html .= $this->_printStaticMenu($this->menu, $i);
		return $html;
  } // toHTML

	
	/////////////////////////////////////////////
	// Helper Functions for HTML_TreeMenu_RigidXL
	/////////////////////////////////////////////

  // _printStaticMenu -- Print current node and all subnodes
	//  $np: Node parent
	//  $ni: Index in parent of current node
	//  $treelevel: Level in tree of current node
	//  $prepend: string of HTML to prepend to current node
  function _printStaticMenu($np, $ni, $treelevel=0, $prepend='')
  {
    $n           = $np->items[$ni];
    $cntSubnodes = empty($n->items) ? 0 : count($n->items);
    $cntSiblings = empty($np->items) ? 0 : count($np->items);

    // Gif modifier
    if ($ni == 0 && $treelevel == 0)   $modifier = $cntSubnodes > 1 ? 'top' : 'single';
    elseif ($ni == ($cntSiblings - 1)) $modifier = 'bottom';
    else                               $modifier = '';  
    $html = $this->_printNode($n, $treelevel, $prepend, $modifier);
    
    // Print any subnodes
    for ($i=0; $i<$cntSubnodes; $i++) {
      // Determine what to prepend to child subnode entries.
      $newPrepend = $prepend;
      if (empty($this->images)) {
        // No images.  Use spaces instead
        $newPrepend .= '&nbsp;&nbsp;';
      }
      else {
        // Prepend images to each menu entry
        //if ($treelevel == 0 && $cntSiblings == 1) $nppimg = '';
        //^^^  This special case does not apply to static menus.
        //     Why?  Bwaaa Ha Ha Ha Haaaaaaa!!  Try it and see.  Happy Halloween...
        if ($ni < ($cntSiblings - 1))  $nppimg = 'line.gif';
        else                           $nppimg = 'linebottom.gif';
				
				$lineImageWidth  = empty($n->lineImageWidth) ? $this->lineImageWidth : $n->lineImageWidth;
				$lineImageHeight = empty($n->lineImageHeight) ? $this->lineImageHeight : $n->lineImageHeight;				
        $newPrepend .= sprintf('<img src="%s/%s" width="%d" height="%d" align="top">', 
	                       $this->images, $nppimg, $lineImageWidth, $lineImageHeight);
      }

      // Now print the menu entry for this node
      $html .= $this->_printStaticMenu($n, $i, $treelevel+1, $newPrepend);
    }
		return $html;
  } // _printStaticMenu


  // Return static HTML for a single menu node
  // This code is based on the JavaScript function drawMenu().
  function _printNode($n, $treelevel, $prepend='', $modifier='')
  {
    if (empty($this->images)) {
      // No images in output
      $iconimg = '';
      $imgTag  = '';
    }
    else {
      $lineImageWidth  = empty($n->lineImageWidth)  ? $this->lineImageWidth  : $n->lineImageWidth;
			$lineImageHeight = empty($n->lineImageHeight) ? $this->lineImageHeight : $n->lineImageHeight;				
			$iconImageWidth  = empty($n->iconImageWidth)  ? $this->iconImageWidth  : $n->iconImageWidth;
			$iconImageHeight = empty($n->iconImageHeight) ? $this->iconImageHeight : $n->iconImageHeight;				
				
      $gifname   = 'branch';
      $iconimg   = $n->icon ? sprintf('<img src="%s/%s" width="%d" height="%d" align="top">', 
                                       $this->images, $n->icon, $iconImageWidth, $iconImageHeight) : '';
      $imgTag    = sprintf('<img src="%s/%s%s.gif" width="%d" height="%d" align="top" border="0" />', 
                           $this->images, $gifname, $modifier, $lineImageWidth, $lineImageHeight);
    }
    $linkStart   = $n->link ? sprintf('<a href="%s" target="%s">', 
                                       $n->link, $this->linkTarget) : '';
    $linkEnd     = $n->link ? '</a>' : '';
    
    $selectedStart = '';
    $selectedEnd   = '';
    if (!empty($n->selected)) {
      $selectedStart = "<span class='" . $this->selectedStyle . "'>";
      $selectedEnd   = "</span>";
    }
		
    // cc 2002-11-12, this depends on operation of _checkproperties()
		$cssClass = empty($n->cssClass) ? $this->defaultClass : $n->cssClass;					
  	if ($cssClass == 'auto') $cssClass = $this->autostyles[min($treelevel, count($this->autostyles)-1)];
    $cssStart = empty($cssClass) ? '' : '<span class="' . $cssClass . '">';
    $cssEnd   = empty($cssClass) ? '' : '</span>';
    
    return '<nobr>'. $cssStart . $prepend . 
           ($treelevel == 0 && count($n->items) == 1 ? '' : $imgTag) . // cc 2002-12-02 may need fixing...
           $iconimg .
           $selectedStart . $linkStart . $n->text . $linkEnd . $selectedEnd .
           $cssEnd . "</nobr><br>\n";
  } // _printNode

} // HTML_TreeMenu_RigidXL
////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////
// class HTML_TreeMenu_ListboxXL extends HTML_TreeMenu_Presentation
//
//   This is NOT an extension of the base Listbox class, rather it's a hybrid
//   of my original implementation and Richard's version.  I've rewritten mine
//   to match his as closely as possible, so that the essential differences are apparent.
////////////////////////////////////////////////////////////////////////////////////
class HTML_TreeMenu_ListboxXL  extends HTML_TreeMenu_Presentation
{
  /**
  * The default presentation properties list -
	* By replacing the entire list, there is no need to subclass the constructor.
  * @var array
  */
  var $defaultProperties = array( //'promoText' => 'Select...',
                                  'indentChar' => '&nbsp;',
                                  'indentNum' => 2,
																	'cssClass' => 'tmlistbox',
																	'bulletStyles' => array('', '&#8226; ', '-- ', '&nbsp;- ', '&nbsp; '),
																	'useBullets' => false );

  // toHTML -- Generate HTML for a listbox from current menu object.
  // We print the entire <form><select>...</select></form>
	function toHTML()
	{
		$cssString   = empty($this->cssClass) ? '' : sprintf(' class="%s"', $this->cssClass);
		$promoString = empty($this->promoText) ? '' : sprintf('<option value="">%s</option>', $this->promoText);

    // Select the branch(es), if any that contain nodes with matching links.
		if (!empty($this->linkSelectKey)) {
		  $keys = $this->linkSelectKey;
		  if (!is_array($keys)) $keys = array($keys);
			foreach ($keys as $key) $this->_expandSelected( $this->menu, $key );
    }

    // Loop through child nodes and gather list of <option> elements.
		$nodeHTML = '';
    if (isset($this->menu->items)) {
      for ($i=0; $i<count($this->menu->items); $i++) {
        $nodeHTML .= $this->_nodeToHTML($this->menu->items[$i]);
      }
    }

    return sprintf('<form onsubmit="var link = this.sl.options[this.sl.selectedIndex].value; if (link) location.href = link; return false" style="margin-bottom:0px"%s><select name="sl"%s>%s%s</select> <input type="submit" value="Go"%s /></form>', 
		               $cssString, $cssString, $promoString, $nodeHTML, $cssString);
	} // toHTML
	
	
  // _nodeToHTML -- Helper routine for _printListbox
  // Print the <option> tags for the menu subtree rooted at $node
  // Note: $node could be either a Menu object or a Node object.
  function _nodeToHTML($node, $treelevel=0)
  {
    // Is this item selected?
    $selectString = empty($node->selected) ? '' : ' selected';

    // Leading for node string
		$prefix = str_repeat($this->indentChar, $this->indentNum * $treelevel);
		if (!empty($this->useBullets) && is_array($this->bulletStyles)) {
      $prefix .= $this->bulletStyles[min($treelevel, count($this->bulletStyles)-1)];
		}

    // Print node string and </option>
    $html = sprintf('<option value="%s"%s>%s%s</option>'."\n", $node->link, $selectString, $prefix, $node->text);
  
    // Proceed through child nodes
    for ($i=0; $i < count($node->items); $i++) {
      $html .= $this->_nodeToHTML($node->items[$i], $treelevel+1);
    }
		
		return $html;
  } // _nodeToHTML
  
	
  // _expandSelected -- Set the 'selected' property for nodes with matching links.
	// Note: when called the first time $node is the menu object
  function _expandSelected( &$node, $key )
  {
    if ($key == (!empty($node->link) ? $node->link : null)) {
      $node->selected = true;
    }
    for ($i=0; $i<count($node->items); $i++) $this->_expandSelected( $node->items[$i], $key );
  } // _expandSelected

} // class HTML_TreeMenu_ListboxXL
////////////////////////////////////////////////////////////////////////////////////


?>
