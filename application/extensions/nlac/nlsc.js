;(function($,excludePattern,includePattern,mergeIfXhr,resMap2Request) {

var ieVer = navigator.userAgent.match(/MSIE (\d+\.\d+);/);ieVer = ieVer && ieVer[1] ? Number(ieVer) : null;
var cont = (ieVer && ieVer<7.1) ? document.createElement("div") : null;

if (!$.nlsc)
	$.nlsc={resMap:{}};

//Normalizes the url
$.nlsc.normUrl=function(url) {
	if (!url) return null;
	if (cont) {
		cont.innerHTML = "<a href=\""+url+"\"></a>";
		url = cont.firstChild.href;
	}
	if (excludePattern && url.match(excludePattern))
		return null;
	if (includePattern && !url.match(includePattern))
		return null;
	return url.replace(/\?*&*(_=\d+)?&*$/g,"");
};

//Simple custom hash function, the same exists in NLSClientScript.php
$.nlsc.h=function(s) {
	var h = 0, i;
	for (i = 0; i < s.length; i++) {
		h = (((h<<5)-h) + s.charCodeAt(i)) & 1073741823;
	}
	return ""+h;
};

//Fetching scripts in the DOM, run once
$.nlsc.fetchMap=function() {
	if (!$.nlsc.fetched) {
		for(var url,i=0,res=$(document).find("script[src]"); i<res.length; i++) {
			if (url = this.normUrl(res[i].src ? res[i].src : res[i].href)) {
				this.resMap[url] = {
					h: $.nlsc.h(url), //hash
					d: 1 //loaded or requested by ajax
				};
			}
		}//i
		$.nlsc.fetched=1;
	}
};

//array of hashes, serialized (needed for server side)
$.nlsc.smap=function() {
	var s="[";
	for(var url in this.resMap)
		s += "\""+this.resMap[url].h+"\",";
	return s.replace(/,$/,"")+"]";
};

//jquery ajaxSetup params
var c = {
	global:true,
	beforeSend: function(xhr, opt) {
		//with the latest jQuery, beforeSend() isn't called for non-script request (at least not always)
		if (!$.nlsc.fetched) {
			$.nlsc.fetchMap();
		}//if

		if (opt.dataType!="script") {
			//hack: letting the server know what is already in the dom...
			if (mergeIfXhr)
				opt.url = resMap2Request(opt.url);
			return true;
		}
		
		//normalize url + disable no-cache random param
		var url = opt.url = $.nlsc.normUrl(opt.url);
		if (!url) return true;

		var r = $.nlsc.resMap[url];
		if (r) {
			if (r.d) //if already loaded (or at least requested by ajax), do not request again
				return false;
		} else {
			//registering the new script
			$.nlsc.resMap[url] = {h:$.nlsc.h(url),d:1};
		}

		return true;
	}//beforeSend
};//c

//removing "defer" attribute from IE scripts anyway

if (ieVer)
	c.dataFilter = function(data,type) {
		if (type && type != "html" && type != "text")
			return data;
		return data.replace(/(<script[^>]+)defer(=[^\s>]*)?/ig, "$1");
	};

$.ajaxSetup(c);

//grabbing already loaded scripts
$(document).ready(function(){$.nlsc.fetchMap()});

})(jQuery,_excludePattern_,_includePattern_,_mergeIfXhr_,_resMap2Request_);