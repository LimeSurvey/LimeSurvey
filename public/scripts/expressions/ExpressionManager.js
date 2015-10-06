function ExpressionManager(vars) {
    var that = this;
    var name2code = {};
    /**
     * Initialization
     */

    // Touched class (same as angularJS .st-touched)
    var setTouched = function() {
        $(this).addClass('touched');
        $(this).closest('div.question').addClass('touched');
    }
    $(document).on('blur', ':input',setTouched);

    /**
     * Add name and value of the button to the form.
     */
    $(document).on('click', 'button[type=submit][value]', function(e) {
        var $button = $(this);
        var $form = $button.closest('form');
        if ($form.length > 0) {
            // Add button data.
            $form.append($('<input type="hidden" name="_button"/>').attr('value', $button.attr('value')));
        }
    });
    // A form is touched when someone tries to submit it.
    $(document).on('submit',"form", function(e) {
        var $this = $(this);
        $this.addClass('touched');
        // Check if form is valid.
        if ($this.find('.invalid').length > 0) {
            e.preventDefault();
        } else {
            // Disable buttons to prevent repeated clicks.
            $this.find(':button').attr('disabled', true);
        }
    });

    $(document).on('change', ':input', function(e) {
        setTouched.call(this);
        // Touched class (same as angularJS .st-dirty)
        $(this).addClass('dirty');
        $(this).closest('div.question').addClass('dirty');
        var code = name2code[$(this).attr('name')];

        if (typeof code != 'undefined') {

            if ($(this).is('[type=file]')) {
                // Update filecount.
                countCode = code + "_filecount";
                vars[countCode].value = this.files.length;

            } else if ($(this).is('[type=checkbox]:not(:checked)')) {
                // Replace this with something not hardcoded?
                vars[code].value = 'N';
            } else {
                vars[code].value = $(this).val();
            }

            console.log("Updated " + code + ' --> ' + vars[code].value);
            that.updateVisibility();
            that.updateReplacements();
            that.updateValidity();
        } else {
            console.log("Not updated, no code found for: " + $(this).attr('name'));
        }
    });

    $(document).ready(function() {
        that.updateVisibility();
        that.updateReplacements();
        that.updateValidity();
    });

    for (var code in vars) {
        name2code[vars[code].name] = code;
    }

    /**
     * Public functions
     */
    this.setValues = function(values) {
        for (var key in values) {
            vars[name2code[key]].value = values[key];
        }
    }
    this.debug = function() {
        console.log(EM);
        console.log(vars);
    }
    this.splitVar = function (code) {
        var parts = code.split('.', 2);
        if (parts.count == 1) {
            parts[1] = 'value';
        }
        return parts;
    };

    // Evaluate all question visibility.
    this.updateVisibility = function() {
        console.log('Updating question visibility');
        $('[data-relevance-expression]').each(function(i, elem) {
            var $elem = $(elem);
            var result = that.evaluate($elem.attr('data-relevance-expression'));
            //console.log("Evaluated " + $elem.attr('data-relevance-expression') + " result:");
            //console.log(result);
            $elem.toggleClass('irrelevant', !result);
        });
        $('[data-irrelevance-expression]').each(function(i, elem) {
            var $elem = $(elem);
            var result = that.evaluate($elem.attr('data-irrelevance-expression'), $elem);
            //console.log("Evaluated " + $elem.attr('data-irrelevance-expression') + " result:");
            //console.log(result);
            $elem.toggleClass('irrelevant', result);
        });
        $('[data-enabled-expression]').each(function(i, elem) {
            var $elem = $(elem);
            var result = that.evaluate($elem.attr('data-enabled-expression'), $elem);
            console.log("Evaluated " + $elem.attr('data-enabled-expression') + " result:");
            console.log(result);
            $elem.find('input, textarea, select').attr('disabled', !result);
            $elem.attr('disabled', !result);
        });
    }

    // Update replacements.
    this.updateReplacements = function() {
        $('[data-expression]').each(function(i, elem) {
            var $elem = $(elem);
            var text = that.evaluate($elem.attr('data-expression'));
            if (text === null) {
                $elem.html('');
            } else {
                $elem.text(text);
            }

        });
    }

    this.updateValidity = function() {
        $('[data-validation-expression]').each(function(i, elem) {
            var $elem = $(elem);
            $elem.closest('.answer').toggleClass('invalid', !that.evaluate($elem.attr('data-validation-expression'), $elem));

        })
    }


    this.getElement = function(code) {
        return $('[name=' + vars[code].name + ']').closest('.question-wrapper').parent();
    }

    this.evaluate = function(expr, reference) {
        if (typeof expr == "boolean") {
            return expr;
        } else if (expr == "") {
            return false;
        }
        try {
            var result = eval(expr);
        } catch (e) {
            if (e instanceof SyntaxError) {
                console.log(reference);
                console.error(e.message);
            }
        }
        return result;
    }
    this.isRelevant = function(code) {
        return this.evaluate(vars[code].relevance, code);
    }

    this.val = function(code) {
        var parts = this.splitVar(code);
        if (parts.length == 1) {
            code = parts[0];
            var suffix = 'value';
        } else {
            code = parts[0];
            var suffix = parts[1];
        }

        switch(suffix) {
            case 'value':
                // Allow for using EM.val() on numbers.
                if (parseFloat(code).toString() == code) {
                    return code;
                }
                if (this.isRelevant(code)) {
                    return vars[code].value;
                } else {
                    return {"irrelevant" : true};
                }
                break;
            case 'shown':
                if (this.isRelevant(code)) {
                    var shown = vars[code].labels[vars[code].value];
                    if (typeof shown == 'undefined') {
                        return null;
                    } else {
                        return shown;
                    }
                } else {
                    return {"irrelevant" : true};
                }
            case 'NAOK':
                return vars[code].value;
                break;
            case 'relevance':
                return vars[code].relevance;
            default:
                console.error('Unknown suffix: ' + suffix);
        }
    }
}

