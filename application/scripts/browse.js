/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id: browse.js 9746 2011-02-06 18:04:26Z shnoulle $
*/

$(document).ready(function(){
    // Delete individual file
    $(".deleteresponse").click(function(){
        thisid=removechars($(this).attr('id')); 
        answer = confirm(strdeleteconfirm);
        if (answer==true)
        {
            $('#deleteanswer').val(thisid);
            $('.cbResponseMarker').attr('checked',false);
            $('#resulttableform').submit();
        }
    });

    // Delete all marked responses
    $("#imgDeleteMarkedResponses").click(function(){
        if ($('.cbResponseMarker:checked').size()>0)
        {
            thisid=removechars($(this).attr('id'));
            answer = confirm(strDeleteAllConfirm);
            if (answer==true)
            {
                $('#deleteanswer').val('marked');
                $('#resulttableform').submit();
            }
        }
        else
            alert(noFilesSelectedForDeletion)
    });

    // Download individual file bundle
    $(".downloadfile").click(function() {
        thisid = removechars($(this).attr('id'));
        $('#downloadfile').val(thisid);
        $('.cbResponseMarker').attr('checked', false);
        $('#resulttableform').submit();
    });

    // Download all marked files
    $("#imgDownloadMarkedFiles").click(function() {
        if ($('.cbResponseMarker:checked').size() > 0)
        {
            $('#downloadfile').val('marked');
            $('#resulttableform').submit();
        }
        else
            alert(noFilesSelectedForDnld)
    });
    
    $("#selectall").click(function(){
        $('.cbResponseMarker').attr('checked',$(this).attr('checked'));
    });
    
    $('#browseresponses').qtip({
        content:{
                text:$('#browselangpopup')
        },
        style: { name: 'cream',
                        tip:true, 
                color:'#111111', 
                border: {
                     width: 1,
                     radius: 5,
                     color: '#EADF95'}
               },  
        position: { adjust: { 
                screen: true, scroll:true },
                corner: {
                        target: 'bottomMiddle',
                        tooltip: 'topMiddle'}
                },
        show: {effect: { length:50},
               when: {
                   event:'click'
               }},
        hide: {fixed:true,
               when: {
                   event:'unfocus'
               }}
    });
    
    // Fix the heigh of the cell
    $('.browsetable td').each(function(){
    	if ($(this).text().length> 30){
    		$(this).html("<span class=\"content\" title=\""+$(this).text()+"\">"+$(this).html()+"</span>");
    	}
    });
    $('.browsetable th strong').each(function(){
    	if ($(this).text().length> 30){
    		$(this).addClass("content");
    		$(this).attr("title",$(this).text());
    	}
   });
   
   $('.browsetable td span.content').qtip();
   $('.browsetable th strong.content').qtip();
});
