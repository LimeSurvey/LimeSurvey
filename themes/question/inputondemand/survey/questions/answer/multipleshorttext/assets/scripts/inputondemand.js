var InputOnDemanControlGenerator = function(containerId, options){
    var $container = $(containerId);

    var $list = $container.find('.selector--inputondemand-list');
    var $button = $container.find('.selector--inputondemand-addlinebutton');

    //if(options.autoadd) {}
    var controlListAddAuto = function() {
        var last = null;
        $list.find('.selector--inputondemand-list-input').each(function(itrt, listItem){
            if(last == null) {
                $(listItem).closest('.selector--inputondemand-list-item').removeClass('d-none');
            } else {
                $(listItem).closest('.selector--inputondemand-list-item').addClass('d-none');
            }
            last = $(listItem).val() == '' ? listItem : null;
        });
    }

    var addLine = function() {
        var last = null;
        $list.find('.selector--inputondemand-list-input').each(function(itrt, listItem){
            if(!$(listItem).closest('.selector--inputondemand-list-item').hasClass('d-none')) {
                last = listItem;
                return;
            }
            if(last !== null) {
                $(listItem).closest('.selector--inputondemand-list-item').removeClass('d-none');
                last = null;
                return false;
            }
        });
        if(!$list.find('.selector--inputondemand-list-item').last().hasClass('d-none')) {
            $button.addClass('d-none');
        }
    }

    var bind = function(){
        $list.find('.selector--inputondemand-list-input').first().closest('.selector--inputondemand-list-item').removeClass('d-none');
        console.log('INPUTONDEMAND',  $list.find('.selector--inputondemand-list-input'));
        console.log('INPUTONDEMAND',  $list.find('.selector--inputondemand-list-input').first())
        console.log('INPUTONDEMAND',  $list.find('.selector--inputondemand-lis-input').first().closest('.selector--inputondemand-list-item'))
        $button.on('click', function(e){
            e.preventDefault();
            addLine();
        });

        if(options.autoadd == 'yes') {
            $button.addClass('d-none');
            controlListAddAuto();
            $list.find('.selector--inputondemand-list-input').on('keyup', controlListAddAuto);
        }
    };

    return {
        bind: bind
    }
}