<?php
// cc 2002-12-02 Restore lost bug fix for usePersistence.
// cc 2002-11-14 Additional tweaks.
// cc 2002-11-12 various modifications for 1.1.0/XL2.0.  See notes inline.

// +-----------------------------------------------------------------------+
// | Copyright (c) 2002, Richard Heyes, Harald Radi                        |
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
// | Author: Richard Heyes <richard@phpguru.org>                           |
// |         Harald Radi <harald.radi@nme.at>                              |
// +-----------------------------------------------------------------------+
//
// $Id$

/**
* HTML_TreeMenu Class
*
* A simple couple of PHP classes and some not so simple
* Jabbascript which produces a tree menu. In IE this menu
* is dynamic, with branches being collapsable. In IE5+ the
* status of the collapsed/open branches persists across page
* refreshes.In any other browser the tree is static. Code is
* based on work of Harald Radi.
*
* Usage.
*
* After installing the package, copy the example php script to
* your servers document root. Also place the TreeMenu.js and the
* images folder in the same place. Running the script should
* then produce the tree.
*
* Thanks go to Chip Chapin (http://www.chipchapin.com) for many
* excellent ideas and improvements.
*
* @author  Richard Heyes <richard@php.net>
* @author  Harald Radi <harald.radi@nme.at>
* @access  public
* @package HTML_TreeMenu
*/

class HTML_TreeMenu
{
    /**
    * Indexed array of subnodes
    * @var array
    */
    var $items;

    /**
    * Path to the images
    * @var string
    */
    var $images;
    
    /**
    * Target for the links generated
    * @var string
    */
    var $linkTarget;
    
    /**
    * Whether to use clientside persistence or not
    * @var bool
    */
		// cc 2002-12-02 Fix typo, rename from $userPersistence
    var $usePersistence;
    
    /**
    * The default CSS class for the nodes
    */
    var $defaultClass;

    /**
    * Constructor
    *
    * @access public
    * @param  array $options An array of the various options you can set for the
    *                        treemenu. These are:
    *                         o images         The path to the images folder. Defaults to "images"
    *                         o linkTarget     The target for the link. Defaults to "_self"
    *                         o usePersistence Whether to use clientside persistence. This persistence
    *                                          is achieved using cookies. Default is true.
    *                         o defaultClass   The default CSS class to apply to a node. Default is none.
    */
    function HTML_TreeMenu($options = array())
    {
        $this->images         = 'images';
        $this->linkTarget     = '_self';
        $this->usePersistence = true;
        $this->defaultClass   = '';

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
    * Allows setting of various parameters after the initial
    * constructor call. Possible options you can set are:
    *  o images          -  The path to the images to be used
    *  o linkTarget      -  The default target for links
    *  o usePersistence  -  Whether to use client side persistence or not
    *
    * @param  string $option Option to set
    * @param  string $value  Value to set the option to
    * @access public
    */
    function setOption($option, $value)
    {
        $this->$option = $value;
    }

    /**
    * This function adds an item to the the tree.
    *
    * @access public
    * @param  object $node The node to add. This object should be
    *                      a HTML_TreeNode object.
    * @return object       Returns a reference to the new node inside
    *                      the tree.
    */
    function &addItem(&$node)
    {
        $this->items[] = &$node;
        return $this->items[count($this->items) - 1];
    }
} // HTML_TreeMenu


/**
* HTML_TreeNode class
* 
* This class is supplementary to the above and provides a way to
* add nodes to the tree. A node can have other nodes added to it. 
*
* @author  Richard Heyes <richard@php.net>
* @author  Harald Radi <harald.radi@nme.at>
* @access  public
* @package HTML_TreeMenu
*/
class HTML_TreeNode
{
    /**
    * The text for this node.
    * @var string
    */
    var $text;

    /**
    * The link for this node.
    * @var string
    */
    var $link;

    /**
    * The icon for this node.
    * @var string
    */
    var $icon;
    
    /**
    * The css class for this node
    * @var string
    */
    var $cssClass;

    /**
    * Indexed array of subnodes
    * @var array
    */
    var $items;

    /**
    * Whether this node is expanded or not
    * @var bool
    */
    var $expanded;
    
    /**
    * Whether this node is dynamic or not
    * @var bool
    */
    var $isDynamic;
    
    /**
    * Should this node be made visible?
    * @var bool
    */
    var $ensureVisible;
    
    /**
    * The parent node. Null if top level
    * @var object
    */
    var $parent;

    /**
    * Constructor
    *
    * @access public
    * @param  array $options An array of options which you can pass to change
    *                        the way this node looks/acts. This can consist of:
    *                         o text          The title of the node, defaults to blank
    *                         o link          The link for the node, defaults to blank
    *                         o icon          The icon for the node, defaults to blank
    *                         o class         The CSS class for this node, defaults to blank
    *                         o expanded      The default expanded status of this node, defaults to false
    *                                         This doesn't affect non dynamic presentation types
    *                         o isDynamic     If this node is dynamic or not. Only affects
    *                                         certain presentation types.
    *                         o ensureVisible If true this node will be made visible despite the expanded
    *                                         settings, and client side persistence. Will not affect
    *                                         some presentation styles, such as Listbox. Default is false
    */
    function HTML_TreeNode($options = array())
    {
        $this->text          = '';
        $this->link          = '';
        $this->icon          = '';
        $this->cssClass      = '';
        $this->expanded      = false;
        $this->isDynamic     = true;
        $this->ensureVisible = false;

        $this->parent        = null;
        
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
    * Allows setting of various parameters after the initial
    * constructor call. Possible options you can set are:
    *  o text
    *  o link
    *  o icon
    *  o cssClass
    *  o expanded
    *  o isDynamic
    *  o ensureVisible
    * ie The same options as in the constructor
    *
    * @access public
    * @param  string $option Option to set
    * @param  string $value  Value to set the option to
    */
    function setOption($option, $value)
    {
        $this->$option = $value;
    }

    /**
    * Adds a new subnode to this node.
    *
    * @access public
    * @param  object $node The new node
    */
    function &addItem(&$node)
    {
        $node->parent  = &$this;
        $this->items[] = &$node;
        
        /**
        * If the subnode has ensureVisible set it needs
        * to be handled, and all parents set accordingly.
        */
        if ($node->ensureVisible) {
            $this->_ensureVisible();
        }

        return $this->items[count($this->items) - 1];
    }
    
    /**
    * Private function to handle ensureVisible stuff
    *
    * @access private
    */
    function _ensureVisible()
    {
        $this->ensureVisible = true;
        $this->expanded      = true;

        if (!is_null($this->parent)) {
            $this->parent->_ensureVisible();
        }
    }
}


/**
* HTML_TreeMenu_DHTML class
*
* This class is a presentation class for the tree structure
* created using the TreeMenu/TreeNode. It presents the 
* traditional tree, static for browsers that can't handle
* the DHTML.
*/
// cc 2002-11-12 Modified to inherit from base class.  Simplifies setting defaults.
class HTML_TreeMenu_DHTML extends HTML_TreeMenu_Presentation
{
    /**
    * Dynamic status of the treemenu. If true (default) this has no effect. If
    * false it will override all dynamic status vars and set the menu to be
    * fully expanded an non-dynamic.
    */
    var $isDynamic;

    /**
    * Constructor, takes the tree structure as
    * an argument and an array of options which
	* can consist of:
	*  o isDynamic  Defines menu wide dynamic status
	*
	* @param object $structure The menu structure
	* @param array  $options   Array of options
    */
    function HTML_TreeMenu_DHTML($structure, $options = array())
    {
        $this->defaultProperties['isDynamic'] = true;
        HTML_TreeMenu_Presentation::HTML_TreeMenu_Presentation($structure, $options);
    }

    /**
    * Returns the HTML for the menu. This method can be
    * used instead of printMenu() to use the menu system
    * with a template system.
    *
    * @access public
    * @return string The HTML for the menu
    */
		// cc 2002-11-14 Added brOK hook.
    function toHTML()
    {
        static $count = 0;
        $menuObj = 'objTreeMenu_' . ++$count;
	
        $html  = "\n";
        $html .= '<script language="javascript" type="text/javascript">' . "\n\t";
        $html .= sprintf('%s = new TreeMenu("%s", "%s", "%s", "%s", %s);',
                         $menuObj,
												 $this->images,               // cc 2002-11-12 was: $this->menu->images
                         $menuObj,
                         $this->linkTarget,                         // was: $this->menu->linkTarget
                         $this->defaultClass,                       // was: $this->menu->defaultClass
                         $this->usePersistence ? 'true' : 'false'); // was: $this->menu->usePersistence
 
        $html .= "\n";

        // cc 2002-11-14 Permit line wrapping in menu items.  Requires JavaScript support
        if (!empty($this->brOK)) $html .= "\t" . $menuObj . '.brOK = true;'."\n";
    
        /**
        * Loop through subnodes
        */
        if (isset($this->menu->items)) {
            for ($i=0; $i<count($this->menu->items); $i++) {
                $html .= $this->_nodeToHTML($this->menu->items[$i], $menuObj);
            }
        }

         $html .= sprintf("\n\t%s.drawMenu();", $menuObj);
        if ($this->menu->usePersistence && $this->isDynamic) {
            $html .= sprintf("\n\t%s.resetBranches();", $menuObj);
        }
        $html .= "\n</script>";

        return $html;
    }
    
    /**
    * Prints a node of the menu
    *
    * @access private
    */
		// cc 2002-11-11 Added $treelevel arg & cssClass setting
		// cc 2002-11-14 Restored addslashes() on node text sent to javascript.
    function _nodeToHTML($nodeObj, $prefix, $return = 'newNode', $treelevel = 0)
    {
        $expanded  = $this->isDynamic ? ($nodeObj->expanded  ? 'true' : 'false') : 'true';
        $isDynamic = $this->isDynamic ? ($nodeObj->isDynamic ? 'true' : 'false') : 'false';

        // cc 2002-11-12, this depends on operation of _checkproperties()
			  $cssClass = empty($nodeObj->cssClass) ? $this->defaultClass : $nodeObj->cssClass;					
  			if ($cssClass == 'auto') $cssClass = $this->autostyles[min($treelevel, count($this->autostyles)-1)];

        $html = sprintf("\t %s = %s.addItem(new TreeNode('%s', %s, %s, %s, %s, '%s'));\n",
                        $return,
                        $prefix,
												// cc 2002-11-01.  (restored 2002-11-14)
                        addslashes($nodeObj->text), 
                        //$nodeObj->text,
                        !empty($nodeObj->icon) ? "'" . $nodeObj->icon . "'" : 'null',
                        !empty($nodeObj->link) ? "'" . $nodeObj->link . "'" : 'null',
                        $expanded,
                        $isDynamic,
                        $cssClass); // cc 2002-11-12

        /**
        * Loop through subnodes
        */
        if (!empty($nodeObj->items)) {
            for ($i=0; $i<count($nodeObj->items); $i++) { // cc 2002-11-11 added $treelevel arg
                $html .= $this->_nodeToHTML($nodeObj->items[$i], $return, $return . '_' . ($i + 1), $treelevel+1);
            }
        }

        return $html;
    }
}


/**
* HTML_TreeMenu_Listbox class
* 
* This class presents the menu as a listbox
*/
class HTML_TreeMenu_Listbox extends HTML_TreeMenu_Presentation
{    
    /**
    * The default presentation properties list -
  	* By replacing the entire list, there is no need to subclass the constructor.
    * @var array
    */
    var $defaultProperties = array( 'promoText' => 'Select...',
		                                'indentChar' => '&nbsp;',
                                    'indentNum' => 2 );

    /**
    * The text that is displayed in the first option
    * @var string
    */
    var $promoText;
    
    /**
    * The character used for indentation
    * @var string
    */
    var $indentChar;
    
    /**
    * How many of the indent chars to use
    * per indentation level
    * @var integer
    */
    var $indentNum;

    /**
    * Returns the HTML generated
    */
    function toHTML()
    {
        static $count = 0;
        $nodeHTML = '';

        /**
        * Loop through subnodes
        */
        if (isset($this->menu->items)) {
            for ($i=0; $i<count($this->menu->items); $i++) {
                $nodeHTML .= $this->_nodeToHTML($this->menu->items[$i]);
            }
        }

        return sprintf('<form onsubmit="var link = this.%s.options[this.%s.selectedIndex].value; if (link) location.href = link; return false"><select name="%s"><option value="">%s</option>%s</select> <input type="submit" value="Go" /></form>',
                       'HTML_TreeMenu_Listbox_' . ++$count,
                       'HTML_TreeMenu_Listbox_' . $count,
                       'HTML_TreeMenu_Listbox_' . $count,
                       $this->promoText,
                       $nodeHTML);
    }
    
    /**
    * Returns HTML for a single node
    * 
    * @access private
    */
    function _nodeToHTML($node, $prefix = '')
    {
        $html = sprintf('<option value="%s">%s%s</option>', $node->link, $prefix, $node->text);
        
        /**
        * Loop through subnodes
        */
        if (isset($node->items)) {
            for ($i=0; $i<count($node->items); $i++) {
                $html .= $this->_nodeToHTML($node->items[$i], $prefix . str_repeat($this->indentChar, $this->indentNum));
            }
        }
        
        return $html;
    }
}


////////////////////////////////////////////////////////////////////////////////////
//  class HTML_TreeMenu_Presentation
//  Super class for presentation classes
//  cc 2002-11-12
////////////////////////////////////////////////////////////////////////////////////
class HTML_TreeMenu_Presentation
{
  /**
  * The menu structure
  * @var object
  */
  var $menu;

  /**
  * The default presentation properties list -
	* Putting it here rather than inside _checkProperties makes it easy to add to or override.
  * @var array
  */
  var $defaultProperties = array( 'images'=>'TMimages', // or 'images'
					 'defaultClass'=>'',
		       'autostyles'=>array('tmenu0text', 'tmenu1text', 'tmenu2text', 'tmenu3text'),
					 'linkSelectKey'=>null,
		       'lineImageWidth'=>20,
		       'lineImageHeight'=>20,
		       'iconImageWidth'=>20,
		       'iconImageHeight'=>20,
					 'linkTarget'=>'_self',
					 'usePersistence'=>true); // cc 2002-12-02 restored this.
					 

  /**
  * Constructor, takes the tree structure as
  * an argument and an array of options.
  *
  * @param object $structure The menu structure
  * @param array  $options   Array of options
  */
  function HTML_TreeMenu_Presentation($structure, $options = array())
  {
    $this->menu      = $structure;
    foreach ($options as $option => $value) {
      $this->$option = $value;
    }
    $this->_checkProperties();
  } // HTML_TreeMenu_Presentation constructor


  /**
  * printMenu -- Prints the HTML generated by toHTML()
  *
  * @access public
  */
  function printMenu()
  {
    echo $this->toHTML();
  } // printMenu

  /**
  * toHTML -- Returns the HTML for the menu. This method can be
  * used instead of printMenu() to use the menu system
  * with a template system.
  *
  * @access public
  * @return string The HTML for the menu
  */    
  function toHTML()
  {
	  return "toHTML\n"; // dummy
  } // toHTML
    
		
  // _checkProperties -- Check presentation object for required properties.
	// If presentation property has not been set, set it from menu object or default values.
  function _checkProperties()
  {
    foreach($this->defaultProperties as $prop => $pdefault) {
      if (!isset($this->$prop)) {
			  // Use Menu object property if set, otherwise use default
			  if (isset($this->menu->$prop)) $this->$prop = $this->menu->$prop;
				else $this->$prop = $pdefault;
			}
    }
  } // _checkProperties
	
} // class HTML_TreeMenu_Presentation
////////////////////////////////////////////////////////////////////////////////////


?>
