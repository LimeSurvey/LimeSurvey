(function ($) {
	$.fn.jsonEditor = function(config) {
		var config = config || {}
		var result = this;
		this.each(function() {
			if ($(this).is("textarea"))
			{
				if (typeof $(this).data("jsonEditor") != 'undefined' )
				{
					result = $(this).data("jsonEditor");
					return false;
				}

				var textarea = $(this);
				$(this).parent();

				config.change = function() {
					textarea.val($(textarea).jsonEditor().getText());
				}
                var value = $(this).val();
                if (value == "")
                {
                    value = "{}";
                }
				$(this).data("jsonEditor", new jsoneditor.JSONEditor($(this).parent()[0], config, $.parseJSON(value)));
				textarea.hide();
				result = $(this).data("jsonEditor");
			}

		});
		return result;
	}
})(jQuery);