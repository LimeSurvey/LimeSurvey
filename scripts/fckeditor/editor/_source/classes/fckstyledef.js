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
 * FCKStyleDef Class: represents a single style definition.
 */

var FCKStyleDef = function( name, element )
{
	this.Name = name ;
	this.Element = element.toUpperCase() ;
	this.IsObjectElement = FCKRegexLib.ObjectElements.test( this.Element ) ;
	this.Attributes = new Object() ;
}

FCKStyleDef.prototype.AddAttribute = function( name, value )
{
	this.Attributes[ name ] = value ;
}

FCKStyleDef.prototype.GetOpenerTag = function()
{
	var s = '<' + this.Element ;

	for ( var a in this.Attributes )
		s += ' ' + a + '="' + this.Attributes[a] + '"' ;

	return s + '>' ;
}

FCKStyleDef.prototype.GetCloserTag = function()
{
	return '</' + this.Element + '>' ;
}


FCKStyleDef.prototype.RemoveFromSelection = function()
{
	if ( FCKSelection.GetType() == 'Control' )
		this._RemoveMe( FCK.ToolbarSet.CurrentInstance.Selection.GetSelectedElement() ) ;
	else
		this._RemoveMe( FCK.ToolbarSet.CurrentInstance.Selection.GetParentElement() ) ;
}