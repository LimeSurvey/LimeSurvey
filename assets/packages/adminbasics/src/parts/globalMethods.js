/**
 * Define global setters for LimeSurvey
 * Also bootstrapping methods and window bound methods are set here
 */
import LOG from '../components/lslog';

const globalWindowMethods = {
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
    validatefilename: (form, strmessage) => {
        if (form.the_file.value == "") {
            $('#pleaseselectfile-popup').modal();
            form.the_file.focus();
            return false ;
        }
        return true ;
    },
    doToolTip: () => {
        try {
            $(".btntooltip").tooltip("destroy");
        } catch (e) {}
        try {
            $('[data-tooltip="true"]').tooltip("destroy");
        } catch (e) {}
        try {
            $('[data-tooltip="true"]').tooltip("destroy");
        } catch (e) {}

        $(".btntooltip").tooltip();
        $('[data-tooltip="true"]').tooltip();
        $('[data-toggle="tooltip"]').tooltip();


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
        const key = getkey(e);
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
    }
};
const globalStartUpMethods = {
    bootstrapping : ()=>{
        $('button,input[type=submit],input[type=button],input[type=reset],.button').button();
        $('button,input[type=submit],input[type=button],input[type=reset],.button').addClass("limebutton");

        $(".progressbar").each(function(){
            var pValue = parseInt($(this).attr('name'));
            $(this).progressbar({value: pValue});

            if (pValue > 85){ $("div",$(this)).css({ 'background': 'Red' }); }
            $("div",this).html(pValue + "%");
        });

        globalWindowMethods.tableCellAdapters();
    }
};


export {globalStartUpMethods, globalWindowMethods};
