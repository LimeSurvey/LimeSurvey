var SurveymenuEntriesFunctions = function() {
	var oCurrentDataOptions = {};
	
	
	var pruneEmpty = function(obj) {
        return (function prune(current) {
            $.each(current, function(key, value) {
                if (value === "null" || (typeof value === 'object' && $.isEmptyObject(value))) {
                    delete current[key];
                }
                if (typeof value === "object" && !$.isArray(value)) current[key] = pruneEmpty(value);
            });
            return current;
        })($.extend(true, {}, obj)); // Do not modify the original object, create a clone instead
    }

    var recurseGetObjectValueWithArray = function(obj, arr, pos) {
        pos = pos || 0;
        if (arr.length == pos) {
            return obj;
        }
        return obj[arr[pos]] !== undefined
            ? recurseGetObjectValueWithArray(obj[arr[pos]], arr, pos + 1)
            : null;
    };
    var setObjectValueWithArray = function(value, obj, arr) {
        var nobjstr = "";
        for (var j = 0; j < arr.length; j++) {
            nobjstr +=
                j < arr.length - 1
                    ? '{ "' + arr[j] + '": '
                    : '{ "' + arr[j] + '": ' + JSON.stringify(value);
        }

        nobjstr += "}".repeat(arr.length);

        var nobj = JSON.parse(nobjstr),
            max = arr.length,
            ref = obj,
            i = 0,
            success = false;

        obj = $.extend(true, obj, nobj);

        do {
            var tmpref = ref[arr[i]];
            if (typeof tmpref !== "object" || $.isArray(tmpref)) {
                if (tmpref !== undefined) {
                    ref[arr[i]] = value;
                    success = true;
                    break;
                }
            }
            ref = tmpref;
            tmpref = undefined;
            i++;
        } while (true);

        return success;
    };

    var triggerHide = function(elem) {
        var prio = $(elem).data("priority");
        var value = $(elem).prop("checked");
        $(".selector__dataOptionModel").each(function(j, jtem) {
            if ($(jtem).data("priority") < prio) {
                $(jtem).prop("checked", false).trigger('change');
                $(jtem).prop("disabled", value);
                if (value) {
                    $(jtem)
                        .closest("label")
                        .addClass("disabled");
                } else {
                    $(jtem)
                        .closest("label")
                        .removeClass("disabled");
                }
            }
        });
    };

    var onChangeCheckbox = function(elem) {
        var toSetValue = $(elem).prop("checked")
            ? $(elem).data("value")
            : "null";
        var optionArray = $(elem).data("option");
        var setSuccessfull = setObjectValueWithArray(
            toSetValue,
            oCurrentDataOptions,
            optionArray
        );

        if (setSuccessfull) {
            $("#SurveymenuEntries_data").val(
                JSON.stringify(pruneEmpty(oCurrentDataOptions))
            );
        }

        if ($(elem).hasClass("selector__disable_following")) {
            triggerHide(elem);
        }
    };

    var bind = function() {
        var sCurrentDataOptions = $("#SurveymenuEntries_data").val();

        try {
            oCurrentDataOptions = JSON.parse(sCurrentDataOptions);
        } catch (e) {
            console.ls.warn(
                "Invalid JSON in data setting",
                sCurrentDataOptions
            );
        }

        $(".selector__dataOptionModel").each(function(i, item) {
            var aOptionValues = $(item).data("option");
            var currentObjectValue = recurseGetObjectValueWithArray(
                oCurrentDataOptions,
                aOptionValues
            );
            if (currentObjectValue !== null && currentObjectValue !== false) {
				$(item).prop("checked", true);
				if ($(item).hasClass("selector__disable_following")) {
					triggerHide(item);
				}
            }
        });
		
		$('.selector__hasInfoBox').on('focusin', function(){
			console.ls.log('FOCUSIN', $(this).closest('div.ex-form-group').find('.selector_infoBox'));
			$(this).closest('div.ex-form-group').find('.selector_infoBox').removeClass('d-none');
			$(this).on('focusout.infoTrigger', function(){
				$(this).closest('div.ex-form-group').find('.selector_infoBox').addClass('d-none');
				$(this).off('focusout.infoTrigger');
			})
		});

        $("#SurveymenuEntries_data").on("change", function() {});
        $(".selector__dataOptionModel").on("change", function() {
			onChangeCheckbox(this);
		});
    };
    return bind;
};
