/**
 * Created with JetBrains PhpStorm.
 * User: antonio
 * Date: 8/31/12
 * Time: 5:18 PM
 * To change this template use File | Settings | File Templates.
 */
$.editable.addInputType('bcolorpicker', {
    /* create input element 'bootstrap style' */
    element : function(settings, original){

        var $input = $('<input/>').
            attr({'type':'text'}).
            css('width','100px');

        $(this).append($input);


        return $input;
    },
    /* attach plugin */
    plugin: function(settings, original) {

        var form = this;
        var $input = $('input', form);

        var options = $.extend({format:'hex'}, settings.colorformat || {});

        $input.colorpicker().on('changeColor', function(ev){
            $input.val(ev.color.toHex());
        });
        $('button', this).addClass('btn');

        setTimeout(function(){$input.select();},200);

    },
    reset: function(settings, original){
        var cpicker = $('input',this).colorpicker()[0];
        var picker = $(cpicker).data('colorpicker').picker;
        $(picker).remove();
        original.reset(this);
    }
});