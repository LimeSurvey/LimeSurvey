CKEDITOR.dialog.add('videoDialog', function (editor) {
    return {
        title: 'Insert a Youtube, Vimeo, Dailymotion URL or embed code',
        minWidth: 400,
        minHeight: 100,
        contents: [
            {
                id: 'tab-basic',
                label: 'Basic Settings',
                elements: [
                    {
                        type: 'text',
                        id: 'url_video',
                        label: 'Youtube, Vimeo, Dailymotion URL or embed code',

                        /**
                         * Rejects empty input and input without a supported video.
                         *
                         * @return {Boolean|String} True if valid, otherwise the error message
                         */
                        validate: function () {
                            if (!this.getValue()) {
                                return 'Empty!';
                            }
                            if (!CKEDITOR.plugins.videodetector.getEmbedUrl(this.getValue())) {
                                return 'No Youtube, Vimeo or Dailymotion video found.';
                            }
                            return true;
                        },

                        /**
                         * Fills the field with the URL of the edited video.
                         *
                         * @param {CKEDITOR.plugins.widget} widget Video widget
                         * @return {void}
                         */
                        setup: function (widget) {
                            this.setValue(widget.data.src || '');
                        },

                        /**
                         * Stores the embed URL of the entered video in the widget.
                         *
                         * @param {CKEDITOR.plugins.widget} widget Video widget
                         * @return {void}
                         */
                        commit: function (widget) {
                            widget.setData('src', CKEDITOR.plugins.videodetector.getEmbedUrl(this.getValue()));
                        }
                    },
                    {
                        type: 'text',
                        id: 'width',
                        label: 'Width (%)',
                        width: '5em',

                        /**
                         * Accepts an empty value (full width) or a whole number from 10 to 100.
                         *
                         * @return {Boolean|String} True if valid, otherwise the error message
                         */
                        validate: function () {
                            var value = CKEDITOR.tools.trim(this.getValue());
                            if (value === '' || (/^\d+$/.test(value) && value >= 10 && value <= 100)) {
                                return true;
                            }
                            return 'The width must be a number from 10 to 100.';
                        },

                        /**
                         * Fills the field with the width of the edited video. Widths in other units than
                         * percent (set in the source view) are shown as empty and kept unless changed.
                         *
                         * @param {CKEDITOR.plugins.widget} widget Video widget
                         * @return {void}
                         */
                        setup: function (widget) {
                            var width = widget.data.width || '';
                            this.setupValue = /%$/.test(width) ? String(parseInt(width, 10)) : '';
                            this.setValue(this.setupValue);
                        },

                        /**
                         * Stores the entered width in the widget.
                         *
                         * @param {CKEDITOR.plugins.widget} widget Video widget
                         * @return {void}
                         */
                        commit: function (widget) {
                            var value = CKEDITOR.tools.trim(this.getValue());
                            if (value !== this.setupValue) {
                                widget.setData('width', value === '' ? '' : value + '%');
                            }
                        }
                    }
                ]
            }
        ]
    };
});
