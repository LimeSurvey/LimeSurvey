// $Id$

$(document).ready(function(){
    $("#template").change(templatechange);
    $("#template").keyup(templatechange);
});

function templatechange()
{
    $("#preview").attr('src',templaterooturl+'/'+this.value+'/preview.png');
}