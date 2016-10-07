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
