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
    $(document).on('blur', 'input, select, textarea',setTouched);


    $(document).on('change', 'input, select, textarea', function(e) {
        setTouched.call(this);
        // Touched class (same as angularJS .st-dirty)
        $(this).addClass('dirty');
        $(this).closest('div.question').addClass('dirty');
        var code = name2code[$(this).attr('name')];
        if (typeof code != 'undefined') {

            // Get the value for a name.
            if ($(this).is('[type=checkbox]:not(:checked)')) {
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
            var result = that.evaluate($elem.attr('data-irrelevance-expression'));
            //console.log("Evaluated " + $elem.attr('data-irrelevance-expression') + " result:");
            //console.log(result);
            $elem.toggleClass('irrelevant', result);
        });
        $('[data-enabled-expression]').each(function(i, elem) {
            var $elem = $(elem);
            var result = that.evaluate($elem.attr('data-enabled-expression'));
            //console.log("Evaluated " + $elem.attr('data-irrelevance-expression') + " result:");
            //console.log(result);
            $elem.find('input, textarea, select').attr('disabled', !result);
            $elem.attr('disabled', !result);
        });
    }

    // Update replacements.
    this.updateReplacements = function() {
        $('[data-expression]').each(function(e) {
            $this = $(this);
            var html = eval($this.attr('data-expression'));
            if (html === null) {
                $this.html('');
            } else {
                $this.html(html);
            }

        });
    }

    this.updateValidity = function() {
        $('[data-validation-expression]').each(function(i, elem) {
            var $elem = $(elem);
            $elem.closest('.answer').toggleClass('invalid', !that.evaluate($elem.attr('data-validation-expression')));

        })
    }


    this.getElement = function(code) {
        return $('[name=' + vars[code].name + ']').closest('.question-wrapper').parent();
    }

    this.evaluate = function(expr) {
        if (typeof expr == "boolean") {
            return expr;
        } else if (expr == "") {
            return false;
        }
        return eval(expr);
    }
    this.isRelevant = function(code) {
        return this.evaluate(vars[code].relevance);
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
                if (this.isRelevant(code)) {
                    return vars[code].value;
                } else {
                    return null;
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
                    return null;
                }
            default:
                console.error('Unknown suffix: ' + suffix);
        }
    }
}