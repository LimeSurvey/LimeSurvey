/*----------------------------------------------------------------------------\
|                                Range Class                                  |
|-----------------------------------------------------------------------------|
|                         Created by Erik Arvidsson                           |
|                  (http://webfx.eae.net/contact.html#erik)                   |
|                      For WebFX (http://webfx.eae.net/)                      |
|-----------------------------------------------------------------------------|
| Used to model the data used when working with sliders, scrollbars and       |
| progress bars. Based on the ideas of the javax.swing.BoundedRangeModel      |
| interface defined by Sun for Java. http://java.sun.com/products/jfc/        |
| swingdoc-api-1.0.3/com/sun/java/swing/BoundedRangeModel.html                |                                                              |
|-----------------------------------------------------------------------------|
|                  Copyright (c) 1999 - 2002 Erik Arvidsson                   |
|-----------------------------------------------------------------------------|
| This software is provided "as is", without warranty of any kind, express or |
| implied, including  but not limited  to the warranties of  merchantability, |
| fitness for a particular purpose and noninfringement. In no event shall the |
| authors or  copyright  holders be  liable for any claim,  damages or  other |
| liability, whether  in an  action of  contract, tort  or otherwise, arising |
| from,  out of  or in  connection with  the software or  the  use  or  other |
| dealings in the software.                                                   |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| This  software is  available under the  three different licenses  mentioned |
| below.  To use this software you must chose, and qualify, for one of those. |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| The WebFX Non-Commercial License          http://webfx.eae.net/license.html |
| Permits  anyone the right to use the  software in a  non-commercial context |
| free of charge.                                                             |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| The WebFX Commercial license           http://webfx.eae.net/commercial.html |
| Permits the  license holder the right to use  the software in a  commercial |
| context. Such license must be specifically obtained, however it's valid for |
| any number of  implementations of the licensed software.                    |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| GPL - The GNU General Public License    http://www.gnu.org/licenses/gpl.txt |
| Permits anyone the right to use and modify the software without limitations |
| as long as proper  credits are given  and the original  and modified source |
| code are included. Requires  that the final product, software derivate from |
| the original  source or any  software  utilizing a GPL  component, such  as |
| this, is also licensed under the GPL license.                               |
|-----------------------------------------------------------------------------|
| 2002-10-14 | Original version released                                      |
|-----------------------------------------------------------------------------|
| Created 2002-10-14 | All changes are in the log above. | Updated 2002-10-14 |
\----------------------------------------------------------------------------*/


function Range() {
	this._value = 0;
	this._minimum = 0;
	this._maximum = 100;
	this._extent = 0;
	
	this._isChanging = false;
}

Range.prototype.setValue = function (value) {
	value = parseInt(value);
	if (isNaN(value)) return;
	if (this._value != value) {
		if (value + this._extent > this._maximum)
			this._value = this._maximum - this._extent;
		else if (value < this._minimum)
			this._value = this._minimum;
		else
			this._value = value;
		if (!this._isChanging && typeof this.onchange == "function")
			 this.onchange();
	}
};

Range.prototype.getValue = function () {
	return this._value;
};

Range.prototype.setExtent = function (extent) {
	if (this._extent != extent) {
		if (extent < 0)
			this._extent = 0;
		else if (this._value + extent > this._maximum)
			this._extent = this._maximum - this._value;
		else
			this._extent = extent;
		if (!this._isChanging && typeof this.onchange == "function")
			this.onchange();
	}
};

Range.prototype.getExtent = function () {
	return this._extent;
};

Range.prototype.setMinimum = function (minimum) {
	if (this._minimum != minimum) {
		var oldIsChanging = this._isChanging;
		this._isChanging = true;

		this._minimum = minimum;
		
		if (minimum > this._value)
			this.setValue(minimum);
		if (minimum > this._maximum) {
			this._extent = 0;
			this.setMaximum(minimum);
			this.setValue(minimum)
		}
		if (minimum + this._extent > this._maximum)
			this._extent = this._maximum - this._minimum;

		this._isChanging = oldIsChanging;
		if (!this._isChanging && typeof this.onchange == "function")
			this.onchange();
	}
};

Range.prototype.getMinimum = function () {
	return this._minimum;
};

Range.prototype.setMaximum = function (maximum) {
	if (this._maximum != maximum) {
		var oldIsChanging = this._isChanging;
		this._isChanging = true;

		this._maximum = maximum;		
		
		if (maximum < this._value)
			this.setValue(maximum - this._extent);
		if (maximum < this._minimum) {
			this._extent = 0;
			this.setMinimum(maximum);
			this.setValue(this._maximum);
		}		
		if (maximum < this._minimum + this._extent)
			this._extent = this._maximum - this._minimum;
		if (maximum < this._value + this._extent)
			this._extent = this._maximum - this._value;
		
		this._isChanging = oldIsChanging;
		if (!this._isChanging && typeof this.onchange == "function")
			this.onchange();
	}
};

Range.prototype.getMaximum = function () {
	return this._maximum;
};
