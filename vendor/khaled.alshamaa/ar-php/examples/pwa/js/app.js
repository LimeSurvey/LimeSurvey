function noDots(str, debug = false) {
    str = str.replace(/[ًٌٍَُِّْ]/gu, '');

    // \b not working with unicode chars in JavaScript
    str = str.replace(/ن(\s+|$)/gu, 'ں\$1');
    str = str.replace(/ك(\s+|$)/gu, 'لـﹳ\$1');

    str = str.replace(/[بتثن]/gu, 'ٮ');
    str = str.replace(/ي/gu, 'ى');

    str = str.replace(/ف/gu, 'ڡ');
    str = str.replace(/ق/gu, 'ٯ');

    str = str.replace(/ش/gu, 'س');
    str = str.replace(/غ/gu, 'ع');
    str = str.replace(/ذ/gu, 'د');
    str = str.replace(/ز/gu, 'ر');
    str = str.replace(/ض/gu, 'ص');
    str = str.replace(/ظ/gu, 'ط');
    str = str.replace(/ة/gu, 'ه');
    str = str.replace(/[جخ]/gu, 'ح');

    str = str.replace(/[أإآ]/gu, 'ا');
    str = str.replace(/ؤ/gu, 'و');
    str = str.replace(/ئ/gu, 'ى');

    return str;
}

function getNoDots() {
    var text = document.getElementById("comment").value;
    
    document.getElementById("comment").value = noDots(text);
    
    return false;
}

function copyToClipboard() {
  // get the textarea
  var copyText = document.getElementById("comment");

  // select the textarea
  copyText.select();
  copyText.setSelectionRange(0, 99999); // for mobile devices

  // copy the text inside the textarea
  document.execCommand("copy");
  
  document.getElementById("copy").select();

  // alert the copied text
  alert("تم النسخ بنجاح!");
}

function newString() {
    document.getElementById("comment").value = "";
    //document.getElementById("comment").select();
}

if ("serviceWorker" in navigator) {
  window.addEventListener("load", function() {
    navigator.serviceWorker
      .register("serviceWorker.js")
      .then(res => console.log("service worker registered"))
      .catch(err => console.log("service worker not registered", err))
  })
}