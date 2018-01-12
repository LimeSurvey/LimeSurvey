$.fn.displayComfortStep = function(options)
{
    // Will be used later for animation params
    var defauts={};
    var params=$.extend(defauts, options);

    $ajaxLoader = $("#ajaxContainerLoading");
    $ajaxLoader.show();

    $("#preUpdaterContainer").empty();
    $("#updaterLayout").show();
    $("#updaterContainer").show();

    // We need to know the destination build to resume any step
    $destinationBuild = $('#destinationBuildForAjax').val();
    $access_token =  $('#access_tokenForAjax').val();
    $url = "";


    $("#step0Updt").removeClass("on").addClass("off");

    switch(params.step) {
        case "newKey":
            $url = $("#newkeyurl").attr('data-url');
            $("#welcome").hide();
            $("#newKey").show();
            break;

        case "checkFiles":
            $url = $("#filesystemurl").attr('data-url');
            break;

        case "checkLocalErrors":
            $url = $("#checklocalerrorsurl").attr('data-url');
            break;

        case "welcome":
            $url = $("#welcomeurl").attr('data-url');
            break;

    }

    // Those datas are defined in _ajaxVariables view
    datas = 'destinationBuild=' + $destinationBuild + '&access_token=' + $access_token + '&'+csrf_token_name+'='+csrf_token;
    console.ls.log($url);
    $.ajax({
        type: "POST",
        data: datas,
        url: $url,
        success: function(html) {
            // We hide the loader, and we append the submit new key content
            $ajaxLoader.hide();
            $("#updaterContainer").empty().append(html);

            // Menus

        },
        error :  function(html, statut){
            $("#preUpdaterContainer").empty();
            $("#updaterLayout").show();
            $("#updaterContainer").show();

            $("#updaterContainer").empty().append("<span class='error'>you have an error, or a notice, inside your local installation of limesurvey. See : <br/></span>");
            $("#updaterContainer").append(html.responseText);
        }
    });

};
