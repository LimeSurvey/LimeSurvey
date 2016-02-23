/*

* Price Format jQuery Plugin
* Created By Eduardo Cuducos
* Currently maintained by Flavio Silveira flavio [at] gmail [dot] com
* Version: 2.0
* Release: 2014-01-26

*/

(function($) {

    /****************
    * Main Function *
    *****************/
    $.fn.priceFormat = function(options)
    {

        var defaults =
        {
            prefix: 'US$ ',
            suffix: '',
            centsSeparator: '.',
            thousandsSeparator: ',',
            limit: false,
            centsLimit: 2,
            clearPrefix: false,
            clearSufix: false,
            allowNegative: false,
            insertPlusSign: false,
            clearOnEmpty:false,
            keepCursorPosition: true,
            startWithInteger: true,
            shiftCentsToInteger: false,
            allowCtrlKey: true

        };

        var options = $.extend(defaults, options);

        return this.each(function()
        {
            // pre defined options
            var obj = $(this);
            var value = '';
            var is_number = /[0-9]/;

            // Check if is an input
            if(obj.is('input'))
            {
                value = obj.val();
            }
            else
            {
                value = obj.html();
            }

            // load the pluggings settings
            var prefix = options.prefix;
            var suffix = options.suffix;
            var centsSeparator = options.centsSeparator;
            var thousandsSeparator = options.thousandsSeparator;
            var limit = options.limit;
            var centsLimit = options.centsLimit;
            var clearPrefix = options.clearPrefix;
            var clearSuffix = options.clearSuffix;
            var allowNegative = options.allowNegative;
            var insertPlusSign = options.insertPlusSign;
            var clearOnEmpty = options.clearOnEmpty;
            var keepCursorPosition = options.keepCursorPosition;
            var startWithInteger = options.startWithInteger;
            var shiftCentsToInteger = options.shiftCentsToInteger;
            var allowCtrlKey = options.allowCtrlKey;

            // If insertPlusSign is on, it automatic turns on allowNegative, to work with Signs
            if (insertPlusSign)
            {
                allowNegative = true;
            }

            function set(nvalue)
            {
                if(obj.is('input'))
                {
                    var dObj = obj[0];
                    if(keepCursorPosition && obj.is('input') && dObj.selectionStart == dObj.selectionEnd)
                    {
                        var str = obj.val();
                        var pointPosition = dObj.selectionStart;

                        var diffThousandSeparator = 0;
                        if(str.indexOf(thousandsSeparator) !== -1 || nvalue.indexOf(thousandsSeparator) !== -1)
                        {
                            diffThousandSeparator = str.length - nvalue.length;
                            pointPosition -= diffThousandSeparator;
                        }

                        obj.val(nvalue);

                        dObj.selectionStart = pointPosition;
                        dObj.selectionEnd = pointPosition;
                    }
                    else
                    {
                        obj.val(nvalue);
                    }
                }
                else
                {
                    obj.html(nvalue);
                }
            }

            function get()
            {
                if(obj.is('input'))
                {
                    value = obj.val();
                }
                else
                {
                    value = obj.html();
                }
                return value;
            }

            // skip everything that isn't a number
            // and also skip the left zeroes
            function to_numbers (str, skipLeftZero)
            {
                var formatted = '';
                for (var i=0;i<(str.length);i++)
                {
                    char_ = str.charAt(i);
                    if (skipLeftZero && formatted.length==0 && char_==0)
                    {
                        char_ = false;
                    }

                    if (char_ && char_.match(is_number))
                    {
                        if (limit)
                        {
                            if (formatted.length < limit)
                            {
                                formatted = formatted+char_;
                            }
                        }
                        else
                        {
                            formatted = formatted+char_;
                        }
                    }
                }

                return formatted;
            }

            // format to fill with zeros to complete cents chars
            function fill_with_zeroes_left (str)
            {
                while (str.length<(centsLimit+1))
                {
                    str = '0'+str;
                }
                return str;
            }

            // format to fill with zeros to complete cents chars at right
            function fill_with_zeroes_right (str)
            {
                while (str.length<(centsLimit+1))
                {
                    str = str+'0';
                }
                return str;
            }

            // format as price
            function price_format (str, ignore)
            {
                if(!ignore && (str === '' || str == price_format('0', true)) && clearOnEmpty)
                {
                    return '';
                }

                // Checking CentsLimit
                if(centsLimit == 0)
                {
                    centsSeparator = "";
                }

                // formatting settings
                if(centsLimit > 0)
                {
                    var ptPos = str.indexOf(centsSeparator);
                    if(ptPos !== -1)
                    {
                        if(!shiftCentsToInteger)
                        {
                            centsVal = str.substr(ptPos + 1);
                            centsVal = fill_with_zeroes_right(to_numbers(centsVal, false)).substr(0, centsLimit);
                            str = str.substr(0, ptPos)+centsSeparator+centsVal;
                        }
                    }
                    else if(startWithInteger)
                    {
                        var len = str.length + centsLimit + 1;
                        str += centsSeparator;
                        while (str.length<(len))
                        {
                            str = str+'0';
                        }
                    }
                }

                var formatted = str;

                formatted = fill_with_zeroes_left(to_numbers(formatted, true));
                var thousandsFormatted = '';
                var thousandsCount = 0;

                var centsVal = "";
                var integerVal = formatted;

                centsVal = formatted.substr(formatted.length-centsLimit,centsLimit);
                integerVal = formatted.substr(0,formatted.length-centsLimit);

                // apply cents pontuation
                formatted = (centsLimit==0) ? integerVal : integerVal+centsSeparator+centsVal;

                // apply thousands pontuation
                if (thousandsSeparator || $.trim(thousandsSeparator) != "")
                {
                    for (var j=integerVal.length;j>0;j--)
                    {
                        char_ = integerVal.substr(j-1,1);
                        thousandsCount++;
                        if (thousandsCount%3==0)
                        {
                            char_ = thousandsSeparator+char_;
                        }
                        thousandsFormatted = char_+thousandsFormatted;
                    }

                    if (thousandsFormatted.substr(0,1)==thousandsSeparator)
                    {
                        thousandsFormatted = thousandsFormatted.substring(1,thousandsFormatted.length);
                    }
                    formatted = (centsLimit==0) ? thousandsFormatted : thousandsFormatted+centsSeparator+centsVal;
                }

                // if the string contains a dash, it is negative - add it to the begining (except for zero)
                if (allowNegative && (integerVal != 0 || centsVal != 0))
                {
                    if (str.indexOf('-') != -1 && str.indexOf('+')<str.indexOf('-') )
                    {
                        formatted = '-' + formatted;
                    }
                    else
                    {
                        if(!insertPlusSign)
                        {
                            formatted = '' + formatted;
                        }
                        else
                        {
                            formatted = '+' + formatted;
                        }
                    }
                }

                // apply the prefix
                if (prefix)
                {
                    formatted = prefix+formatted;
                }

                // apply the suffix
                if (suffix)
                {
                    formatted = formatted+suffix;
                }

                return formatted;
            }

            // filter what user type (only numbers and functional keys)
            function key_check (e)
            {
                var code = (e.keyCode ? e.keyCode : e.which);
                var functional = false;
                var str = value;

                // allow key numbers, 0 to 9
                if((code >= 48 && code <= 57) || (code >= 96 && code <= 105))
                {
                    functional = true;
                }

                // check Backspace, Tab, Enter, Delete, and left/right arrows
                else if (code ==  8)
                {
                    functional = true;
                }
                else if (code ==  9)
                {
                    functional = true;
                }
                else if (code == 13)
                {
                    functional = true;
                }
                else if (code == 46)
                {
                    functional = true;
                }
                else if (code == 37)
                {
                    functional = true;
                }
                else if (code == 39)
                {
                    functional = true;
                }
                // Minus Sign, Plus Sign
                else if (allowNegative && (code == 189 || code == 109 || code == 173))
                {
                    functional = true;
                }
                else if (insertPlusSign && (code == 187 || code == 107 || code == 61))
                {
                    functional = true;
                }

                // Home, End
                else if (code == 35)
                {
                    functional = true;
                }
                else if (code == 36)
                {
                    functional = true;
                }

                // allow Ctrl shortcuts (copy, paste etc.)
                else if (allowCtrlKey && e.ctrlKey)
                {
                    functional = true;
                }


                if (!functional)
                {
                    e.stopPropagation();
                }

            }

            // Formatted price as a value
            function price_it ()
            {
                var str = get();
                var price = price_format(str);
                if (str != price)
                {
                    set(price);
                }
                if(parseFloat(str) == 0.0 && clearOnEmpty)
                {
                    set('');
                }
            }

            // Add prefix on focus
            function add_prefix()
            {
                obj.val(prefix + get());
            }

            function add_suffix()
            {
                obj.val(get() + suffix);
            }

            // Clear prefix on blur if is set to true
            function clear_prefix()
            {
                if($.trim(prefix) != '' && clearPrefix)
                {
                    var array = get().split(prefix);
                    set(array[1]);
                }
            }

            // Clear suffix on blur if is set to true
            function clear_suffix()
            {
                if($.trim(suffix) != '' && clearSuffix)
                {
                    var array = get().split(suffix);
                    set(array[0]);
                }
            }

            // bind the actions
            obj.bind('keydown.price_format', key_check);
            obj.bind('keyup.price_format', price_it);
            obj.bind('focusout.price_format', price_it);

            // Clear Prefix and Add Prefix
            if(clearPrefix)
            {
                obj.bind('focusout.price_format', function()
                {
                    clear_prefix();
                });

                obj.bind('focusin.price_format', function()
                {
                    add_prefix();
                });
            }

            // Clear Suffix and Add Suffix
            if(clearSuffix)
            {
                obj.bind('focusout.price_format', function()
                {
                    clear_suffix();
                });

                obj.bind('focusin.price_format', function()
                {
                    add_suffix();
                });
            }

            // If value has content
            if (get().length>0)
            {
                price_it();
                clear_prefix();
                clear_suffix();
            }

        });

    };

    /**********************
    * Remove price format *
    ***********************/
    $.fn.unpriceFormat = function(){
      return $(this).unbind(".price_format");
    };

    /******************
    * Unmask Function *
    *******************/
    $.fn.unmask = function(){

        var field;
        var result = "";

        if($(this).is('input'))
        {
            field = $(this).val();
        }
        else
        {
            field = $(this).html();
        }

        for(var f in field)
        {
            if(!isNaN(field[f]) || field[f] == "-")
            {
                result += field[f];
            }
        }

        return result;
    };

})(jQuery);
