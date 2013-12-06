if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.fullscreen = {

	init: function()
	{	
		this.fullscreen = false;
		this.addBtn('fullscreen', 'Fullscreen', function(obj)
		{
			obj.toggleFullscreen();
		});
		
		this.setBtnRight('fullscreen');
	},
	toggleFullscreen: function()
	{
		var html;
	
		if (this.fullscreen === false)
		{
			this.changeBtnIcon('fullscreen', 'normalscreen');
			this.setBtnActive('fullscreen');
			this.fullscreen = true;
			
			this.fsheight = this.$editor.height();

			this.tmpspan = $('<span></span>');
			this.$box.addClass('redactor_box_fullscreen').after(this.tmpspan);

			$('body, html').css('overflow', 'hidden');
			$('body').prepend(this.$box);

			this.fullScreenResize();
			$(window).resize($.proxy(this.fullScreenResize, this));
			$(document).scrollTop(0,0);
			
			this.$editor.focus();
		}
		else
		{
			this.removeBtnIcon('fullscreen', 'normalscreen');
			this.setBtnInactive('fullscreen');
			this.fullscreen = false;

			$(window).unbind('resize', $.proxy(this.fullScreenResize, this));
			$('body, html').css('overflow', '');
			
			this.$box.removeClass('redactor_box_fullscreen').css({ width: 'auto', height: 'auto' });
			this.tmpspan.after(this.$box).remove();
			
			this.syncCode();
			
			
			if (this.opts.autoresize)
			{
				this.$el.css('height', 'auto');
				this.$editor.css('height', 'auto')						
			}
			else
			{
				this.$el.css('height', this.fsheight);
				this.$editor.css('height', this.fsheight)			
			}
			
			this.$editor.focus();
		}		
	},
	fullScreenResize: function()
	{
		if (this.fullscreen === false)
		{
			return false;
		}
		
		var pad = this.$editor.css('padding-top').replace('px', '');
		var height = $(window).height() - 34;
		this.$box.width($(window).width() - 2).height(height+34);		
		this.$editor.height(height-(pad*2));
		this.$el.height(height);
	},
}