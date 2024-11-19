/*
  LS statistics PDF-worker
  Copyright (c) 2016 LimeSurvey GmbH - Markus Flür

  Released under GPL3.0
*/

"use strict";
var CreatePDF = function(){

    var doc = new jsPDF()
    ,  promiseObjects = []
    ,  sizes = []
    ,  preparedPages = 0
    ,  countPages = 0
    ,  responseMethod = ""
    //compile the image to pages of the pdf
    ,  compileCanvas = function(i, imgData){
            //Calculate width and height in scale to DIN A4 relation
            var width, height,
                h_max = 247, w_max = 180,
                w_relative = ((w_max/imgData.sizes.w)),
                h_relative = ((h_max/imgData.sizes.h));

            if(imgData.sizes.h < imgData.sizes.w){
                width = w_max;
                height = Math.floor((imgData.sizes.h*w_relative));
            } else {
                width = Math.floor((imgData.sizes.w*h_relative));
                height = h_max;
            }

            width = width>180 ? 180 : width;
            height = height>247 ? 247 : height;

            doc.addImage(imgData.image, 'PNG', 15,25, width, height, null, 'FAST');
        }
    //increment the counter and add the html to the array
    ,  addImgObject = function(object){
        var deferred = new Promise(function(resolve, reject){
            var sizes = object.sizes;
            domtoimage.toPng(object.html).then(function(image){
                    resolve({image: image, sizes: sizes});
                }, function(error){throw error;});
            });
        promiseObjects.push(deferred);
        }
    //orchestrate the atomic processes to build the pdf
    ,  runConversion = function(key){
        var def = new Promise(function(resolve, reject){
            Promise.all(promiseObjects).then(
                function(imgObjects){
                    countPages = imgObjects.length;
                    for( var i in imgObjects){
                      // Exclude __proto__
                      // see: https://stackoverflow.com/questions/1107681/javascript-hiding-prototype-methods-in-for-loop
                      if (imgObjects.hasOwnProperty(i)) {
                          var imageObject = imgObjects[i];
                          compileCanvas(i,imageObject);
                          preparedPages++;
                          if((preparedPages)<countPages)
                              doc.addPage();
                      }
                    }
                    resolve("all done");
                },
                function(array){
                    reject(array);
                });
            });
            return def;
        }
    //return the pdf data string
    ,  returnPdf = function(){
        return doc.output('dataurlstring');
        }
    //create response object
    ,  createResponse = {
        sendImg : function(){
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
    ,  action = function(method, eventData){
        switch(method){
            case "sendImg": addImgObject(eventData); return createResponse[method](); break;
            case "getParseHtmlPromise": return runConversion(eventData); break;
            case "checkProgress": return createResponse[method](); break;
            case "exportPdf": return createResponse[method](returnPdf()); break;
            default: return createResponse.unknown(method); break;
        }
    };
    return action;
};
