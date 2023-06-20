/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"nl",name:"QuickFix",callback:function(d){function b(a){d.call(this,a)}var f="January February March April May June July August September October November December".split(" ");b.prototype=new d;b.prototype.constructor=b;b.prototype.fix=function(a,b){var c=this.issue.element,e=c.getText(),d=this,e=e.replace(/(\d{1,2}[.\/-]\d{1,2}[.\/-]\d{2,4})/g,function(a){a=d.parseDate(a);return d.getFriendlyDate(a)});c.setText(e);b&&b(this)};b.prototype.parseDate=
function(a){a=a.split(/[.\-\/]+/);return{day:a[0],month:a[1],year:a[2]}};b.prototype.getFriendlyDate=function(a){var b=f[Number(a.month-1)],c=Number(a.year);0<=c&&100>c&&(c=70<=c?c+1900:c+2E3);return[Number(a.day),b,c].join(" ")};b.prototype.lang={};CKEDITOR.plugins.a11ychecker.quickFixes.add("nl/DateUnfold",b)}})})();