/**
 * Plugin to show the next step of the ComfortUpdate
 * @param {Object} options
 */
$.fn.comfortUpdateNextStep = function(options)
{
    // Will be used later for animation params
    var defauts={};
    var params=$.extend(defauts, options);

    // We defined the progress menu items to swich on using the number of the actual step
    step = '#step'+params.step+'Updt';
    ps = ( parseInt(params.step) - 1 );
    precStep = '#step'+ ps +'Updt';
    $step = $(step);
    $precStep = $(precStep);
    $precStep.removeClass("on").addClass("off");
    $step.removeClass("off").addClass("on");
    $errormsg = $("#localerrormsg").data('message');

    return this.each(function(){
        if ( $(this).is( "form" ) ) {
            $(this).on('submit', function(e){
                e.preventDefault();

                // After clicking on a update button , we must first hide the buttons container and show the updater with left menus
                if(params.step == 0 )
                {
                    $("#preUpdaterContainer").empty();
                    $("#updaterLayout").show();
                    $("#updaterContainer").show();
                }

                // We show the ajaxloader
                $ajaxLoader = $("#ajaxContainerLoading");
                $updaterContainer = $("#updaterContainer");
                $updaterContainer.empty();
                $ajaxLoader.show();


                // The ajax request call an action to update controller. This action is defined inside the form.
                // For example, the forms .launchUpdateForm inside the view _updatesavailable calls update/sa/getwelcome wich will itself calls the update server to get the welcome message.
                $.ajax({
                    url: $(this).attr('action'),
                    type: $(this).attr('method'),
                    data: $(this).serialize(),
                    success: function(html) {
                        setTimeout(function()
                        {
                            $ajaxLoader.hide();
                            $updaterContainer.empty().append(html);
                        }, 1000);
                    },
                    error :  function(html, statut){
                        $ajaxLoader.hide();
                        $updaterContainer.empty().append("<span class='error'>"+$errormsg+"<br/></span>");
                        $updaterContainer.append(html.responseText);
                    },

                });
            });
        }
    });
};
