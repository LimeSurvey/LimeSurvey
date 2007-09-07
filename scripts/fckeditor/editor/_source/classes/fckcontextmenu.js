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
 * FCKContextMenu Class: renders an control a context menu.
 */

var FCKContextMenu = function( parentWindow, langDir )
{
	this.CtrlDisable = false ;

	var oPanel = this._Panel = new FCKPanel( parentWindow ) ;
	oPanel.AppendStyleSheet( FCKConfig.SkinPath + 'fck_editor.css' ) ;
	oPanel.IsContextMenu = true ;

	// The FCKTools.DisableSelection doesn't seems to work to avoid dragging of the icons in Mozilla
	// so we stop the start of the dragging
	if ( FCKBrowserInfo.IsGecko )
		oPanel.Document.addEventListener( 'draggesture', function(e) {e.preventDefault(); return false;}, true ) ;

	var oMenuBlock = this._MenuBlock = new FCKMenuBlock() ;
	oMenuBlock.Panel = oPanel ;
	oMenuBlock.OnClick = FCKTools.CreateEventListener( FCKContextMenu_MenuBlock_OnClick, this ) ;

	this._Redraw = true ;
}


FCKContextMenu.prototype.SetMouseClickWindow = function( mouseClickWindow )
{
	if ( !FCKBrowserInfo.IsIE )
	{
		this._Document = mouseClickWindow.document ;
		this._Document.addEventListener( 'contextmenu', FCKContextMenu_Document_OnContextMenu, false ) ;
	}
}

FCKContextMenu.prototype.AddItem = function( name, label, iconPathOrStripInfoArrayOrIndex, isDisabled )
{
	var oItem = this._MenuBlock.AddItem( name, label, iconPathOrStripInfoArrayOrIndex, isDisabled) ;
	this._Redraw = true ;
	return oItem ;
}

FCKContextMenu.prototype.AddSeparator = function()
{
	this._MenuBlock.AddSeparator() ;
	this._Redraw = true ;
}

FCKContextMenu.prototype.RemoveAllItems = function()
{
	this._MenuBlock.RemoveAllItems() ;
	this._Redraw = true ;
}

FCKContextMenu.prototype.AttachToElement = function( element )
{
	if ( FCKBrowserInfo.IsIE )
		FCKTools.AddEventListenerEx( element, 'contextmenu', FCKContextMenu_AttachedElement_OnContextMenu, this ) ;
	else
		element._FCKContextMenu = this ;

//	element.onmouseup		= FCKContextMenu_AttachedElement_OnMouseUp ;
}

function FCKContextMenu_Document_OnContextMenu( e )
{
	var el = e.target ;

	while ( el )
	{
		if ( el._FCKContextMenu )
		{
			if ( el._FCKContextMenu.CtrlDisable && ( e.ctrlKey || e.metaKey ) )
				return true ;

			FCKTools.CancelEvent( e ) ;
			FCKContextMenu_AttachedElement_OnContextMenu( e, el._FCKContextMenu, el ) ;
		}
		el = el.parentNode ;
	}
}

function FCKContextMenu_AttachedElement_OnContextMenu( ev, fckContextMenu, el )
{
//	var iButton = e ? e.which - 1 : event.button ;

//	if ( iButton != 2 )
//		return ;

	if ( fckContextMenu.CtrlDisable && ( ev.ctrlKey || ev.metaKey ) )
		return true ;

	var eTarget = el || this ;

	if ( fckContextMenu.OnBeforeOpen )
		fckContextMenu.OnBeforeOpen.call( fckContextMenu, eTarget ) ;

	if ( fckContextMenu._MenuBlock.Count() == 0 )
		return false ;

	if ( fckContextMenu._Redraw )
	{
		fckContextMenu._MenuBlock.Create( fckContextMenu._Panel.MainNode ) ;
		fckContextMenu._Redraw = false ;
	}
	
	// This will avoid that the content of the context menu can be dragged in IE
	// as the content of the panel is recreated we need to do it every time
	FCKTools.DisableSelection( fckContextMenu._Panel.Document.body ) ;

	fckContextMenu._Panel.Show(
		ev.pageX || ev.screenX,
		ev.pageY || ev.screenY,
		ev.currentTarget || null
	) ;

	return false ;
}

function FCKContextMenu_MenuBlock_OnClick( menuItem, contextMenu )
{
	contextMenu._Panel.Hide() ;
	FCKTools.RunFunction( contextMenu.OnItemClick, contextMenu, menuItem ) ;
}