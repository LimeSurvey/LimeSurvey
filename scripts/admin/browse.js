/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This xversion may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id: browse.js 10251 2011-06-10 17:33:49Z tpartner $
*/

$(document).ready(function(){
    var lastSel = 0;
	 
    $("#responseTable")
	    .jqGrid({
	        url: siteURL + "/admin/browse/" + surveyID + "/grid",
	        editurl: siteURL + "/admin/browse/" + surveyID + "/grid",
	        datatype: "json",
	        mtype: "POST",
	        caption: "Responses",
	        width: "100%",
	        height: "100%",
	        rowNum: 25, 
	        pager: "#responsePager",
	        editable: true,
	        colNames: colNames,
	        colModel: colModel,
	        sortname: 'id',
	        sortorder: 'asc',
	        viewrecords : true,
	        gridview: true,
	        multiselect: true,
	        loadonce: true,
	        rowList: [25,50,100,250,500,1000,5000,10000],
			ondblClickRow: function(id) {
                if (lastSel != id) {
                    $('#responseTable').saveRow(lastSel); 
                    $('#responseTable').editRow(id, true); 
                    lastSel=id; 
                }
		    },
		    onSelectRow: function(id) {
			    $('#responseTable').saveRow(lastSel); 
		    }
	    })
	    .filterToolbar({
 		 	  'autosearch': true,	
 		 	  'searchOnEnter': false
 		 })
	    .navGrid("#responsePager", {
 			  'add': false,
 			  'refresh': false,
 			  'edit': false
 		 })
 		 .navButtonAdd("#responsePager", {
 		 	  'caption': 'Download marked files',
 		 	  'onClickButton': function (rows) {
 		 	  		var rows = $("#responseTable").getGridParam('selarrrow');
 		 	  		
 		 	  		if (rows.length <= 0) { 	
 		 	  			alert ("Please select some records first!");
 		 	  			return;
	 		 	  	}

					sendPost(siteURL + "/admin/browse/" + surveyID + "/grid", {
						'oper': 'downloadarchives',
						'ids': rows
					});
			  }
		 })
});


/**
    Send a post request to the server to download a file
    
    @param myaction     post action
    @param data         parameters for $_POST
    
*/
function sendPost(myaction, data)
{
	var myform = document.createElement('form');
	document.body.appendChild(myform);
	myform.action = myaction;
	myform.method = 'POST';
	
	for (var key in data) {
        var myel = document.createElement('input');
        myel.type = 'hidden';
        myel.name = key;
        myform.appendChild(myel);
        myel.value = data[key];
	}
	
	myform.submit();
}


/**
    Dowload a file from a response
    @param id       ID of the response
    @param column   Field of the text upload question
    @param filename Name of the file
*/
function getFile(id, field, filename)
{
	sendPost(siteURL + "/admin/browse/" + surveyID + "/grid", {
		'id': id,
		'fieldname': field,
		'oper': 'downloadfile',
		'filename': filename
	});	
}


/**
    Get an archive containing all the file from a response
    @param id       ID of the response
*/
function getArchive(id)
{
	sendPost(siteURL + "/admin/browse/" + surveyID + "/grid", {
		'oper': 'downloadarchive',
		'id': id
	});
}

