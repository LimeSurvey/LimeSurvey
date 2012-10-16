/*
* LimeSurvey (tm)
* Copyright (C) 2012 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

$(document).ready(function(){
    $("body").delegate("#title", "keypress", function(e) {
        key=e.which;
        if ( key==null || key==0 || key==8 || key==9  || key==27 )
            return true;
        thischar=String.fromCharCode(key);
        returnvalue=(thischar==thischar.replace(/['`~!@#$%^&*()|+\-=?;:'",.<>\{\}\[\]\\\/]/gi,''));
        return returnvalue;
    });
    $("body").delegate("#title", "keyup", function() {
        $(this).val($(this).val().replace(/['`~!@#$%^&*()|+\-=?;:'",.<>\{\}\[\]\\\/]/gi,''));
    });
});