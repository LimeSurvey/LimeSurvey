// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
    $("#template").change(templatechange);
    $("#template").keyup(templatechange);
});

function templatechange()
{
    $("#preview").attr('src',templaterooturl+'/'+this.value+'/preview.png');
}