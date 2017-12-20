/*
 Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
(function(){function a(a){this.issue=a}a.prototype={issue:null};a.prototype.constructor=a;a.prototype.display=function(a,b){};a.prototype.fix=function(a,b){b&&b(this)};a.prototype.validate=function(a){return[]};a.prototype.markSelection=function(a,b){var c=a.createRange();c.setStartBefore(this.issue.element);c.setEndAfter(this.issue.element);b.selectRanges([c])};a.prototype.lang={};a.prototype.lang={};CKEDITOR.plugins.a11ychecker.quickFixes.add("nl/QuickFix",a)})();