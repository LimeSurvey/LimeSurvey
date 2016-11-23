/*
  LS statistics PDF-worker
  Copyright (c) 2016 LimeSurvey GmbH - Markus Flür

  Released under GPL3.0
*/

/**
 * This script is designated as a webworker to create a pdf page out of our statistics
 * A normal js script qould run to death on a bigger survey, this webworker 
 */

importScripts('./jspdf.debug.js'); //this file have to be in the same folder!
"use strict";
var doc = new jsPDF()
 ,  imgObjects = []
 ,  tables = []
 ,  sizes = []
 ,  preparedPages = 0
 ,  countPages = 0
 ,  responseMethod = ""
//compile the image to pages of the pdf
 ,  compileCanvas = function(i, imgData){
        //Calculate width and height in scale to DIN A4 relation
        var width, height,
            h_max = 247, w_max = 180,
            w_relative = ((w_max/sizes[i].w)),
            h_relative = ((h_max/sizes[i].h));
        
        if(sizes[i].h < sizes[i].w){
            width = w_max;
            height = Math.floor((sizes[i].h*w_relative));
        } else {
            width = Math.floor((sizes[i].w*h_relative));
            height = h_max;
        }

        width = width>180 ? 180 : width;
        height = height>247 ? 247 : height;

        doc.addImage(imgData, 'PNG', 15,25, width, height, null, 'FAST');
    }
//increment the counter and add the html to the array
 ,  catchImgObject = function(object){
     imgObjects.push(object.image);
     sizes.push(object.sizes);
     countPages = imgObjects.length;
    }
//orchestrate the atomic processes to build the pdf
 ,  runConversion = function(key){
        var def = new Promise(function(resolve, reject){
            countPages = imgObjects.length;
            for( var i in imgObjects){
                var image = imgObjects[i];
                compileCanvas(i,image);
                preparedPages++;
                if((i+1)<countPages) 
                    doc.addPage();
            }
            resolve("all done");
        });
        return def;
    }
//return the pdf data string
 ,  returnPdf = function(){
     return doc.output('dataurlstring');
    }
//create response object
 ,  createResponse = {
     sendHtml : function(){
         return {
             success: true,
             type: "htmlincome",
             msg: "Html saved"
         }
     },
     parseHtml : function(){
         return {
             success: true,
             type: "parsehtml",
             msg: "Conversion started"
         }
     },
     checkProgress : function(){
         return {
             success: true,
             type: "progress",
             msg: Math.round(preparedPages/countPages)*100
         }
     },
     exportPdf : function(pdfDataString){
         return {
             success: true,
             type: "pdfdata",
             msg: pdfDataString
         }
     },
     unknown : function(method){
         return {
             success: false,
             type: "error", 
             error: "CRITICAL! Method unknown.",
             msg: "Unknown Method: "+method
            };
     }   
    }
 ,  parseMessage = function(messageEventData){
     switch(messageEventData.method){
         case "sendImg": catchImgObject(messageEventData.object); return createResponse[messageEventData.method](); break;
         case "parseHtml": 
            runConversion(messageEventData.key)
                .then(function(done){postMessageToHost(createResponse[messageEventData.method](returnPdf(messageEventData.key)))}); 
                return createResponse[messageEventData.method](); 
                break;
         case "checkProgress": checkProgress(messageEventData.key); return createResponse[messageEventData.method](); break;
         case "exportPdf": return createResponse[messageEventData.method](returnPdf(messageEventData.key)); break;
         default: return createResponse.unknown(messageEventData.method); break;
     }
    }
 ,  postMessageToHost = function(content){
     self.postMessage(response);
 };
//Bind the listener
 self.addEventListener('message', function(e){
     var response = parseMessage(e.data);
     postMessageToHost(response);
 });