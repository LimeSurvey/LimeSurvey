var InputOnDemanControlGenerator = function(containerId, options){
    var $container = $(containerId);

    var $list = $container.find('.selector--inputondemand-list');
    var $button = $container.find('.selector--inputondemand-addlinebutton');

    //if(options.autoadd) {}
    var controlListItemVisibility = function() {
        var last = null;
        var lastComplete = null;
        var isFirst = true;
        $list.find('.selector--inputondemand-list-input').each(function(itrt, listItem){
            var isComplete = $(listItem).val() != '';
            if (isFirst || (isComplete && lastComplete == null)) {
                $(listItem).closest('.selector--inputondemand-list-item').removeClass('hidden');
            } else {
                $(listItem).closest('.selector--inputondemand-list-item').addClass('hidden');
                if (lastComplete == null) {
                    lastComplete = last;
                    if (options.autoadd == 'yes') {
                        $(listItem).closest('.selector--inputondemand-list-item').removeClass('hidden');
                    }
                }
            }
            last = listItem;
            isFirst = false;
        });
    }

    var addLine = function() {
        var last = null;
        $list.find('.selector--inputondemand-list-input').each(function(itrt, listItem){
            if(!$(listItem).closest('.selector--inputondemand-list-item').hasClass('hidden')) {
                last = listItem;
                return;
            }
            if(last !== null) {
                $(listItem).closest('.selector--inputondemand-list-item').removeClass('hidden');
                last = null;
                return false;
            }
        });
        if(!$list.find('.selector--inputondemand-list-item').last().hasClass('hidden')) {
            $button.addClass('hidden');
        }
    }

    var bind = function(){
        // Assess which lines to show when question is rendered.
        // It may be someone navigating back or editing a response.
        // Then we should show all non-empty lines
        controlListItemVisibility();

        $button.on('click', function(e){
            e.preventDefault();
            addLine();
        });

        if(options.autoadd == 'yes') {
            $button.addClass('hidden');
            $list.find('.selector--inputondemand-list-input').on('keyup', controlListItemVisibility);
        }
    };

    return {
        bind: bind
    }
}
