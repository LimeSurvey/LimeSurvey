/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * FCKEditingArea Class: renders an editable area.
 */

/**
 * @constructor
 * @param {String} targetElement The element that will hold the editing area. Any child element present in the target will be deleted.
 */
var FCKEditingArea = function( targetElement )
{
	this.TargetElement = targetElement ;
	this.Mode = FCK_EDITMODE_WYSIWYG ;

	if ( FCK.IECleanup )
		FCK.IECleanup.AddItem( this, FCKEditingArea_Cleanup ) ;
}


/**
 * @param {String} html The complete HTML for the page, including DOCTYPE and the <html> tag.
 */
FCKEditingArea.prototype.Start = function( html, secondCall )
{
	var eTargetElement	= this.TargetElement ;
	var oTargetDocument	= FCKTools.GetElementDocument( eTargetElement ) ;

	// Remove all child nodes from the target.
	while( eTargetElement.childNodes.length > 0 )
		eTargetElement.removeChild( eTargetElement.childNodes[0] ) ;

	if ( this.Mode == FCK_EDITMODE_WYSIWYG )
	{
		// Create the editing area IFRAME.
		var oIFrame = this.IFrame = oTargetDocument.createElement( 'iframe' ) ;
		oIFrame.src = 'javascript:void(0)' ;
		oIFrame.frameBorder = 0 ;
		oIFrame.width = oIFrame.height = '100%' ;

		// Append the new IFRAME to the target.
		eTargetElement.appendChild( oIFrame ) ;

		// IE has a bug with the <base> tag... it must have a </base> closer,
		// otherwise the all sucessive tags will be set as children nodes of the <base>.
		if ( FCKBrowserInfo.IsIE )
			html = html.replace( /(<base[^>]*?)\s*\/?>(?!\s*<\/base>)/gi, '$1></base>' ) ;
		else if ( !secondCall )
		{
			// If nothing in the body, place a BOGUS tag so the cursor will appear.
			if ( FCKBrowserInfo.IsGecko )
				html = html.replace( /(<body[^>]*>)\s*(<\/body>)/i, '$1' + GECKO_BOGUS + '$2' ) ;

			// Gecko moves some tags out of the body to the head, so we must use
			// innerHTML to set the body contents (SF BUG 1526154).

			// Extract the BODY contents from the html.
			var oMatch = html.match( FCKRegexLib.BodyContents ) ;

			if ( oMatch )
			{
				html =
					oMatch[1] +					// This is the HTML until the <body...> tag, inclusive.
					'&nbsp;' +
					oMatch[3] ;					// This is the HTML from the </body> tag, inclusive.

				this._BodyHTML = oMatch[2] ;	// This is the BODY tag contents.
			}
			else
				this._BodyHTML = html ;			// Invalid HTML input.
		}

		// Get the window and document objects used to interact with the newly created IFRAME.
		this.Window = oIFrame.contentWindow ;

		// IE: Avoid JavaScript errors thrown by the editing are source (like tags events).
		// TODO: This error handler is not being fired.
		// this.Window.onerror = function() { alert( 'Error!' ) ; return true ; }

		var oDoc = this.Document = this.Window.document ;

		oDoc.open() ;
		oDoc.write( html ) ;
		oDoc.close() ;

		// Firefox 1.0.x is buggy... ohh yes... so let's do it two times and it
		// will magicaly work.
		if ( FCKBrowserInfo.IsGecko10 && !secondCall )
		{
			this.Start( html, true ) ;
			return ;
		}

		this.Window._FCKEditingArea = this ;

		// FF 1.0.x is buggy... we must wait a lot to enable editing because
		// sometimes the content simply disappears, for example when pasting
		// "bla1!<img src='some_url'>!bla2" in the source and then switching
		// back to design.
		if ( FCKBrowserInfo.IsGecko10 )
			this.Window.setTimeout( FCKEditingArea_CompleteStart, 500 ) ;
		else
			FCKEditingArea_CompleteStart.call( this.Window ) ;
	}
	else
	{
		var eTextarea = this.Textarea = oTargetDocument.createElement( 'textarea' ) ;
		eTextarea.className = 'SourceField' ;
		eTextarea.dir = 'ltr' ;
		eTextarea.style.width = eTextarea.style.height = '100%' ;
		eTextarea.style.border = 'none' ;
		eTargetElement.appendChild( eTextarea ) ;

		eTextarea.value = html  ;

		// Fire the "OnLoad" event.
		FCKTools.RunFunction( this.OnLoad ) ;
	}
}

// "this" here is FCKEditingArea.Window
function FCKEditingArea_CompleteStart()
{
	// Of Firefox, the DOM takes a little to become available. So we must wait for it in a loop.
	if ( !this.document.body )
	{
		this.setTimeout( FCKEditingArea_CompleteStart, 50 ) ;
		return ;
	}

	var oEditorArea = this._FCKEditingArea ;
	oEditorArea.MakeEditable() ;

	// Fire the "OnLoad" event.
	FCKTools.RunFunction( oEditorArea.OnLoad ) ;
}

FCKEditingArea.prototype.MakeEditable = function()
{
	var oDoc = this.Document ;

	if ( FCKBrowserInfo.IsIE )
	{
		oDoc.body.contentEditable = true ;

		/* The following commands don't throw errors, but have no effect.
		oDoc.execCommand( 'AutoDetect', false, false ) ;
		oDoc.execCommand( 'KeepSelection', false, true ) ;
		*/
	}
	else
	{
		try
		{
			// Disable Firefox 2 Spell Checker.
			oDoc.body.spellcheck = ( this.FFSpellChecker !== false ) ;

			if ( this._BodyHTML )
			{
				oDoc.body.innerHTML = this._BodyHTML ;
				this._BodyHTML = null ;
			}

			oDoc.designMode = 'on' ;

			// Tell Gecko to use or not the <SPAN> tag for the bold, italic and underline.
			try
			{
				oDoc.execCommand( 'styleWithCSS', false, FCKConfig.GeckoUseSPAN ) ;
			}
			catch (e)
			{
				// As evidenced here, useCSS is deprecated in favor of styleWithCSS:
				// http://www.mozilla.org/editor/midas-spec.html
				oDoc.execCommand( 'useCSS', false, !FCKConfig.GeckoUseSPAN ) ;
			}

			// Analysing Firefox 1.5 source code, it seams that there is support for a
			// "insertBrOnReturn" command. Applying it gives no error, but it doesn't
			// gives the same behavior that you have with IE. It works only if you are
			// already inside a paragraph and it doesn't render correctly in the first enter.
			// oDoc.execCommand( 'insertBrOnReturn', false, false ) ;

			// Tell Gecko (Firefox 1.5+) to enable or not live resizing of objects (by Alfonso Martinez)
			oDoc.execCommand( 'enableObjectResizing', false, !FCKConfig.DisableObjectResizing ) ;

			// Disable the standard table editing features of Firefox.
			oDoc.execCommand( 'enableInlineTableEditing', false, !FCKConfig.DisableFFTableHandles ) ;
		}
		catch (e) {}
	}
}

FCKEditingArea.prototype.Focus = function()
{
	try
	{
		if ( this.Mode == FCK_EDITMODE_WYSIWYG )
		{
			// The following check is important to avoid IE entering in a focus loop. Ref:
			// http://sourceforge.net/tracker/index.php?func=detail&aid=1567060&group_id=75348&atid=543653
			if ( FCKBrowserInfo.IsIE && this.Document.hasFocus() )
				return ;

			if ( FCKBrowserInfo.IsSafari )
				this.IFrame.focus() ;
			else
			{
				this.Window.focus() ;
			}
		}
		else
		{
			var oDoc = FCKTools.GetElementDocument( this.Textarea ) ;
			if ( (!oDoc.hasFocus || oDoc.hasFocus() ) && oDoc.activeElement == this.Textarea )
				return ;

			this.Textarea.focus() ;
		}
	}
	catch(e) {}
}

function FCKEditingArea_Cleanup()
{
	this.TargetElement = null ;
	this.IFrame = null ;
	this.Document = null ;
	this.Textarea = null ;

	if ( this.Window )
	{
		this.Window._FCKEditingArea = null ;
		this.Window = null ;
	}
}
