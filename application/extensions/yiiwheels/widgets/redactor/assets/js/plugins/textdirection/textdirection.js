if (!RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.textdirection = {
	init: function()
	{
		var that = this;
		var dropdown = {};

		dropdown['ltr'] = { title: 'Left to right', callback: function () { that.ltrTextDirection(); } };
		dropdown['rtl'] = { title: 'Right to left', callback: function () { that.rtlTextDirection(); } };

		this.buttonAdd('direction', 'Change direction', false, dropdown);
	},
	rtlTextDirection: function()
	{
		this.bufferSet();
		this.blockSetAttr('dir', 'rtl');
	},
	ltrTextDirection: function()
	{
		this.bufferSet();
		this.blockRemoveAttr('dir');
	}
};