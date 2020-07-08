/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

var gmaps = gmaps || new Object;
var osmaps = osmaps || new Object;
var zoom = zoom || [];

$(document).on('ready pjax:scriptcomplete',function()
{
    $(".ls-answers .location").each(function(index,element){
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
        else if ($("#mapservice_"+question_id).val()==100){
            //  Maps
            if (osmaps[''+question] == undefined) {
                osmaps[''+question] = OSGeoInitialize(question,latLng);
            }
        }
    });

});


function isvalidCoord(val,type){
    if(type=='lat'){
        var min=-90;
        var max=90
    }else{
        var min=-180;
        var max=180
    }
    if (!isNaN(parseFloat(val)) && (val>min && val<=max)) {
        return true;
    } else {
        return false;
    }
}


// OSMap functions
function OSGeoInitialize(question,latLng){
        var tileServerURL = {
            OSM : "//{s}.tile.openstreetmap.org/{z}/{x}/{y}",
            HUM : "//{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}",
            CYC : "//{s}.tile.thunderforest.com/cycle/{z}/{x}/{y}",
            TRA : "//{s}.tile.thunderforest.com/transport/{z}/{x}/{y}"
        };
        var name = question.substr(0,question.length - 2);
        // tiles layers def
        // If not latLng is set the Map will center to Hamburg
        var MapOption=LSmaps[name];
        if(isNaN(MapOption.latitude) || MapOption.latitude==""){
            MapOption.latitude=53.582665;
        }
        if(isNaN(MapOption.longitude) || MapOption.longitude==""){
            MapOption.longitude=10.018924;
        }
        var mapOSM = L.tileLayer(tileServerURL.OSM+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
            attribution: 'Map data © <a href="//www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        });
        var mapCYC = L.tileLayer(tileServerURL.CYC+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
            attribution: 'Map data © <a href="//www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        });
        var mapHOT = L.layerGroup([L.tileLayer(tileServerURL.HUM+".png", {
            maxZoom: 20,
            subdomains: ["a", "b", "c"],
        }), L.tileLayer(tileServerURL+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
            attribution: 'Map data © <a href="//www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        })]);
        var mapTRA = L.layerGroup([L.tileLayer(tileServerURL.TRA+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
        }), L.tileLayer(tileServerURL+".png", {
            maxZoom: 19,
            subdomains: ["a", "b", "c"],
            attribution: 'Map data © <a href="//www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        })]);

        var baseLayers = {
            "Street Map": mapOSM,
            "Humanitarian": mapHOT,
            "Cyclemap": mapCYC,
            "Traffic Map" : mapTRA
        };
        var overlays = {
        };
        var map = L.map("map_"+name, {
            zoom:MapOption.zoomLevel,
            minZoom:1,
            center: [MapOption.latitude, MapOption.longitude] ,
            maxBounds: ([[-90, -180],[90, 180]]),
            layers: [mapOSM]
        });

        //function zoomExtent(){ // todo: restrict to rect ?
        //    map.setView([15, 15],1);
        //}

        var pt1 = latLng[0].split("@");
        var pt2 = latLng[1].split("@");

        if ((pt1.length == 2) && (pt2.length == 2)) { // is Rect
            var isRect = true;
            lat = "";
            lng = "";
            minLat = pt1[0];
            minLng = pt1[1];
            maxLat = pt2[0];
            maxLng = pt2[1];
            map.fitBounds([[minLat, minLng],[maxLat, maxLng]]);
            map.setMaxBounds([[minLat, minLng],[maxLat, maxLng]]);
            UI_update("","");
        } else { // is default marker position
            var isRect = false;
            lat = latLng[0];
            lng = latLng[1];
        }

        if (isNaN(parseFloat(lat)) || isNaN(parseFloat(lng))) {
            lat=-9999; lng=-9999;
        }

        var marker = new L.marker([lat,lng], {title:'Current Location',id:1,draggable:'true'});
        map.addLayer(marker);

        var layerControl = L.control.layers(baseLayers, overlays, {
          collapsed: true
        }).addTo(map);

        map.on('click',
            function(e) {
                var coords = L.latLng(e.latlng.lat,e.latlng.lng);
                if(isvalidCoord(coords.lat,'lat') && isvalidCoord(coords.lng,'lng')){
                    marker.setLatLng(coords);
                    UI_update(e.latlng.lat,e.latlng.lng);
                }
            }
        )

        marker.on('dragend', function(e){
                var marker = e.target;
                var position = marker.getLatLng();
                UI_update(position.lat,position.lng)
        });

        function UI_update(lat,lng){
            if (isvalidCoord(lat,'lat') && isvalidCoord(lng,'lng')) {
                $("#answer"+name).val(Math.round(lat*100000)/100000 + ";" + Math.round(lng*100000)/100000);
                $("#answer_lat"+question).val(Math.round(lat*100000)/100000).removeClass("text-danger").data('prevvalue',Math.round(lat*100000)/100000);
                $("#answer_lng"+question).val(Math.round(lng*100000)/100000).removeClass("text-danger").data('prevvalue',Math.round(lat*100000)/100000);
            } else {
                $("#answer"+name).val("");
                $("#answer_lat"+question).val("").data('prevvalue','');
                $("#answer_lng"+question).val("").data('prevvalue','');
            }
            checkconditions($("#answer"+name).val(), name, 'text', 'keyup');
        }


        $('.coords[name^='+name+']').each(function(){
            $(this).data('prevvalue',$(this).val());
        });
        $('.coords[name^='+name+']').on('blur',function(){
            if ($(this).data('prevvalue') != $(this).val()) {
                var newLat = $("#answer_lat"+question).val();
                if(newLat!=="" && !isvalidCoord(newLat,'lat')){
                    $("#answer_lat"+question).addClass("text-danger");
                }else{
                    $("#answer_lat"+question).removeClass("text-danger");
                }
                var newLng = $("#answer_lng"+question).val();
                if(newLng!="" && !isvalidCoord(newLng)){
                    $("#answer_lng"+question).addClass("text-danger");
                }else{
                    $("#answer_lng"+question).removeClass("text-danger");
                }
                if (isvalidCoord(newLat,'lat') && isvalidCoord(newLng,'lng')) {
                    $("#answer"+name).val(newLat + ";" + newLng);
                    map.setView([newLat, newLng]);
                    marker.setLatLng(L.latLng(newLat,newLng));
                    checkconditions($("#answer"+name).val(), name, 'text', 'keyup');
                }
            }
            $(this).data('prevvalue',$(this).val());
        });
        var geonamesApiUrl = "api.geonames.org";
        if(window.location.protocol=='https:'){
            /* Checked : work on 2019-03 , see #13873 */
            geonamesApiUrl = "secure.geonames.org";
        }
        $("#searchbox_"+name).autocomplete({
            serviceUrl : "//"+geonamesApiUrl+"/searchJSON",
            dataType: "json",
            paramName: 'name_startsWith',
            deferRequestBy: 500,
            params:{
                username : LSmap.geonameUser,
                featureClass : 'P',
                orderby : 'population',
                maxRows : 10,
                lang : LSmap.geonameLang
            },
            ajaxSettings:{
                beforeSend : function(jqXHR, settings) {
                    if($("#restrictToExtent_"+name).prop('checked')){
                        settings.url += "&east=" + map.getBounds().getEast() + "&west=" + map.getBounds().getWest() + "&north=" + map.getBounds().getNorth() + "&south=" + map.getBounds().getSouth();
                    }
                }
            },
            orientation: 'auto',
            minChars: 3,
            autoSelectFirst:true,
            transformResult: function(response) {
                return {
                    suggestions: $.map(response.geonames, function(geoname) {
                        return { value: geoname.name + " - " + geoname.countryName, data: { src:'geoname',lat:geoname.lat,lng:geoname.lng } };
                    })
                };
            },
            onSearchStart: function(query) {
                $( this ).prop("readonly",true);
            },
            onSearchComplete : function(query, suggestions) {
                $( this ).prop("readonly",false);
            },
            onSelect : function(suggestion) {
                if(suggestion.data.src=='geoname'){
                    map.setView([suggestion.data.lat, suggestion.data.lng], MapOption.zoomLevel);
                    marker.setLatLng([suggestion.data.lat, suggestion.data.lng]);
                    UI_update(suggestion.data.lat, suggestion.data.lng);
                }
            }
        });
        var mapQuestion = $('#question'+name.split('X')[2]);

        function resetMapTiles(mapQuestion) {

            //window.setTimeout(function(){

                if($(mapQuestion).css('display') == 'none' && $.support.leadingWhitespace) { // IE7-8 excluded (they work as-is)
                    $(mapQuestion).css({
                        'position': 'relative',
                        'left': '-9999em'
                    }).show();
                    map.invalidateSize();
                    $(mapQuestion).css({
                        'position': 'relative',
                        'left': 'auto'
                    }).hide();
                }

            //},50);
        }

        resetMapTiles(mapQuestion);

        jQuery(window).resize(function() {
            window.setTimeout(function(){
                resetMapTiles(mapQuestion);
            },5);
        });

    /* Remove the cache from search when click on restrictToExtent */
    $("#restrictToExtent_"+name).on("change",function(){
        $("#searchbox_"+name).autocomplete('clearCache');
    });
    /* if restrictToExtent is checked : remove the search cache when bound updated */
    $("#searchbox_"+name).on("viewreset",function(){ /* moveend,zoomend */
        if($("#restrictToExtent_"+name).is(":checked")){
            $("#searchbox_"+name).autocomplete('clearCache');
        }
    });
    /* reset search on focus */
    $("#searchbox_"+name).on("focusin",function(){
        $(this).val("");
    });
    return map;



}


//// Google Maps Functions (for API V3) ////
// Initialize map
function GMapsInitialize(question,lat,lng) {


    var name = question.substr(0,question.length - 2);
    if(isNaN(lat) || lat==""){
        lat=53.582665;
    }
    if(isNaN(lng) || lng==""){
        lng=10.018924;
    }
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
    } else if (osmaps[question]) {
        var currentMap = osmaps[question];
        currentMap.invalidateSize();
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



/* Placeholder hack for IE */
if (navigator.appName == "Microsoft Internet Explorer") {
  $("input").each(function () {
    if ($(this).val() === "" && $(this).attr("placeholder") !== "") {
      $(this).val($(this).attr("placeholder"));
      $(this).focus(function () {
        if ($(this).val() === $(this).attr("placeholder")) $(this).val("");
      });
      $(this).blur(function () {
        if ($(this).val() === "") $(this).val($(this).attr("placeholder"));
      });
    }
  });
}
