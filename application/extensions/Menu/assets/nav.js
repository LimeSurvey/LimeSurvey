/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).on("change",'select.active',function(){
    $(this).closest('form').submit();
});
$(document).ready(function() {
    $("select.active").siblings(".jshide").hide();
});
//$(document).ready(function() {
//    $('#surveylist').bind('change', selectSurvey);
//    $('#grouplist').bind('change', selectGroup);
//    $('#questionlist').bind('change', selectQuestion);
//});

//function selectQuestion(event)
//{
//    window.location.href = LS.createUrl('questions/update', { 'id' : $(this).val()});
//}

//function selectGroup(event)
//{
//    window.location.href = LS.createUrl('groups/view', { 'id' : $(this).val()});
//}

//function selectSurvey(event)
//{
//    window.location.href = LS.createUrl('admin/survey', { 'sa' : 'view', 'surveyid' : $(this).val()});
//}
