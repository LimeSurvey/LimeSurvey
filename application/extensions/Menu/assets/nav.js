/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function() {
    $('#surveylist').bind('change', selectSurvey);
    $('#grouplist').bind('change', selectGroup);
    $('#questionlist').bind('change', selectQuestion);
});

function selectQuestion(event)
{
    window.location.href = LS.createUrl('questions/update', { 'id' : $(this).val()});
}

function selectGroup(event)
{
    window.location.href = LS.createUrl('groups/view', { 'id' : $(this).val()});
}

function selectSurvey(event)
{
    window.location.href = LS.createUrl('surveys/view', { 'id' : $(this).val()});
}
