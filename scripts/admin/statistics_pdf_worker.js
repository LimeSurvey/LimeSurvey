/**
 * This script is designated as a webworker to create a pdf page out of our statistics
 * A normal js script qould run to death on a bigger survey, this webworker 
 */
import('html2canvas.js');
"use strict";
var doc = new jsPDF()
 ,  tableObjects = []
 ,  tables = []
 ,  sizes = []
 ,  preparedPages = 0
 ,  countPages = 0
 ,  responseMethod = ""
 ,  createCanvas = function(i,tableObject){
        var canvas = html2canvas(tableObject.html),
            height = tableObject.height,
            width = tableObject.width;
        sizes.push({h: height, w: width});
        tables.push(canvas);
    }
 ,  compileCanvas = function(i, canvas){
        var imgData = canvas.toDataURL("image/png");
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
 ,  catchTableObject = function(object){
     tableObjects.push(object);
     countPages = tableObjects.length;
    }
 ,  runConversion = function(key){
        var def = new Promise();
        for(var incrementor in tableObjects){
            createCanvas(incrementor, tableObjects[incrementor] );
        };
        countPages = tableImages.length;
        Promise.all(tables).then(
            function(tableImages) {
                for( var i in tableImages){
                    var canvas = tableImages[i];
                    compileCanvas(i,canvas);
                    preparedPages++;
                    if((i+1)<countPages) 
                        doc.addPage();
                });
            }
        );
    }
 ,  returnPdf = function(){
     return doc.output('dataurlstring');
    }
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
             error: "CRITICAL! Method unknown."
             msg: "Unknown Method: "+method
            };
     }   
    }
 ,  parseMessage = function(messageEventData){
     switch(messageEventData.method){
         case "sendHtml": catchTableObject(messageEventData.html); return createResponse[messageEventData.method](); break;
         case "parseHtml": runConversion(messageEventData.key); return createResponse[messageEventData.method](); break;
         case "checkProgress": checkProgress(messageEventData.key); return createResponse[messageEventData.method](); break;
         case "exportPdf": return createResponse[messageEventData.method](returnPdf(messageEventData.key)); break;
         default return createResponse.unknown(messageEventData.method);
     }
 };

 self.addEventListener('message', function(e){
     var response = parseMessage(e.data);
     self.postMessage(response);
 });