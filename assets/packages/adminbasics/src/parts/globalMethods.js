/**
 * Define global setters for LimeSurvey
 * Also bootstrapping methods and window bound methods are set here
 */
import LOG from '../components/lslog';

const globalWindowMethods = {
    // TODO: It seems below two functions are not used and can be deleted. Please confirm.
    renderBootstrapSwitch : () => {
        try{
            if(!$('[data-is-bootstrap-switch]').parent().hasClass('bootstrap-switch-container')) {
                $('[data-is-bootstrap-switch]').bootstrapSwitch({
                    onInit: () => LOG.log("BootstrapSwitch Initialized")
                });
            }
        } catch(e) { LOG.error(e); }
    },
    unrenderBootstrapSwitch : () => {
        try{
            $('[data-is-bootstrap-switch]').bootstrapSwitch('destroy');
        } catch(e) { LOG.error(e); }
    },
    // ==================================================================================
    validatefilename: (form, strmessage) => {
        if (form.the_file.value == "") {
            $('#pleaseselectfile-popup').modal();
            form.the_file.focus();
            return false ;
        }
        return true ;
    },
    doToolTip: () => {
        // Destroy all tooltips
        try {
            $('.tooltip').tooltip('dispose');
        } catch (e) {}

        // Reinit all tooltips
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },
    doSelect2: () => {
        $("select.activate-search").select2();


	$(document).on('select2:open', function(e) {
	  document.querySelector(`[aria-controls="select2-${e.target.id}-results"]`).focus();
	});
    },
    // finds any duplicate array elements using the fewest possible comparison
    arrHasDupes:  ( arrayToCheck ) => {
        return (_.uniq(arrayToCheck).length !== arrayToCheck.length);
    },
    arrHasDupesWhich: ( arrayToCheck ) => {
        return (_.difference(_.uniq(arrayToCheck), arrayToCheck)).length > 0;
    },
    getkey :  (e) => {
        return (window.event) ? window.event.keyCode :(e ? e.which : null);
    },
    goodchars : (e, goods) => {
        const key = globalWindowMethods.getkey(e);
        if (key == null) return true;

        // get character
        const keychar = (String.fromCharCode(key)).toLowerCase();

        goods = goods.toLowerCase();

        return (goods.indexOf(keychar) != -1) || ( key==null || key==0 || key==8 || key==9  || key==27 );

    },
    tableCellAdapters: () => {
        $('table.activecell').on("click", [
            'tbody td input:checkbox',
            'tbody td input:radio',
            'tbody td label',
            'tbody th input:checkbox',
            'tbody th input:radio',
            'tbody th label'
        ].join(', '), function(e) {
            e.stopPropagation();
        });
        $('table.activecell').on("click", 'tbody td, tbody th', function() {
            if($(this).find("input:radio,input:checkbox").length==1)
            {
              $(this).find("input:radio").click();
              $(this).find("input:radio").triggerHandler("click");
              $(this).find("input:checkbox").click();
              $(this).find("input:checkbox").triggerHandler("click");
            }
        });
    },
    sendPost: (url,content, contentObject) => {
        contentObject = contentObject || {};
        const $form = $("<form method='POST'>").attr("action", url);
        if(typeof content == 'string' && content != ''){
            try {
                contentObject = _.merge(contentObject, JSON.parse(content));
            } catch(e) { console.error('JSON parse on sendPost failed!') }
        }

        _.each(contentObject, (value,key) => {
            $("<input type='hidden'>").attr("name", key).attr("value", value).appendTo($form);
        });

        $("<input type='hidden'>").attr("name", LS.data.csrfTokenName).attr("value", LS.data.csrfToken).appendTo($form);
        $form.appendTo("body");
        $form.submit();
    },
    addHiddenElement: (form, name, value) => {
        $('<input type="hidden"/>').attr('name', name).attr('value', value).appendTo($(form));
    },
    fixAccordionPosition : () => {
        $('#accordion').on('shown.bs.collapse',".panel-collapse.collapse", function (e) {
            if(e.target != this) return;
            $('#accordion').find('.panel-collapse.collapse').not('#'+$(this).attr('id')).collapse('hide');
        });
    },
    /**
     * Validates that an end date is not lower than a start date
     * @param {Object} startDatePicker Start datepicker object
     * @param {Object} endDatePicker End datepicker object
     * @param {?function} errorCallback Optional function to call in case of error
     */
    validateEndDateHigherThanStart: (startDatePicker, endDatePicker, errorCallback) => {
        if (!startDatePicker || !startDatePicker.date()) {
            return true;
        }
        if (!endDatePicker || !endDatePicker.date()) {
            return true;
        }
        const difference = endDatePicker.date().diff(startDatePicker.date());
        if (difference >= 0) {
            return true;
        }
        if (typeof errorCallback === 'function') {
            errorCallback();
        }
        return false;
    },
};
const globalStartUpMethods = {
    bootstrapping : ()=>{
        // $('button,input[type=submit],input[type=button],input[type=reset],.button').button();
        // $('button,input[type=submit],input[type=button],input[type=reset],.button').addClass("limebutton");

        $(".progressbar").each(function(){
            var pValue = parseInt($(this).attr('name'));
            $(this).progressbar({value: pValue});

            if (pValue > 85){ $("div",$(this)).css({ 'background': 'Red' }); }
            $("div",this).html(pValue + "%");
        });
        /* set default for select2 */
        $.fn.select2.defaults.set("theme", "bootstrap-5");
        globalWindowMethods.tableCellAdapters();
    }
};


export {globalStartUpMethods, globalWindowMethods};