// Submit the form with Ajax
var AjaxSubmitObject = function () {
    var activeSubmit = false;
    // First we get the value of the button clicked  (movenext, submit, prev, etc)
    var move = "";

    var startLoadingBar = function () {
        $('#ajax_loading_indicator').css('display','block').find('#ajax_loading_indicator_bar').css({
            'width': '20%',
            'display': 'block'
        });
    };
    
    
    var endLoadingBar = function () {
        $('#ajax_loading_indicator').css('opacity','0').find('#ajax_loading_indicator_bar').css('width', '100%');
        setTimeout(function () {
            $('#ajax_loading_indicator').css({'display': 'none', 'opacity': 1}).find('#ajax_loading_indicator_bar').css({
                'width': '0%',
                'display': 'none'
            });
        }, 1800);
    };

    var bindActions = function () {

        // Always bind to document to not need to bind again
        $(document).on("click", ".ls-move-btn",function () {
            move = $(this).attr("value");
        });

        // If the user try to submit the form
        // Always bind to document to not need to bind again
        $(document).on("submit", "#limesurvey", function (e) {
            e.preventDefault();

            // Prevent multiposting
            //Check if there is an active submit
            //If there is -> return immediately
            if(activeSubmit) return;
            //block further submissions
            activeSubmit = true;

            //start the loading animation
            startLoadingBar();

            var sUrl = $(this).attr("action");
            var aPost = $(this).serialize();

            // We add the value of the button clicked to the post request
            aPost += "&move=" + move;

            $.ajax({
                url: sUrl,
                type: 'POST',
                dataType: 'html',
                data: aPost,

                success: function (body_html, status, request) {

                    $('.toRemoveOnAjax').each(function () {
                        $(this).remove();
                    });

                    var currentUrl = window.location.href;
                    var cleanUrl = currentUrl.replace("&newtest=Y", "").replace(/\?newtest=Y(\&)?/, '?');

                    if (currentUrl != cleanUrl) {
                        window.history.pushState({
                            "html": body_html,
                            "pageTitle": request.getResponseHeader('title')
                        }, "", cleanUrl);
                    }

                    var $dataScripts = $(body_html).filter('script');
                    var $newBody = $($.parseHTML(body_html));
                    var $replaceableContainer = $newBody.find('div#dynamicReloadContainer');
                    $("#dynamicReloadContainer").empty().append($replaceableContainer);

                    $dataScripts.each(function () {
                        $(this).attr('type', 'text/javascript').addClass('toRemoveOnAjax').prependTo('body');
                    });

                    // We end the loading animation
                    endLoadingBar();
                    
                    //free submitting again
                    activeSubmit = false;

                    //also trigger the pjax:complete event to load all adherent scripts
                    $(document).trigger('pjax:complete');

                },

                error: function (result, status, error) {
                    alert("ERROR");
                    console.log(result);
                }
            });
        });
    };
    return {
        bindActions: bindActions,
        startLoadingBar: startLoadingBar,
        endLoadingBar: endLoadingBar,
        unsetSubmit: function(){activeSubmit = false;},
        blockSubmit: function(){activeSubmit = true;}
    }
}

