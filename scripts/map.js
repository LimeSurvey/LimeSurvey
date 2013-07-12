/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

$(document).ready(function()
{
	$(".location").each(function(index,element){
		var question = $(element).attr('name');
		var coordinates = $(element).val();
		var latLng = coordinates.split(" ");
		var question_id = question.substr(0,question.length-2);
		if ($("#mapservice_"+question_id).val()==1){
			// Google Maps
			if (gmaps[''+question] == undefined) {
				GMapsInitialize(question,latLng[0],latLng[1]);
			}
		}
		else if ($("#mapservice_"+question_id).val()==2){
			// Open Street Map
			if (osmaps[''+question]==undefined) {
				osmaps[''+question] = OSMapInitialize(question,latLng[0],latLng[1]);
			}
		}
	});
	$(document).on('focusout', ".location",function(event){
		var question = $(event.target).attr('name');
		var name = question.substr(0,question.length - 2);
		var coordinates = $(event.target).attr('value');
		var xy = coordinates.split(" ");
		var currentMap = gmaps[question];
		var marker = gmaps['marker__'+question];
		var markerLatLng = new google.maps.LatLng(xy[0],xy[1]);
		geocodeAddress(name, markerLatLng);
		marker.setPosition(markerLatLng);
		currentMap.panTo(markerLatLng);
	});
});

gmaps = new Object;
osmaps = new Object;
zoom = [];

// OSMap functions
function OSMapInitialize(question,lat,lng){

    map = new OpenLayers.Map("gmap_canvas_" + question);
    map.addLayer(new OpenLayers.Layer.OSM());
    var lonLat = new OpenLayers.LonLat(lat,lng)
          .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );
    var zoom=11;
    var markers = new OpenLayers.Layer.Markers( "Markers" );
    map.addLayer(markers);
    markers.addMarker(new OpenLayers.Marker(lonLat));
    map.setCenter (lonLat, zoom);
    return map;

}
//// Google Maps Functions (for API V3) ////

// Initialize map
function GMapsInitialize(question,lat,lng) {
	
	var name = question.substr(0,question.length - 2);
	var latlng = new google.maps.LatLng(lat, lng);
	
	var mapOptions = {
		zoom: zoom[name],
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	
	var map = new google.maps.Map(document.getElementById("gmap_canvas_" + question), mapOptions);
	gmaps[''+question] = map;
    
	var marker = new google.maps.Marker({
		position: latlng,
		draggable:true,
		map: map,
		id: 'marker__'+question
	});
	gmaps['marker__'+question] = marker;
	
	google.maps.event.addListener(map, 'rightclick', function(event) {
		marker.setPosition(event.latLng);
		map.panTo(event.latLng);
		geocodeAddress(name, event.latLng);
		$("#answer"+question).val(Math.round(event.latLng.lat()*10000)/10000 + " " + Math.round(event.latLng.lng()*10000)/10000);
	});
	
	google.maps.event.addListener(marker, 'dragend', function(event) {
		//map.panTo(event.latLng);
		geocodeAddress(name, event.latLng);
		$("#answer"+question).val(Math.round(event.latLng.lat()*10000)/10000 + " " + Math.round(event.latLng.lng()*10000)/10000);
	});
}

// Reset map when shown by conditions
function resetMap(qID) {
	var question = $('#question'+qID+' input.location').attr('name');
	var name = question.substr(0,question.length - 2);
	var coordinates = $('#question'+qID+' input.location').attr('value');
	var xy = coordinates.split(" ");
	if(gmaps[question]) {
		var currentMap = gmaps[question];
		var marker = gmaps['marker__'+question];
		var markerLatLng = new google.maps.LatLng(xy[0],xy[1]);
		marker.setPosition(markerLatLng);
		google.maps.event.trigger(currentMap, 'resize')
		currentMap.setCenter(markerLatLng);
	}
}

// Reverse geocoder
function geocodeAddress(name, pos) {
	var geocoder = new google.maps.Geocoder();
	
	var city  = '';
	var state = '';
	var country = '';
	var postal = '';
	
	geocoder.geocode({
		latLng: pos
	}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK && results[0]) {
			$(results[0].address_components).each(function(i, val) {
				if($.inArray('locality', val.types) > -1) {
					city = val.short_name;
				}
				else if($.inArray('administrative_area_level_1', val.types) > -1) {
					state = val.short_name;
				}
				else if($.inArray('country', val.types) > -1) {
					country = val.short_name;
				}
				else if($.inArray('postal_code', val.types) > -1) {
					postal = val.short_name;
				}
			});
			
			var location = (results[0].geometry.location);
		}
		getInfoToStore(name, pos.lat(), pos.lng(), city, state, country, postal);
	});
}

// Store address info
function getInfoToStore(name, lat, lng, city, state, country, postal){
    
	var boycott = $("#boycott_"+name).val();
    // 2 - city; 3 - state; 4 - country; 5 - postal
    if (boycott.indexOf("2")!=-1)
        city = '';
    if (boycott.indexOf("3")!=-1)
        state = '';
    if (boycott.indexOf("4")!=-1)
        country = '';
    if (boycott.indexOf("5")!=-1)
        postal = '';
    
    $("#answer"+name).val(lat + ';' + lng + ';' + city + ';' + state + ';' + country + ';' + postal);
}

