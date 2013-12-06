/**
 * Created with JetBrains PhpStorm.
 * User: antonio
 * Date: 8/31/12
 * Time: 5:18 PM
 * To change this template use File | Settings | File Templates.
 */
$.editable.addInputType('bdatepicker', {
    /* create input element 'bootstrap style' */
    element : function(settings, original){
        var now = new Date();
        var dd = now.getDate();
        var mm = now.getMonth() + 1; // Jan starts at 0

        var yyyy = now.getFullYear();

        if (dd<10){dd='0'+dd;}if(mm<10){mm='0'+mm;}

        var today = dd+'-'+mm+'-'+yyyy;

        var $div = $('<div/>').addClass('input-append date bdatepicker')
            .css({'float':'left','margin-right':'2px'});
        var $input = $('<input/>').
            attr({'type':'text','value':today}).
            addClass('span2');
        var $addon = $('<span/>').addClass('add-on').append($('<i/>').addClass('icon-th'));

        $(this).append($div.append($input).append($addon));


        return $input;
    },
    /* attach plugin */
    plugin: function(settings, original) {

        var form = this;
        var $input = $('input', form);

        $input.datepicker().on('changeDate', function(ev){
            $input.datepicker('hide');
        });
        $('button', this).addClass('btn');

        setTimeout(function(){$input.select();},200);

    },
    reset: function(settings, original){
        var dpicker = $('input',this).datepicker()[0];
        var picker = $(dpicker).data('datepicker').picker;
        $(picker).remove();
        original.reset(this);
    }
});