if (!RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.fullscreen = {
	init: function()
	{
		this.fullscreen = false;

		this.buttonAdd('fullscreen', 'Fullscreen', $.proxy(this.toggleFullscreen, this));
		this.buttonSetRight('fullscreen');

		if (this.opts.fullscreen) this.toggleFullscreen();
	},
	toggleFullscreen: function()
	{
		var html;

		if (!this.fullscreen)
		{
			this.buttonChangeIcon('fullscreen', 'normalscreen');
			this.buttonActive('fullscreen');
			this.fullscreen = true;

			if (this.opts.toolbarExternal)
			{
				this.toolcss = {};
				this.boxcss = {};
				this.toolcss.width = this.$toolbar.css('width');
				this.toolcss.top = this.$toolbar.css('top');
				this.toolcss.position = this.$toolbar.css('position');
				this.boxcss.top = this.$box.css('top');
			}

			this.fsheight = this.$editor.height();

			if (this.opts.iframe) html = this.get();

			this.tmpspan = $('<span></span>');
			this.$box.addClass('redactor_box_fullscreen').after(this.tmpspan);

			$('body, html').css('overflow', 'hidden');
			$('body').prepend(this.$box);

			if (this.opts.iframe) this.fullscreenIframe(html);

			this.fullScreenResize();
			$(window).resize($.proxy(this.fullScreenResize, this));
			$(document).scrollTop(0, 0);

			this.focus();
			this.observeStart();

		}
		else
		{
			this.buttonRemoveIcon('fullscreen', 'normalscreen');
			this.buttonInactive('fullscreen');
			this.fullscreen = false;

			$(window).off('resize', $.proxy(this.fullScreenResize, this));
			$('body, html').css('overflow', '');

			this.$box.removeClass('redactor_box_fullscreen').css({ width: 'auto', height: 'auto' });

			if (this.opts.iframe) html = this.$editor.html();
			this.tmpspan.after(this.$box).remove();

			if (this.opts.iframe) this.fullscreenIframe(html);
			else this.sync();

			var height = this.fsheight;
			if (this.opts.autoresize) height = 'auto';

			if (this.opts.toolbarExternal)
			{
				this.$box.css('top', this.boxcss.top);
				this.$toolbar.css({
					'width': this.toolcss.width,
					'top': this.toolcss.top,
					'position': this.toolcss.position
				});
			}

			if (!this.opts.iframe) this.$editor.css('height', height);
			else this.$frame.css('height', height);

			this.$editor.css('height', height);
			this.focus();
			this.observeStart();
		}
	},
	fullscreenIframe: function(html)
	{
		this.$editor = this.$frame.contents().find('body').attr({
			'contenteditable': true,
			'dir': this.opts.direction
		});

		// set document & window
		if (this.$editor[0])
		{
			this.document = this.$editor[0].ownerDocument;
			this.window = this.document.defaultView || window;
		}

		// iframe css
		this.iframeAddCss();

		if (this.opts.fullpage) this.setFullpageOnInit(html);
		else this.set(html);

		if (this.opts.wym) this.$editor.addClass('redactor_editor_wym');
	},
	fullScreenResize: function()
	{
		if (!this.fullscreen) return false;

		var toolbarHeight = this.$toolbar.height();

		var pad = this.$editor.css('padding-top').replace('px', '');
		var height = $(window).height() - toolbarHeight;
		this.$box.width($(window).width() - 2).height(height + toolbarHeight);

		if (this.opts.toolbarExternal)
		{
			this.$toolbar.css({
				'top': '0px',
				'position': 'absolute',
				'width': '100%'
			});

			this.$box.css('top', toolbarHeight + 'px');
		}

		if (!this.opts.iframe) this.$editor.height(height - (pad * 2));
		else
		{
			setTimeout($.proxy(function()
			{
				this.$frame.height(height);

			}, this), 1);
		}

		this.$editor.height(height);
	}
};