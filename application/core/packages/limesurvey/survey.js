/**
 * @file Javascript core function for public survey
 * @description loaded before template javascript : allow template to use own function (if function is called after template.js)
 * @copyright LimeSurvey <http://www.limesurvey.org/>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

/**
 * Manage the index
 */
function manageIndex(){
    $("#index").on('click','li,.row',function(e){
        if(!$(e.target).is('button')){
            $(this).children("[name='move']").click();
        }
    });
    $(".outerframe").addClass("withindex");// Did we need it ? Another class name ? Can not be added directly to body like showprogress showqnumcode- etc ?
    //~ Don't know what this part done : comment before remove
    //~ var idx = $("#index");
    //~ var row = $("#index .row.current");
    //~ if(row.length)
        //~ idx.scrollTop(row.position().top - idx.height() / 2 - row.height() / 2);

}

/**
 * Update survey just when select a new language
 */
function activateLanguageChanger(){
    $('.ls-language-changer-item').on('change','select',function() {
        if($(this).data('targeturl')){
            /* We can't use get in all url combination */
            var target=$(this).data('targeturl');
            if(target.indexOf("?") >=0){
                target+="&lang="+$(this).val();
            }else{
                target+="?lang="+$(this).val();
            }
            location.href = target;
            return false;
        }else{
            if(!$(this).closest('form').length){
                if($('form#limesurvey').length==1){ // The limesurvey form exist in document, move select and button inside and click
                    $("form#limesurvey [name='lang']").remove();// Remove existing lang selector
                    $("<input type='hidden']>").attr('name','lang').val($(this).find('option:selected').val()).appendTo($('form#limesurvey'));
                    $(this).closest('.ls-language-changer-item').find(".ls-change-lang").clone().addClass("ls-js-hidden").appendTo($('form#limesurvey')).click();
                }else{// If there are no form : we can't use it, we need to create and submit. This break no-js compatibility in some page (token for example).
                    $("<form>", {
                        "class":'ls-js-hidden',
                        "html": '<input type="hidden" name="lang" value="' + $(this).find('option:selected').val() + '" />',
                        "action": target,
                        "method": 'post'
                    }).appendTo(document.body).append($("input[name='YII_CSRF_TOKEN']")).submit();
                }
            }else{
                $(this).closest('form').find("[name='lang']").not($(this)).remove();// Remove other lang
                $(this).closest('.ls-language-changer-item').find(":submit").click();
            }
        }
    });
}
