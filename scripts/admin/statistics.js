$(document).ready(function(){
     $('#usegraph').click( function(){
        if ($('#grapherror').length>0)
        { 
            $('#grapherror').show();
            $('#usegraph').attr('checked',false);
        }
     })
     $('#viewsummaryall').click( function(){
        if ($('#viewsummaryall').attr('checked')==true)
        { 
            $('#filterchoices input[type=checkbox]').attr('checked', true);
        }
        else
        {
            $('#filterchoices input[type=checkbox]').attr('checked', false);
            
        }
     })
     $('#hidefilter').click( function(){
            $('#filterchoices').hide();
            $('#filterchoice_state').val('1');
            $('#vertical_slide2').hide();               
     })
     $('#showfilter').click( function(){
            $('#filterchoices').show();
            $('#filterchoice_state').val('');
            $('#vertical_slide2').show();               
     })
     
     for (var i in aGMapData) {
     		gMapInit("statisticsmap_" + i, aGMapData[i]);
	  }  
});

function showhidefilters(value) {
 if(value == true) {
   hide('filterchoices');
 } else {
   show('filterchoices');
 }
}

function selectCheckboxes(Div, CheckBoxName, Button)
{	
	var aDiv = document.getElementById(Div);
	var nInput = aDiv.getElementsByTagName("input");
	var Value = document.getElementById(Button).checked;
	//alert(Value);
	
	for(var i = 0; i < nInput.length; i++)
	{
		if(nInput[i].getAttribute("name")==CheckBoxName)
		nInput[i].checked = Value;
	}
}

function nographs()
{
	document.getElementById('usegraph').checked = false;
}

function gMapInit(id, data)
{
    if (!data || !data["coord"] || !data["zoom"])
    {
        return;
    }
    
    var latlng;
    if (data["coord"].length > 0) {
        var c = data["coord"][0].split(" ");
        latlng = new google.maps.LatLng(parseFloat(c[0]), parseFloat(c[1]));
    } else {   
        latLng = new google.maps.LatLng(0, 0); 	
    }
    
    var myOptions = {
        zoom: parseFloat(data["zoom"]),
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var map = new google.maps.Map(document.getElementById(id), myOptions);

    for (var i = 0; i < data["coord"].length; ++i) {
        var c = data["coord"][i].split(" ");
          	     	  
        var marker = new google.maps.Marker({    
            position: new google.maps.LatLng(parseFloat(c[0]), parseFloat(c[1])),    
            map: map    
        });  	
    }
}
