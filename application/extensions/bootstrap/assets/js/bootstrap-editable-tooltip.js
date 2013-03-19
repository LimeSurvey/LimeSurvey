/*! Bootstrap EditableTooltip
 * In-place tooltip editing with Bootstrap Form and Popover
 * https://github.com/vitalets/bootstrap-editable-tooltip
 * Modified version of Bootstrap Editable From Vitaly Potapov
 * Copyright (c) 2012 Vitaliy Potapov; Licensed MIT, GPL */

(function ($) {

    //Editable object
    var EditableTooltip = function (element, options) {
        var type, typeDefaults, doAutotext = false, valueSetByTooltip = false;
        this.$element = $(element);

        this.$element.tooltip({placement:'top'});

        //detect type
        type = (this.$element.data().type || (options && options.type) || $.fn.editableTooltip.defaults.type);
        typeDefaults = ($.fn.editableTooltip.types[type]) ? $.fn.editableTooltip.types[type] : {};

        //apply options
        this.settings = $.extend({}, $.fn.editableTooltip.defaults, $.fn.editableTooltip.types.defaults, typeDefaults, options, this.$element.data());

        //apply type's specific init()
        this.settings.init.call(this, options);

        //store name
        this.name = this.settings.name || this.$element.attr('id') || this.$element.attr('name');
        if (!this.name) {
            $.error('You should define name (or id) for EditableTooltip element');
        }

        //if validate is map take only needed function
        if (typeof this.settings.validate === 'object' && this.name in this.settings.validate) {
            this.settings.validate = this.settings.validate[this.name];
        }

        //set value from settings or by element text
        if (this.settings.value === undefined || this.settings.value === null) {
            this.settings.setValueByTooltip.call(this);
            valueSetByTooltip = true;
        } else {
            this.value = this.settings.value;
            valueSetByTooltip = false;
        }

        //also storing last saved value (initially equals to value)
        this.lastSavedValue = this.value;

        this.$toggle = $('<i class="icon-info-sign"></i>').attr('data-original-title', 'Tooltip Editor: <strong>'+this.name+'</strong>');

        this.$element.append(this.$toggle);

        //bind click event on toggle
        this.$toggle.on('click', $.proxy(this.click, this));

        //blocking click event when going from inside popover. all other clicks will close it
        $('body').on('click.editable-tooltip', '.editable-tooltip-popover', function (e) { e.stopPropagation(); });

        //autotext
        if(!valueSetByTooltip && this.value !== null && this.value !== undefined) {
            switch(this.settings.autotext) {
                case 'always':
                    doAutotext = true;
                    break;

                case 'never':
                    doAutotext = false;
                    break;

                case 'auto':
                    if(this.$element.attr('title').length) {
                        doAutotext = false;
                    } else {
                        //for SELECT do not use autotext when source is url and autotext = 'auto' (to prevent extra request)
                        if (type === 'select') {
                            this.settings.source = tryParseJson(this.settings.source, true);
                            if (this.settings.source && typeof this.settings.source === 'object') {
                                doAutotext = true;
                            }
                        } else {
                            doAutotext = true;
                        }
                    }
                    break;
            }
        }

        function finalize() {
            //show emptytext if visible text is empty
            this.handleEmpty();

            //trigger 'render' event with property isInit = true
            var event = jQuery.Event("render");
            event.isInit = true;
            this.$element.trigger(event, this);
        }

        if(doAutotext) {
            $.when(this.settings.setTooltipByValue.call(this)).then($.proxy(finalize, this));
        } else {
            finalize.call(this);
        }


    };

    EditableTooltip.prototype = {
        constructor: EditableTooltip,

        click: function (e) {
            e.stopPropagation();
            e.preventDefault();

            var popover = this.$toggle.data('popover');
            if (popover && popover.tip().is(':visible')) {
                this.hide();
            } else {
                this.show();
            }
        },

        show: function () {
            //hide all other popovers if shown
            $('.popover').find('form').find('button.editable-tooltip-cancel').click();

            //for the first time create popover
            if (!this.$toggle.data('popover')) {
                this.$toggle.popover({
                    trigger  :'manual',
                    placement:'right',
                    content  :this.settings.loading
                });

                this.$toggle.data('popover').tip().addClass('editable-tooltip-popover');
            }

            //show popover
            this.$toggle.popover('show');

            //movepopover to correct position. Refers to bug in bootstrap 2.1.x with popover positioning
            this.setPosition();

            this.$element.addClass('editable-tooltip-open');
            this.errorOnRender = false;

            //use deferred approach to load data asynchroniously
            $.when(this.settings.renderInput.call(this))
                .then($.proxy(function () {
                var $tip = this.$toggle.data('popover').tip();

                //render content & input
                this.$content = $(this.settings.formTemplate);
                this.$content.find('div.control-group').prepend(this.$input);

                //invoke form into popover content
                $tip.find('.popover-content p').append(this.$content);

                //set position once more. It is required to pre-move popover when it is close to screen edge.
                this.setPosition();

                //check for error during render input
                if (this.errorOnRender) {
                    this.$input.attr('disabled', true);
                    $tip.find('button.btn-primary').attr('disabled', true);
                    $tip.find('form').submit(function () {
                        return false;
                    });
                    //show error
                    this.enableContent(this.errorOnRender);
                } else {
                    this.$input.removeAttr('disabled');
                    $tip.find('button.btn-primary').removeAttr('disabled');
                    //bind form submit
                    $tip.find('form').submit($.proxy(this.submit, this));
                    //show input (and hide loading)
                    this.enableContent();
                    //set input value
                    this.settings.setInputValue.call(this);
                }

                //bind popover hide on button
                $tip.find('button.editable-tooltip-cancel').click($.proxy(this.hide, this));

                //bind popover hide on escape
                $(document).on('keyup.editable-tooltip', $.proxy(function (e) {
                    if (e.which === 27) {
                        e.stopPropagation();
                        this.hide();
                    }
                }, this));

                //hide popover on external click
                $(document).on('click.editable-tooltip', $.proxy(this.hide, this));

                //trigger 'shown' event
                this.$element.trigger('shown', this);
            }, this));
        },

        submit: function (e) {
            e.stopPropagation();
            e.preventDefault();

            var error,
                value = this.settings.getInputValue.call(this);

            //validation
            if (error = this.validate(value)) {
                this.enableContent(error);
                return;
            }

            /*jslint eqeqeq: false*/
            if (value == this.value) {
                /*jslint eqeqeq: true*/
                //if value not changed --> do nothing, simply hide popover
                this.hide();
            } else {
                //saving new value
                this.save(value);
            }
        },

        save: function(value) {
            $.when(this.send(value))
                .done($.proxy(function (data) {
                var error, isAjax = (typeof data !== 'undefined');

                //check and run custom success handler
                if (isAjax && typeof this.settings.success === 'function' && (error = this.settings.success.apply(this, arguments))) {
                    //show form with error message
                    this.enableContent(error);
                    return;
                }

                //set new value and text
                this.value = value;
                this.settings.setTooltipByValue.call(this);

                //to show that value modified but not saved
                if (isAjax) {
                    this.markAsSaved();
                } else {
                    this.markAsUnsaved();
                }

                this.handleEmpty();
                this.hide();

                //trigger 'render' event with property isInit = false
                var event = jQuery.Event("render");
                event.isInit = false;
                this.$element.trigger(event, this);
            }, this))
                .fail($.proxy(function(xhr) {
                var msg = (typeof this.settings.error === 'function') ? this.settings.error.apply(this, arguments) : null;
                this.enableContent(msg || xhr.responseText || xhr.statusText);
            }, this));
        },

        send: function(value) {
            var send, pk, params;

            //getting primary key
            if (typeof this.settings.pk === 'function') {
                pk = this.settings.pk.call(this.$element);
            } else if (typeof this.settings.pk === 'string' && $(this.settings.pk).length === 1 && $(this.settings.pk).parent().length) { //pk is ID of existing element
                pk = $(this.settings.pk).text();
            } else {
                pk = this.settings.pk;
            }

            send = (this.settings.url !== undefined) && ((this.settings.send === 'always') || (this.settings.send === 'auto' && pk) || (this.settings.send === 'ifpk' /* deprecated */ && pk));

            if (send) { //send to server
                //hide form, show loading
                this.enableLoading();

                //try parse json in single quotes
                this.settings.params = tryParseJson(this.settings.params, true);

                //creating params
                params = (typeof this.settings.params === 'string') ? {params:this.settings.params} : $.extend({}, this.settings.params);
                params.name = this.name;
                params.value = value;
                if (pk) {
                    params.pk = pk;
                }

                //send ajax to server and return deferred object
                return $.ajax({
                    url     : (typeof this.settings.url === 'function') ? this.settings.url.call(this) : this.settings.url,
                    data    : params,
                    type    : 'post',
                    dataType: 'json'
                });
            }
        },

        hide: function () {
            this.$toggle.popover('hide');
            this.$element.removeClass('editable-tooltip-open');
            $(document).off('keyup.editable-tooltip');
            $(document).off('click.editable-tooltip');

            //returning focus on toggle element
            if (this.settings.enablefocus || this.$element.get(0) !== this.$toggle.get(0)) {
                this.$toggle.focus();
            }

            //trigger 'hidden' event
            this.$element.trigger('hidden', this);
        },

        /**
         * show input inside popover
         */
        enableContent:function (error) {
            if (error !== undefined && error.length > 0) {
                this.$content.find('div.control-group').addClass('error').find('span.help-block').text(error);
            } else {
                this.$content.find('div.control-group').removeClass('error').find('span.help-block').text('');
            }
            this.$content.show();
            //hide loading
            this.$toggle.data('popover').tip().find('.editable-tooltip-loading').hide();

            //move popover to final correct position
            this.setPosition();

            //TODO: find elegant way to exclude hardcode of types here
            if (this.settings.type === 'text' || this.settings.type === 'textarea') {
                this.$input.focus();
            }
        },

        /**
         * move popover to new position. This function mainly copied from bootstrap-popover.
         */
        setPosition: function () {
            var p = this.$toggle.data('popover'), $tip = p.tip(), inside = false, placement, pos, actualWidth, actualHeight, tp;

            placement = typeof p.options.placement === 'function' ? p.options.placement.call(p, $tip[0], p.$element[0]) : p.options.placement;

            pos = p.getPosition(inside);

            actualWidth = $tip[0].offsetWidth;
            actualHeight = $tip[0].offsetHeight;


            switch (inside ? placement.split(' ')[1] : placement) {
                case 'bottom':
                    tp = {top:pos.top + pos.height, left:pos.left + pos.width / 2 - actualWidth / 2};
                    break;
                case 'top':
                    /* For Bootstrap 2.1.x: 10 pixels needed to correct popover position. See https://github.com/twitter/bootstrap/issues/4665 */
                    if($tip.find('.arrow').get(0).offsetHeight === 10) {actualHeight += 10;}
                    tp = {top:pos.top - actualHeight, left:pos.left + pos.width / 2 - actualWidth / 2};
                    break;
                case 'left':
                    /* For Bootstrap 2.1.x: 10 pixels needed to correct popover position. See https://github.com/twitter/bootstrap/issues/4665 */
                    if($tip.find('.arrow').get(0).offsetWidth === 10) {actualWidth  += 10;}
                    tp = {top:pos.top + pos.height / 2 - actualHeight / 2, left:pos.left - actualWidth};
                    break;
                case 'right':
                    tp = {top:pos.top + pos.height / 2 - actualHeight / 2, left:pos.left + pos.width};
                    break;
            }

            $tip.css(tp).addClass(placement).addClass('in');
        },

        /**
         * show loader inside popover
         */
        enableLoading:function () {
            //enlage loading to whole area of popover
            var $tip = this.$toggle.data('popover').$tip;
            $tip.find('.editable-tooltip-loading').css({height:this.$content[0].offsetHeight, width:this.$content[0].offsetWidth});

            this.$content.hide();
            this.$toggle.data('popover').tip().find('.editable-tooltip-loading').show();
        },

        handleEmpty:function () {
            //don't have editalbe class --> it's not link --> toggled by another element --> no need to set emptytext
            if (!this.$element.hasClass('editable-tooltip')) {
                return;
            }

            this.$element.attr('data-original-title', this.settings.emptytext);
        },

        validate:function (value) {
            if (value === undefined) {
                value = this.value;
            }
            if (typeof this.settings.validate === 'function') {
                return this.settings.validate.call(this, value);
            }
        },

        markAsUnsaved:function () {
            if (this.value !== this.lastSavedValue) {
                this.$element.addClass('editable-tooltip-changed');
            } else {
                this.$element.removeClass('editable-tooltip-changed');
            }
        },

        markAsSaved:function () {
            this.lastSavedValue = this.value;
            this.$element.removeClass('editable-tooltip-changed');
        }
    };


    /* EDITABLE PLUGIN DEFINITION
     * ======================= */

    $.fn.editableTooltip = function (option) {
        //special methods returning non-jquery object
        var result = {}, args = arguments;
        switch (option) {
            case 'validate':
                this.each(function () {
                    var $this = $(this), data = $this.data('editable-tooltip'), error;
                    if (data && (error = data.validate())) {
                        result[data.name] = error;
                    }
                });
                return result;

            case 'getValue':
                this.each(function () {
                    var $this = $(this), data = $this.data('editable-tooltip');
                    if (data && data.value !== undefined && data.value !== null) {
                        result[data.name] = data.value;
                    }
                });
                return result;

            case 'submit':  //collects value, validate and submit to server for creating new record
                var config = arguments[1] || {},
                    $elems = this,
                    errors = this.editable('validate'),
                    values;

                if(typeof config.error !== 'function') {
                    config.error = function() {};
                }

                if($.isEmptyObject(errors)) {
                    values = this.editable('getValue');
                    if(config.data) {
                        $.extend(values, config.data);
                    }
                    $.ajax({
                        type: 'POST',
                        url: config.url,
                        data: values,
                        dataType: 'json'
                    }).success(function(response) {
                            if(typeof response === 'object' && response.id) {
                                $elems.editable('option', 'pk', response.id);
                                $elems.editable('markAsSaved');
                                if(typeof config.success === 'function') {
                                    config.success.apply($elems, arguments);
                                }
                            } else { //server-side validation error
                                config.error.apply($elems, arguments);
                            }
                        }).error(function(){  //ajax error
                            config.error.apply($elems, arguments);
                        });
                } else { //client-side validation error
                    config.error.call($elems, {errors: errors});
                }

                return this;
        }

        //return jquery object
        return this.each(function () {
            var $this = $(this), data = $this.data('editable-tooltip'), options = typeof option === 'object' && option;
            if (!data) {
                $this.data('editable-tooltip', (data = new EditableTooltip(this, options)));
            }

            if(option === 'option') {
                if(args.length === 2 && typeof args[1] === 'object') {
                    data.settings = $.extend({}, data.settings, args[1]); //set options by object
                } else if(args.length === 3 && typeof args[1] === 'string') {
                    data.settings[args[1]] = args[2]; //set one option
                }
            } else if (typeof option === 'string') {
                data[option]();
            }
        });
    };

    $.fn.editableTooltip.Constructor = EditableTooltip;

    //default settings
    $.fn.editableTooltip.defaults = {
        url:null, //url for submit
        type:'text', //input type
        name:null, //field name
        pk:null, //primary key or record
        value:null, //real value, not shown. Especially usefull for select
        emptytext:'Empty', //text shown on empty element
        params:null, //additional params to submit
        send:'auto', // strategy for sending data on server: 'always', 'never', 'auto' (default). 'auto' = 'ifpk' (deprecated)
        autotext:'auto', //can be auto|never|always. Useful for select element: if 'auto' -> element text will be automatically set by provided value and source (in case source is object so no extra request will be performed).
        enablefocus:false, //wether to return focus on link after popover is closed. It's more functional, but focused links may look not pretty
        formTemplate:'<form class="form-inline" autocomplete="off">' +
            '<div class="control-group">' +
            '&nbsp;<button type="submit" class="btn btn-primary"><i class="icon-ok icon-white"></i></button>&nbsp;<button type="button" class="btn editable-tooltip-cancel"><i class="icon-ban-circle"></i></button>' +
            '<span class="help-block" style="clear: both"></span>' +
            '</div>' +
            '</form>',
        loading:'<div class="editable-tooltip-loading"></div>',

        validate:function (value) {
        }, //client-side validation. If returns msg - data will not be sent
        success:function (data) {
        }, //after send callback
        error:function (xhr) {
        }  //error wnen submitting data
    };

    //input types
    $.fn.editableTooltip.types = {
        //for all types
        defaults:{
            inputclass:'span2',
            placeholder:null,
            init:function (options) {},
            // this function called every time popover shown. Should set value of this.$input
            renderInput:function () {
                this.$input = $(this.settings.template);
                this.$input.addClass(this.settings.inputclass);
                if (this.settings.placeholder) {
                    this.$input.attr('placeholder', this.settings.placeholder);
                }
            },
            setInputValue:function () {
                this.$input.val(this.value);
                this.$input.focus();
            },
            //getter for value from input
            getInputValue:function () {
                return this.$input.val();
            },

            //setting text of element (init)
            setTooltipByValue:function () {
                this.$element.attr('data-original-title', this.value);
            },

            //setting value by element text (init)
            setValueByTooltip:function () {
                this.value = $.trim(this.$element.attr('data-original-title'));
            }
        },

        //text
        text:{
            template:'<input type="text">',
            setInputValue:function () {
                this.$input.val(this.value);
                setCursorPosition.call(this.$input, this.$input.val().length);
                this.$input.focus();
            }
        },
        //textarea
        textarea:{
            template:'<textarea rows="8"></textarea>',
            inputclass:'span3',
            renderInput:function () {
                this.$input = $(this.settings.template);
                this.$input.addClass(this.settings.inputclass);
                if (this.settings.placeholder) {
                    this.$input.attr('placeholder', this.settings.placeholder);
                }

                //ctrl + enter
                this.$input.keydown(function (e) {
                    if (e.ctrlKey && e.which === 13) {
                        $(this).closest('form').submit();
                    }
                });
            },
            setInputValue:function () {
                this.$input.val(this.value);
                setCursorPosition.apply(this.$input, [this.$input.val().length]);
                this.$input.focus();
            },
            setValueByTooltip:function () {
                var content = this.$element.attr('data-original-title');
                if(this.$element.data('html'))
                {
                    lines = content.split(/<br\s*\/?>/i);

                    for (var i = 0; i < lines.length; i++) {
                        lines[i] = $('<div>').html(lines[i]).text();
                    }
                    this.value = lines.join("\n");
                }else{
                    this.value =  content;
                }
            },
            setTooltipByValue:function () {
                var lines = this.value.split("\n");
                for (var i = 0; i < lines.length; i++) {
                    lines[i] = $('<div>').text(lines[i]).html();
                }
                var text = this.$element.data('html') ?  lines.join('<br>') : lines.join(' ');

                this.$element.attr('data-original-title', text);
            }
        },

        //select
        select:{
            template:'<select></select>',
            source:null,
            prepend:false,
            onSourceReady:function (success, error) {
                // try parse json in single quotes (for double quotes jquery does automatically)
                try {
                    this.settings.source = tryParseJson(this.settings.source, false);
                } catch (e) {
                    error.call(this);
                    return;
                }

                if (typeof this.settings.source === 'string') {
                    var cacheID = this.settings.source + '-' + this.name, cache;

                    if (!$(document).data(cacheID)) {
                        $(document).data(cacheID, {});
                    }
                    cache = $(document).data(cacheID);

                    //check for cached data
                    if (cache.loading === false && cache.source && typeof cache.source === 'object') { //take source from cache
                        this.settings.source = cache.source;
                        success.call(this);
                        return;
                    } else if (cache.loading === true) { //cache is loading, put callback in stack to be called later
                        cache.callbacks.push($.proxy(function () {
                            this.settings.source = cache.source;
                            success.call(this);
                        }, this));

                        //also collecting error callbacks
                        cache.err_callbacks.push($.proxy(error, this));
                        return;
                    } else { //no cache yet, activate it
                        cache.loading = true;
                        cache.callbacks = [];
                        cache.err_callbacks = [];
                    }

                    //options loading from server
                    $.ajax({
                        url:this.settings.source,
                        type:'get',
                        data:{name:this.name},
                        dataType:'json',
                        success:$.proxy(function (data) {
                            this.settings.source = this.settings.doPrepend.call(this, data);
                            cache.loading = false;
                            cache.source = this.settings.source;
                            success.call(this);
                            $.each(cache.callbacks, function () {
                                this.call();
                            }); //run callbacks for other fields
                        }, this),
                        error:$.proxy(function () {
                            cache.loading = false;
                            error.call(this);
                            $.each(cache.err_callbacks, function () {
                                this.call();
                            }); //run callbacks for other fields
                        }, this)
                    });
                } else { //options as json/array

                    //convert regular array to object
                    if ($.isArray(this.settings.source)) {
                        var arr = this.settings.source, obj = {};
                        for (var i = 0; i < arr.length; i++) {
                            if (arr[i] !== undefined) {
                                obj[i] = arr[i];
                            }
                        }
                        this.settings.source = obj;
                    }

                    this.settings.source = this.settings.doPrepend.call(this, this.settings.source);
                    success.call(this);
                }
            },

            doPrepend:function (data) {
                this.settings.prepend = tryParseJson(this.settings.prepend, true);

                if (typeof this.settings.prepend === 'string') {
                    return $.extend({}, {'':this.settings.prepend}, data);
                } else if (typeof this.settings.prepend === 'object') {
                    return $.extend({}, this.settings.prepend, data);
                } else {
                    return data;
                }
            },

            renderInput:function () {
                var deferred = $.Deferred();
                this.$input = $(this.settings.template);
                this.$input.addClass(this.settings.inputclass);
                this.settings.onSourceReady.call(this, function () {
                    if (typeof this.settings.source === 'object' && this.settings.source != null) {
                        $.each(this.settings.source, $.proxy(function (key, value) {
                            this.$input.append($('<option>', { value:key }).text(value));
                        }, this));
                    }
                    deferred.resolve();
                }, function () {
                    this.errorOnRender = 'Error when loading options';
                    deferred.resolve();
                });

                return deferred.promise();
            },

            setValueByText:function () {
                this.value = null; //it's not good to set value by select text. better set NULL
            },

            setTextByValue:function () {
                var deferred = $.Deferred();
                this.settings.onSourceReady.call(this, function () {
                    if (typeof this.settings.source === 'object' && this.value in this.settings.source) {
                        this.$element.text(this.settings.source[this.value]);
                    } else {
                        //set empty string when key not found in source
                        this.$element.attr('data-original-title', '');
                    }
                    deferred.resolve();
                }, function () {
                    this.$element.attr('data-original-title', 'Error!');
                    deferred.resolve();
                });

                return deferred.promise();
            }
        }
    };

    /*
     * ========================== FUNCTIONS ========================
     */

    /**
     * set caret position in input
     * see http://stackoverflow.com/questions/499126/jquery-set-cursor-position-in-text-area
     */
    function setCursorPosition(pos) {
        this.each(function (index, elem) {
            if (elem.setSelectionRange) {
                elem.setSelectionRange(pos, pos);
            } else if (elem.createTextRange) {
                var range = elem.createTextRange();
                range.collapse(true);
                range.moveEnd('character', pos);
                range.moveStart('character', pos);
                range.select();
            }
        });
        return this;
    }

    /**
     * function to parse JSON in *single* quotes. (jquery automatically parse only double quotes)
     * That allows such code as: <a data-source="{'a': 'b', 'c': 'd'}">
     * safe = true --> means no exception will be thrown
     * for details see http://stackoverflow.com/questions/7410348/how-to-set-json-format-to-html5-data-attributes-in-the-jquery
     */
    function tryParseJson(s, safe) {
        if (typeof s === 'string' && s.length && s.match(/^\{.*\}$/)) {
            if (safe) {
                try {
                    /*jslint evil: true*/
                    s = (new Function('return ' + s))();
                    /*jslint evil: false*/
                } catch (e) {} finally {
                    return s;
                }
            } else {
                /*jslint evil: true*/
                s = (new Function('return ' + s))();
                /*jslint evil: false*/
            }
        }

        return s;
    }

    /**
     * function merges only specified keys
     */
    function mergeKeys(objTo, objFrom, keys) {
        var key, keyLower;
        if (!$.isArray(keys)) {
            return objTo;
        }
        for (var i = 0; i < keys.length; i++) {
            key = keys[i];
            if (key in objFrom) {
                objTo[key] = objFrom[key];
                continue;
            }
            //note, that when getting data-* attributes via $.data() it's converted it to lowercase.
            //details: http://stackoverflow.com/questions/7602565/using-data-attributes-with-jquery
            //workaround is code below.
            keyLower = key.toLowerCase();
            if (keyLower in objFrom) {
                objTo[key] = objFrom[keyLower];
            }
        }
        return objTo;
    }

}(window.jQuery));
