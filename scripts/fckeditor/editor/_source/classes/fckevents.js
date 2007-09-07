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
 * FCKEvents Class: used to handle events is a advanced way.
 */

var FCKEvents = function( eventsOwner )
{
	this.Owner = eventsOwner ;
	this._RegisteredEvents = new Object() ;
}

FCKEvents.prototype.AttachEvent = function( eventName, functionPointer )
{
	var aTargets ;

	if ( !( aTargets = this._RegisteredEvents[ eventName ] ) )
		this._RegisteredEvents[ eventName ] = [ functionPointer ] ;
	else
		aTargets.push( functionPointer ) ;
}

FCKEvents.prototype.FireEvent = function( eventName, params )
{
	var bReturnValue = true ;

	var oCalls = this._RegisteredEvents[ eventName ] ;

	if ( oCalls )
	{
		for ( var i = 0 ; i < oCalls.length ; i++ )
			bReturnValue = ( oCalls[ i ]( this.Owner, params ) && bReturnValue ) ;
	}

	return bReturnValue ;
}
