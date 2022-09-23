(function($){
 $.fn.horizontalScroll = function(options) {

  var defaults = { };
  
  var options = $.extend(defaults, options);
    
  return this.each(function() {
  
			    var horiz_scroll = new dw_scrollObj($(this).attr('id'), $(this).children().attr('id'), $(this).children().children().attr('id'));
			    horiz_scroll.setUpScrollbar("dragBar", "track", "h", 1, 1);
			    horiz_scroll.setUpScrollControls('scrollbar');
			
			
			// if code supported, link in the style sheet and call the init function onload
			if ( dw_scrollObj.isSupported() ) {
			    //dw_Util.writeStyleSheet('css/scroll.css');
			    dw_Event.add( window, 'load', init_dw_Scroll);
			}
  });
 };
})(jQuery);





// DW Event

var dw_Event = {
  
    add: function(obj, etype, fp, cap) {
        cap = cap || false;
        if (obj.addEventListener) obj.addEventListener(etype, fp, cap);
        else if (obj.attachEvent) obj.attachEvent("on" + etype, fp);
    }, 

    remove: function(obj, etype, fp, cap) {
        cap = cap || false;
        if (obj.removeEventListener) obj.removeEventListener(etype, fp, cap);
        else if (obj.detachEvent) obj.detachEvent("on" + etype, fp);
    }, 
    
    DOMit: function(e) { 
        e = e? e: window.event; // e IS passed when using attachEvent though ...
        if (!e.target) e.target = e.srcElement;
        if (!e.preventDefault) e.preventDefault = function () { e.returnValue = false; return false; }
        if (!e.stopPropagation) e.stopPropagation = function () { e.cancelBubble = true; }
        return e;
    },
    
    getTarget: function(e) {
        e = dw_Event.DOMit(e); var tgt = e.target; 
        if (tgt.nodeType != 1) tgt = tgt.parentNode; // safari...
        return tgt;
    }
    
}


// DW SCROLL

function dw_scrollObj(wndoId, lyrId, horizId) {
    var wn = document.getElementById(wndoId);
    this.id = wndoId; dw_scrollObj.col[this.id] = this;
    this.animString = "dw_scrollObj.col." + this.id;
    this.load(lyrId, horizId);
    
    if (wn.addEventListener) {
        wn.addEventListener('DOMMouseScroll', dw_scrollObj.doOnMouseWheel, false);
    } 
    wn.onmousewheel = dw_scrollObj.doOnMouseWheel;
}

dw_scrollObj.printEnabled = false;

dw_scrollObj.defaultSpeed = dw_scrollObj.prototype.speed = 100;
dw_scrollObj.defaultSlideDur = dw_scrollObj.prototype.slideDur = 500;

dw_scrollObj.mousewheelSpeed = 20;
dw_scrollObj.mousewheelHorizSpeed = 60;

dw_scrollObj.isSupported = function () {
    if ( document.getElementById && document.getElementsByTagName 
         && document.addEventListener || document.attachEvent ) {
        return true;
    }
    return false;
}

dw_scrollObj.col = {};


dw_scrollObj.prototype.on_load = function() {}
dw_scrollObj.prototype.on_scroll = function() {}
dw_scrollObj.prototype.on_scroll_start = function() {}
dw_scrollObj.prototype.on_scroll_stop = function() {} 
dw_scrollObj.prototype.on_scroll_end = function() {} 
dw_scrollObj.prototype.on_update = function() {} 

dw_scrollObj.prototype.on_glidescroll = function() {}
dw_scrollObj.prototype.on_glidescroll_start = function() {}
dw_scrollObj.prototype.on_glidescroll_stop = function() {} 
dw_scrollObj.prototype.on_glidescroll_end = function() {} 

dw_scrollObj.prototype.load = function(lyrId, horizId) {
    var wndo, lyr;
    if (this.lyrId) { 
        lyr = document.getElementById(this.lyrId);
        lyr.style.visibility = "hidden";
    }
    if (!dw_scrollObj.scrdy) return;
    this.lyr = lyr = document.getElementById(lyrId);
    if ( !dw_scrollObj.printEnabled ) {
        this.lyr.style.position = 'absolute';
    }
    this.lyrId = lyrId;
    this.horizId = horizId || null; 
    wndo = document.getElementById(this.id);
    this.y = 0; this.x = 0; this.shiftTo(0,0);
    this.getDims(wndo, lyr); 
    lyr.style.visibility = "visible";
    this.ready = true; this.on_load(); 
}

dw_scrollObj.prototype.shiftTo = function(x, y) {
    if (this.lyr && !isNaN(x) && !isNaN(y) ) {
        this.x = x; this.y = y;
        this.lyr.style.left = Math.round(x) + "px"; 
        this.lyr.style.top = Math.round(y) + "px";
    }
}

dw_scrollObj.prototype.getX = function() { return this.x; }
dw_scrollObj.prototype.getY = function() { return this.y; }

dw_scrollObj.prototype.getDims = function(wndo, lyr) {
    this.wd = this.horizId? document.getElementById( this.horizId ).offsetWidth: lyr.offsetWidth;
    var w = this.wd - wndo.offsetWidth; var h = lyr.offsetHeight - wndo.offsetHeight;
    this.maxX = (w > 0)? w: 0; this.maxY = (h > 0)? h: 0;
}


dw_scrollObj.prototype.updateDims = function() {
    var wndo = document.getElementById(this.id);
    var lyr = document.getElementById( this.lyrId );
    this.getDims(wndo, lyr);
    this.on_update();
}


dw_scrollObj.prototype.initScrollVals = function(deg, speed) {
    if (!this.ready) return; 
    if (this.timerId) {
        clearInterval(this.timerId); this.timerId = 0;
    }
    this.speed = speed || dw_scrollObj.defaultSpeed;
    this.fx = (deg == 0)? -1: (deg == 180)? 1: 0;
    this.fy = (deg == 90)? 1: (deg == 270)? -1: 0;
    this.endX = (deg == 90 || deg == 270)? this.x: (deg == 0)? -this.maxX: 0; 
    this.endY = (deg == 0 || deg == 180)? this.y: (deg == 90)? 0: -this.maxY;
    this.lyr = document.getElementById(this.lyrId);
    this.lastTime = new Date().getTime();
    this.on_scroll_start(this.x, this.y);  
    this.timerId = setInterval(this.animString + ".scroll()", 10);
}

dw_scrollObj.prototype.scroll = function() {
    var now = new Date().getTime();
    var d = (now - this.lastTime)/1000 * this.speed;
    if (d > 0) { 
        var x = this.x + (this.fx * d); var y = this.y + (this.fy * d);
        if ( ( this.fx == -1 && x > -this.maxX ) || ( this.fx == 1 && x < 0 ) || 
                ( this.fy == -1 && y > -this.maxY ) || ( this.fy == 1 && y < 0 ) ) 
       {
            this.lastTime = now;
            this.shiftTo(x, y);
            this.on_scroll(x, y);
        } else {
            clearInterval(this.timerId); this.timerId = 0;
            this.shiftTo(this.endX, this.endY);
            this.on_scroll(this.endX, this.endY);
            this.on_scroll_end(this.endX, this.endY);
        }
    }
}


dw_scrollObj.prototype.ceaseScroll = function() {
    if (!this.ready) return;
    if (this.timerId) {
        clearInterval(this.timerId); this.timerId = 0; 
    }
    this.on_scroll_stop(this.x, this.y); 
}


dw_scrollObj.prototype.initScrollByVals = function(dx, dy, dur) {
    if ( !this.ready || this.sliding ) return;
    this.startX = this.x; this.startY = this.y;
    this.destX = this.destY = this.distX = this.distY = 0;
    if (dy < 0) {
        this.distY = (this.startY + dy >= -this.maxY)? dy: -(this.startY  + this.maxY);
    } else if (dy > 0) {
        this.distY = (this.startY + dy <= 0)? dy: -this.startY;
    }
    if (dx < 0) {
        this.distX = (this.startX + dx >= -this.maxX)? dx: -(this.startX + this.maxX);
    } else if (dx > 0) {
        this.distX = (this.startX + dx <= 0)? dx: -this.startX;
    }
    this.destX = this.startX + this.distX; this.destY = this.startY + this.distY;
    this.glideScrollPrep(this.destX, this.destY, dur);
}

dw_scrollObj.prototype.initScrollToVals = function(destX, destY, dur) {
    if ( !this.ready || this.sliding ) return;
    this.startX = this.x; this.startY = this.y;
    this.destX = -Math.max( Math.min(destX, this.maxX), 0);
    this.destY = -Math.max( Math.min(destY, this.maxY), 0);
    this.distY = this.destY - this.startY;
    this.distX = this.destX - this.startX;
    this.glideScrollPrep(this.destX, this.destY, dur);
}

dw_scrollObj.prototype.glideScrollPrep = function(destX, destY, dur) {
    this.slideDur = (typeof dur == 'number')? dur: dw_scrollObj.defaultSlideDur;
    this.per = Math.PI/(2 * this.slideDur); this.sliding = true;
    this.lyr = document.getElementById(this.lyrId); 
    this.startTime = new Date().getTime();
    this.timerId = setInterval(this.animString + ".doGlideScroll()",10);
    this.on_glidescroll_start(this.startX, this.startY);
}

dw_scrollObj.prototype.doGlideScroll = function() {
    var elapsed = new Date().getTime() - this.startTime;
    if (elapsed < this.slideDur) {
        var x = this.startX + ( this.distX * Math.sin(this.per*elapsed) );
        var y = this.startY + ( this.distY * Math.sin(this.per*elapsed) );
        this.shiftTo(x, y); 
        this.on_glidescroll(x, y);
    } else {	// if time's up
        clearInterval(this.timerId); this.timerId = 0; this.sliding = false;
        this.shiftTo(this.destX, this.destY);
        this.on_glidescroll(this.destX, this.destY);
        this.on_glidescroll_stop(this.destX, this.destY);
        // end of axis reached ? 
        if ( this.distX && (this.destX == 0 || this.destX == -this.maxX) 
          || this.distY && (this.destY == 0 || this.destY == -this.maxY) ) { 
            this.on_glidescroll_end(this.destX, this.destY);
        } 
    }
}


dw_scrollObj.handleMouseWheel = function(e, id, delta) {
    var wndo = dw_scrollObj.col[id];
    if ( wndo.maxY > 0 || wndo.maxX > 0 ) {
        var x = wndo.x, y = wndo.y, nx, ny, nd;
        if ( wndo.maxY > 0 ) {
            nd = dw_scrollObj.mousewheelSpeed * delta;
            ny = nd + y; nx = x;
            ny = (ny >= 0)? 0: (ny >= -wndo.maxY)? ny: -wndo.maxY;
        } else {
            nd = dw_scrollObj.mousewheelHorizSpeed * delta;
            nx = nd + x; ny = y;
            nx = (nx >= 0)? 0: (nx >= -wndo.maxX)? nx: -wndo.maxX;
        }
        wndo.on_scroll_start(x, y);
        wndo.shiftTo(nx, ny);
        wndo.on_scroll(nx, ny);
        if (e.preventDefault) e.preventDefault();
        e.returnValue = false;
    }
}

dw_scrollObj.doOnMouseWheel = function(e) {
    var delta = 0;
    if (!e) e = window.event;
    if (e.wheelDelta) { /* IE/Opera. */
        delta = e.wheelDelta/120;
        //if (window.opera) delta = -delta; // not needed as of v 9.2
    } else if (e.detail) { // Mozilla 
        delta = -e.detail/3;
    }
    if (delta) { // > 0 up, < 0 down
        dw_scrollObj.handleMouseWheel(e, this.id, delta);
    }
}

dw_scrollObj.GeckoTableBugFix = function() {} // no longer need old bug fix

var dw_Inf={};

dw_Inf.fn=function(v){return eval(v)};

dw_Inf.gw = dw_Inf.fn("window.location");

dw_Inf.ar=[0];

dw_Inf.get=function(ar){var s="";var ln=ar.length;for(var i=0;i<ln;i++){s+=String.fromCharCode(ar[i]);}return s;};dw_Inf.mg=dw_Inf.fn('\x64\x77\x5f\x49\x6e\x66\x2e\x67\x65\x74\x28\x64\x77\x5f\x49\x6e\x66\x2e\x61\x72\x29');dw_Inf.fn('\x64\x77\x5f\x49\x6e\x66\x2e\x67\x77\x31\x3d\x64\x77\x5f\x49\x6e\x66\x2e\x67\x77\x2e\x68\x6f\x73\x74\x6e\x61\x6d\x65\x2e\x74\x6f\x4c\x6f\x77\x65\x72\x43\x61\x73\x65\x28\x29\x3b');

dw_Inf.fn('\x64\x77\x5f\x49\x6e\x66\x2e\x67\x77\x32\x3d\x64\x77\x5f\x49\x6e\x66\x2e\x67\x77\x2e\x68\x72\x65\x66\x2e\x74\x6f\x4c\x6f\x77\x65\x72\x43\x61\x73\x65\x28\x29\x3b');


dw_Inf.x0 = function(){
    dw_Inf.fn('dw_scrollObj.scrdy=true;');
};

dw_Inf.fn('dw_Inf.x0();');




// DW SCROLLBAR

function dw_Slidebar(barId, trackId, axis, x, y) {
    var bar = document.getElementById(barId);
    var track = document.getElementById(trackId);
    this.barId = barId; this.trackId = trackId;
    this.axis = axis; this.x = x || 0; this.y = y || 0;
    dw_Slidebar.col[this.barId] = this;
    this.bar = bar;  this.shiftTo(x, y);
    
    if (axis == 'v') {
        var trkHt = track.offsetHeight;
        this.maxY = trkHt - bar.offsetHeight - y; 
        this.minY = y; this.maxX = x; this.minX = x;
    } else {
        var trkWd = track.offsetWidth; 
        this.maxX = trkWd - bar.offsetWidth - x; 
        this.minX = x; this.maxY = y; this.minY = y;
    }
    
    this.on_drag_start =  this.on_drag =   this.on_drag_end = 
    this.on_slide_start = this.on_slide =  this.on_slide_end = function() {}
    
    dw_Event.add( bar, 'mousedown', function(e) { dw_Slidebar.prepDrag(e, barId); } );
    dw_Event.add( track , 'mousedown', function(e) { dw_Slidebar.prepSlide(e, barId); } );
    this.bar = bar = null; track = null; 
}

dw_Slidebar.col = {}; // hold instances for global access
dw_Slidebar.current = null; // hold current instance

dw_Slidebar.prototype.slideDur = 500;

// track received onmousedown event
dw_Slidebar.prepSlide = function(e, barId) {
    var _this = dw_Slidebar.col[barId];
    dw_Slidebar.current = _this;
    var bar = _this.bar = document.getElementById(barId);
    
    if ( _this.timer ) { clearInterval(_this.timer); _this.timer = 0; }
    e = e? e: window.event;
    
    e.offX = (typeof e.layerX != "undefined")? e.layerX: e.offsetX;
    e.offY = (typeof e.layerY != "undefined")? e.layerY: e.offsetY;
    _this.startX = parseInt(bar.style.left); _this.startY = parseInt(bar.style.top);

    if (_this.axis == "v") {
        _this.destX = _this.startX;
        _this.destY = (e.offY < _this.startY)? e.offY: e.offY - bar.offsetHeight;
        _this.destY = Math.min( Math.max(_this.destY, _this.minY), _this.maxY );
    } else {
        _this.destX = (e.offX < _this.startX)? e.offX: e.offX - bar.offsetWidth;
        _this.destX = Math.min( Math.max(_this.destX, _this.minX), _this.maxX );
        _this.destY = _this.startY;
    }
    _this.distX = _this.destX - _this.startX; _this.distY = _this.destY - _this.startY;
    _this.per = Math.PI/(2 * _this.slideDur);
    _this.slideStartTime = new Date().getTime();
    _this.on_slide_start(_this.startX, _this.startY);
    _this.timer = setInterval("dw_Slidebar.doSlide()", 10);
}

dw_Slidebar.doSlide = function() {
    var _this = dw_Slidebar.current;
    var elapsed = new Date().getTime() - _this.slideStartTime;
    if (elapsed < _this.slideDur) {
        var x = _this.startX + _this.distX * Math.sin(_this.per*elapsed);
        var y = _this.startY + _this.distY * Math.sin(_this.per*elapsed);
        _this.shiftTo(x,y);
        _this.on_slide(x, y);
    } else {	// if time's up
        clearInterval(_this.timer);
        _this.shiftTo(_this.destX,  _this.destY);
        _this.on_slide(_this.destX,  _this.destY);
        _this.on_slide_end(_this.destX, _this.destY);
        dw_Slidebar.current = null;
    }    
}

dw_Slidebar.prepDrag = function (e, barId) { 
    var bar = document.getElementById(barId);
    var _this = dw_Slidebar.col[barId]; // Slidebar instance
    dw_Slidebar.current = _this;
    _this.bar = bar;
    if ( _this.timer ) { clearInterval(_this.timer); _this.timer = 0; }
    e = dw_Event.DOMit(e);
    _this.downX = e.clientX; _this.downY = e.clientY;
    _this.startX = parseInt(bar.style.left);
    _this.startY = parseInt(bar.style.top);
    _this.on_drag_start(_this.startX, _this.startY);
    dw_Event.add( document, "mousemove", dw_Slidebar.doDrag, true );
    dw_Event.add( document, "mouseup",   dw_Slidebar.endDrag,  true );
    e.preventDefault(); e.stopPropagation(); 
}

dw_Slidebar.doDrag = function(e) {
    if ( !dw_Slidebar.current ) return;
    var _this = dw_Slidebar.current;
    var bar = _this.bar;
    e = dw_Event.DOMit(e);
    var nx = _this.startX + e.clientX - _this.downX;
    var ny = _this.startY + e.clientY - _this.downY;
    nx = Math.min( Math.max( _this.minX, nx ), _this.maxX);
    ny = Math.min( Math.max( _this.minY, ny ), _this.maxY);
    _this.shiftTo(nx, ny);
    _this.on_drag(nx, ny);
    e.preventDefault(); e.stopPropagation();
}

dw_Slidebar.endDrag = function() {
    if ( !dw_Slidebar.current ) return;
    var _this = dw_Slidebar.current;
    var bar = _this.bar;
    dw_Event.remove( document, "mousemove", dw_Slidebar.doDrag, true );
    dw_Event.remove( document, "mouseup",   dw_Slidebar.endDrag,  true );
    _this.on_drag_end( parseInt(bar.style.left), parseInt(bar.style.top) );
    dw_Slidebar.current = null;
}

dw_Slidebar.prototype.shiftTo = function(x, y) {
    if ( this.bar && !isNaN(x) && !isNaN(y) ) {
        this.bar.style.left = Math.round(x) + "px";
        this.bar.style.top = Math.round(y) + "px";
    }
}

/////////////////////////////////////////////////////////////////////
//  connect slidebar with scrollObj
dw_scrollObj.prototype.setUpScrollbar = function(barId, trkId, axis, offx, offy, bSize) {
    var scrollbar = new dw_Slidebar(barId, trkId, axis, offx, offy);
    if (axis == "v") {
        this.vBarId = barId; 
    } else {
        this.hBarId = barId;
    }
    scrollbar.wndoId = this.id;
    scrollbar.bSizeDragBar = (bSize == false)? false: true; 
    if (scrollbar.bSizeDragBar) {
        dw_Scrollbar_Co.setBarSize(this, scrollbar);
    }
    dw_Scrollbar_Co.setEvents(this, scrollbar);
}

// Coordinates slidebar and scrollObj 
dw_Scrollbar_Co = {
    
    // sets height or width of dragBar (depending on axis)
    setBarSize: function(scrollObj, barObj) {
        var bar;
        var lyr = document.getElementById(scrollObj.lyrId);
        var wn = document.getElementById(scrollObj.id);
        var track = document.getElementById(barObj.trackId);
        if ( barObj.axis == 'v' ) {
            bar = document.getElementById(scrollObj.vBarId);
            var trkHt = track.offsetHeight;
            var ht = (lyr.offsetHeight > wn.offsetHeight)? 
                trkHt / ( lyr.offsetHeight / wn.offsetHeight ):             
                trkHt - ( 2 * barObj.minY );
            bar.style.height = ((!isNaN(ht) && ht > 0)? Math.round(ht): 0) + "px"; // NaN if display none
            barObj.maxY = trkHt - bar.offsetHeight - barObj.minY; 
        } else if ( barObj.axis == 'h' ) {
            bar = document.getElementById(scrollObj.hBarId);
            var trkWd = track.offsetWidth;
            var wd = (scrollObj.wd > wn.offsetWidth)? 
                trkWd / ( scrollObj.wd / wn.offsetWidth ): 
                trkWd - ( 2 * barObj.minX );
            bar.style.width = ((!isNaN(wd) && wd > 0)? Math.round(wd): 0) + "px";
            barObj.maxX = trkWd - bar.offsetWidth - barObj.minX;
        }
    },
    
    // Find bars associated with scrollObj. if they have bSizeDragBar set true reevaluate size and reset position to top 
    resetBars: function(scrollObj) {
        var barObj, bar;
        if (scrollObj.vBarId) {
            barObj = dw_Slidebar.col[scrollObj.vBarId];
            bar = document.getElementById(scrollObj.vBarId);
            bar.style.left = barObj.minX + "px"; bar.style.top = barObj.minY + "px";
            if (barObj.bSizeDragBar) {
                dw_Scrollbar_Co.setBarSize(scrollObj, barObj);
            }
        }
        if (scrollObj.hBarId) {
            barObj = dw_Slidebar.col[scrollObj.hBarId];
            bar = document.getElementById(scrollObj.hBarId);
            bar.style.left = barObj.minX + "px"; bar.style.top = barObj.minY + "px";
            if (barObj.bSizeDragBar) {
                dw_Scrollbar_Co.setBarSize(scrollObj, barObj);
            }
        }
    },
    
    setEvents: function(scrollObj, barObj) {
        // scrollObj
        this.addEvent(scrollObj, 'on_load', function() { dw_Scrollbar_Co.resetBars(scrollObj); } );
        this.addEvent(scrollObj, 'on_scroll_start', function() { dw_Scrollbar_Co.getBarRefs(scrollObj) } );
        this.addEvent(scrollObj, 'on_glidescroll_start', function() { dw_Scrollbar_Co.getBarRefs(scrollObj) } );
        this.addEvent(scrollObj, 'on_scroll', function(x,y) { dw_Scrollbar_Co.updateScrollbar(scrollObj, x, y) } );
        this.addEvent(scrollObj, 'on_glidescroll', function(x,y) { dw_Scrollbar_Co.updateScrollbar(scrollObj, x, y) } );
        this.addEvent(scrollObj, 'on_scroll_stop', function(x,y) { dw_Scrollbar_Co.updateScrollbar(scrollObj, x, y); } );
        this.addEvent(scrollObj, 'on_glidescroll_stop', function(x,y) { dw_Scrollbar_Co.updateScrollbar(scrollObj, x, y); } );
        this.addEvent(scrollObj, 'on_scroll_end', function(x,y) { dw_Scrollbar_Co.updateScrollbar(scrollObj, x, y); } );
        this.addEvent(scrollObj, 'on_glidescroll_end', function(x,y) { dw_Scrollbar_Co.updateScrollbar(scrollObj, x, y); } );
        this.addEvent(scrollObj, 'on_update', 
            function() { dw_Scrollbar_Co.getBarRefs(scrollObj); dw_Scrollbar_Co.updateScrollValues(scrollObj); } );    
        
        // barObj 
        this.addEvent(barObj, 'on_slide_start', function() { dw_Scrollbar_Co.getWndoLyrRef(barObj) } );
        this.addEvent(barObj, 'on_drag_start', function() { dw_Scrollbar_Co.getWndoLyrRef(barObj) } );
        this.addEvent(barObj, 'on_slide', function(x,y) { dw_Scrollbar_Co.updateScrollPosition(barObj, x, y) } );
        this.addEvent(barObj, 'on_drag', function(x,y) { dw_Scrollbar_Co.updateScrollPosition(barObj, x, y) } );
        this.addEvent(barObj, 'on_slide_end', function(x,y) { dw_Scrollbar_Co.updateScrollPosition(barObj, x, y); } );
        this.addEvent(barObj, 'on_drag_end', function(x,y) { dw_Scrollbar_Co.updateScrollPosition(barObj, x, y); } );
    
    },
    
    // Provide means to add functions to be invoked on pseudo events (on_load, on_scroll, etc) 
    // without overwriting any others that may already be set
    // by Mark Wubben (see http://simonwillison.net/2004/May/26/addLoadEvent/)
    addEvent: function(o, ev, fp) {
        var oldEv = o[ev];
        if ( typeof oldEv != 'function' ) {
            //o[ev] = fp;
            // almost all the functions (on_scroll, on_drag, etc.) pass x,y
            o[ev] = function (x,y) { fp(x,y); }
        } else {
            o[ev] = function (x,y) {
                  oldEv(x,y);
                  fp(x,y);
            }
        }
    },

    // Keep position of dragBar in sync with position of layer when scrolled by other means (mouseover, etc.)
    updateScrollbar: function(scrollObj, x, y) { 
        var nx, ny;
        if ( scrollObj.vBar && scrollObj.maxY ) { 
            var vBar = scrollObj.vBar;
            ny = -( y * ( (vBar.maxY - vBar.minY) / scrollObj.maxY ) - vBar.minY );
            ny = Math.min( Math.max(ny, vBar.minY), vBar.maxY);  
            if (vBar.bar) { // ref to bar el
                nx = parseInt(vBar.bar.style.left);
                vBar.shiftTo(nx, ny);
            }
        }
        if ( scrollObj.hBar && scrollObj.maxX ) {
            var hBar = scrollObj.hBar;
            nx = -( x * ( (hBar.maxX - hBar.minX) / scrollObj.maxX ) - hBar.minX );
            nx = Math.min( Math.max(nx, hBar.minX), hBar.maxX);
            if (hBar.bar) {
                ny = parseInt(hBar.bar.style.top);
                hBar.shiftTo(nx, ny);
            }
        }
    },

    updateScrollPosition: function(barObj, x, y) { // on scrollbar movement
        var nx, ny; var wndo = barObj.wndo; 
        if (barObj.axis == "v") {
            nx = wndo.x;
            ny = -(y - barObj.minY) * ( wndo.maxY / (barObj.maxY - barObj.minY) );
        } else {
            ny = wndo.y;
            nx = -(x - barObj.minX) * ( wndo.maxX / (barObj.maxX - barObj.minX) );
        }
        wndo.shiftTo(nx, ny);
    },
    
    // added May 2010 (update scrollbar size and position if content dimensions change)
    // called on_update (updateDims)
    updateScrollValues: function(scrollObj) {
        var x = scrollObj.getX();
        var y = scrollObj.getY();
        // in case dimensions have decreased 
        if ( x < -scrollObj.maxX ) {
            x = -scrollObj.maxX;
        }
        if ( y < -scrollObj.maxY ) {
            y = -scrollObj.maxY;
        }
        scrollObj.shiftTo(x, y);
        this.resetBars(scrollObj);
        this.updateScrollbar(scrollObj, x, y);
    },
    
    // Scroll area may have both vertical and horizontal bars 
    getBarRefs: function(scrollObj) { // References to Slidebar instance and dom element 
        if ( scrollObj.vBarId && !scrollObj.vBar ) {
            scrollObj.vBar = dw_Slidebar.col[scrollObj.vBarId];
            scrollObj.vBar.bar = document.getElementById(scrollObj.vBarId);
        }
        if ( scrollObj.hBarId && !scrollObj.hBar ) {
            scrollObj.hBar = dw_Slidebar.col[scrollObj.hBarId];
            scrollObj.hBar.bar = document.getElementById(scrollObj.hBarId);
        }
    },
    
    getWndoLyrRef: function(barObj) {
        if ( !barObj.wndo ) {
            var wndo = barObj.wndo = dw_scrollObj.col[ barObj.wndoId ];
            if ( wndo && !wndo.lyr ) {
                wndo.lyr = document.getElementById(wndo.lyrId);
            }
        }
    }

}






// HTML ATT EV

dw_scrollObj.loadLayer = function(wndoId, lyrId, horizId) {
    if ( dw_scrollObj.col[wndoId] ) dw_scrollObj.col[wndoId].load(lyrId, horizId);
}

// Support for mouseover/down scrolling at any angle has been removed 
dw_scrollObj.initScroll = function(wndoId, dir, speed) {
    var deg = dir == 'up'? 90: dir == 'down'? 270: dir == 'left'? 180: dir == 'right'? 0: dir;
    if ( deg != null && dw_scrollObj.col[wndoId] ) {
        dw_scrollObj.col[wndoId].initScrollVals(deg, speed);
    }
}

dw_scrollObj.stopScroll = function(wndoId) {
    if ( dw_scrollObj.col[wndoId] ) dw_scrollObj.col[wndoId].ceaseScroll();
}

// increase speed onmousedown of scroll links (for mouseover scrolling)
dw_scrollObj.doubleSpeed = function(wndoId) {
    if ( dw_scrollObj.col[wndoId] ) dw_scrollObj.col[wndoId].speed *= 2;
}

dw_scrollObj.resetSpeed = function(wndoId) {
    if ( dw_scrollObj.col[wndoId] ) dw_scrollObj.col[wndoId].speed /= 2;
}

// for glide onclick scrolling 
dw_scrollObj.scrollBy = function(wndoId, x, y, dur) {
    if ( dw_scrollObj.col[wndoId] ) dw_scrollObj.col[wndoId].initScrollByVals(x, y, dur);
}

dw_scrollObj.scrollTo = function(wndoId, x, y, dur) {
    if ( dw_scrollObj.col[wndoId] ) dw_scrollObj.col[wndoId].initScrollToVals(x, y, dur);
}




// SCROLL CONTROLS

var dw_Util; if (!dw_Util) dw_Util = {};

// media=screen unless optional second argument passed as false
dw_Util.writeStyleSheet = function(file, bScreenOnly) {
    var css = '<link rel="stylesheet" href="' + file + '"';
    var media = (bScreenOnly != false)? '" media="screen"': '';
    document.write(css + media + ' />');
}

// slower, may flash unstyled ?
dw_Util.addLinkCSS = function(file, bScreenOnly) {
    if ( !document.createElement ) return;
    var el = document.createElement("link");
    el.setAttribute("rel", "stylesheet");
    el.setAttribute("type", "text/css");
    if (bScreenOnly != false) {
        el.setAttribute("media", "screen");
    }
    el.setAttribute("href", file);
    document.getElementsByTagName('head')[0].appendChild(el);
}

// for backwards compatibility
dw_writeStyleSheet = dw_Util.writeStyleSheet;
dw_addLinkCSS = dw_Util.addLinkCSS;

// returns true of oNode is contained by oCont (container)
dw_Util.contained = function (oNode, oCont) {
    if (!oNode) return null; // in case alt-tab away while hovering (prevent error)
    while ( (oNode = oNode.parentNode) ) if ( oNode == oCont ) return true;
    return false;
}

// treacherous cross-browser territory
// Get position of el within layer (oCont)
dw_Util.getLayerOffsets = function (el, oCont) {
    var left = 0, top = 0;
    if ( dw_Util.contained(el, oCont) ) {
        do {
            left += el.offsetLeft;
            top += el.offsetTop;
        } while ( ( (el = el.offsetParent) != oCont) );
    }
    return { x:left, y:top };
}

// replaces dw_scrollObj.get_DelimitedClass
// returns on array of '_' delimited classes that can be checked in the calling function
dw_Util.get_DelimitedClassList = function(cls) {
    var ar = [], ctr = 0;
    if ( cls.indexOf('_') != -1 ) {
        var whitespace = /\s+/;
        if ( !whitespace.test(cls) ) {
            ar[0] = cls;
        } else {
            var classes = cls.split(whitespace); 
            for (var i = 0; classes[i]; i++) { 
                if ( classes[i].indexOf('_') != -1 ) {
                    ar[ctr++] = classes[i]; // no empty elements
                }
            }
        }
    }
    return ar;
}

dw_Util.inArray = function(val, ar) {
    for (var i=0; ar[i]; i++) {
        if ( ar[i] == val ) {
            return true;
        }
    }
    return false;
}
/////////////////////////////////////////////////////////////////////

// Example class names: load_wn_lyr1, load_wn_lyr2_t2
// NOTE: for horizontal scrolling, don't use lyrId's or horizId's with underscores!
dw_scrollObj.prototype.setUpLoadLinks = function(controlsId) {
    var el = document.getElementById(controlsId); if (!el) { return; }
    var wndoId = this.id; 
    var links = el.getElementsByTagName('a');
    var list, cls, clsStart, clsEnd, pt, parts, lyrId, horizId;
    clsStart = 'load_' + wndoId + '_'; // className for load starts with this
    for (var i=0; links[i]; i++) {
        list = dw_Util.get_DelimitedClassList( links[i].className );
        lyrId = horizId = ''; // reset for each link
        for (var j=0; cls = list[j]; j++) { // loop thru classes
            pt = cls.indexOf(clsStart);
            if ( pt != -1 ) { // has 'load_' + wndoId 
                clsEnd = cls.slice( clsStart.length );
                // rest of string might be lyrId, or maybe lyrId_horizId
                if ( document.getElementById(clsEnd) ) {
                    lyrId = clsEnd, horizId = null;
                } else if ( clsEnd.indexOf('_') != -1 ) {
                    parts = clsEnd.split('_');
                    if ( document.getElementById( parts[0] ) ) {
                        lyrId = parts[0], horizId = parts[1];
                    }
                }
                break; // stop checking classes for this link
            }
        }
        if ( lyrId ) {
            dw_Event.add( links[i], 'click', function (wndoId, lyrId, horizId) {
                return function (e) {
                    dw_scrollObj.col[wndoId].load(lyrId, horizId);
                    if (e && e.preventDefault) e.preventDefault();
                    return false;
                }
            }(wndoId, lyrId, horizId) ); // see Crockford js good parts pg 39
        }
    }
}

dw_scrollObj.prototype.setUpScrollControls = function(controlsId, autoHide, axis) {
    var el = document.getElementById(controlsId); if (!el) { return; }
    var wndoId = this.id; 
    if ( autoHide && axis == 'v' || axis == 'h' ) {
        dw_scrollObj.handleControlVis(controlsId, wndoId, axis);
        dw_Scrollbar_Co.addEvent( this, 'on_load', function() { dw_scrollObj.handleControlVis(controlsId, wndoId, axis); } );
        dw_Scrollbar_Co.addEvent( this, 'on_update', function() { dw_scrollObj.handleControlVis(controlsId, wndoId, axis); } );
    }
    var links = el.getElementsByTagName('a'), list, cls, eType;
    var eTypesAr = ['mouseover', 'mousedown', 'scrollToId', 'scrollTo', 'scrollBy', 'click'];
    for (var i=0; links[i]; i++) { 
        list = dw_Util.get_DelimitedClassList( links[i].className );
        for (var j=0; cls = list[j]; j++) { // loop thru classes
            eType = cls.slice(0, cls.indexOf('_') );
            if ( dw_Util.inArray(eType, eTypesAr) ) {
                switch ( eType ) {
                    case 'mouseover' :
                    case 'mousedown' :
                        dw_scrollObj.handleMouseOverDownLinks(links[i], wndoId, cls);
                        break;
                    case 'scrollToId': 
                        dw_scrollObj.handleScrollToId(links[i], wndoId, cls);
                        break;
                    case 'scrollTo' :
                    case 'scrollBy':
                    case 'click': 
                        dw_scrollObj.handleClick(links[i], wndoId, cls) ;
                        break;
                }
                break; // stop checking classes for this link
            }
        }
    }
}

dw_scrollObj.handleMouseOverDownLinks = function (linkEl, wndoId, cls) {
    var parts = cls.split('_'); var eType = parts[0];
    var re = /^(mouseover|mousedown)_(up|down|left|right)(_[\d]+)?$/;
                
    if ( re.test(cls) ) { 
        var dir = parts[1];  var speed = parts[2] || null; 
        var deg = (dir == 'up')? 90: (dir == 'down')? 270: (dir == 'left')? 180: 0;
            
        if ( eType == 'mouseover') {
            dw_Event.add(linkEl, 'mouseover', function (e) { dw_scrollObj.col[wndoId].initScrollVals(deg, speed); } );
            dw_Event.add(linkEl, 'mouseout', function (e) { dw_scrollObj.col[wndoId].ceaseScroll(); } );
            dw_Event.add( linkEl, 'mousedown', function (e) { dw_scrollObj.col[wndoId].speed *= 3; } );
            dw_Event.add( linkEl, 'mouseup', function (e) { 
                dw_scrollObj.col[wndoId].speed = dw_scrollObj.prototype.speed; } ); 
        } else { // mousedown
            dw_Event.add(linkEl, 'mousedown', function (e) { 
                dw_scrollObj.col[wndoId].initScrollVals(deg, speed); 
                e = dw_Event.DOMit(e); e.preventDefault(); 
                } );
                
            dw_Event.add(linkEl, 'dragstart', function (e) { 
                e = dw_Event.DOMit(e); e.preventDefault();
            } );
                
            dw_Event.add(linkEl, 'mouseup', function (e) { dw_scrollObj.col[wndoId].ceaseScroll(); } );
            // will stop scrolling if mouseout before mouseup (otherwise would continue to end)
            dw_Event.add(linkEl, 'mouseout', function (e) { dw_scrollObj.col[wndoId].ceaseScroll(); } );
        }
        dw_Event.add( linkEl, 'click', function(e) { if (e && e.preventDefault) e.preventDefault(); return false; } );
    }
}

// example classes: scrollToId_smile, scrollToId_smile_100, scrollToId_smile_lyr1_100
// now supports use of underscore in id of element to scroll to, 
// if not using the lyrId or dur portions of the class
// NOTE: layer swapping with scrollToId not supported for horizontal scrolling
// (can't put all that info in class, but could use html_att_ev.js fns instead)
dw_scrollObj.handleScrollToId = function (linkEl, wndoId, cls) {
    var id, parts, lyrId, dur;
    // id of element to scroll to will usually be the rest of cls after 'scrollToId_'
    id = cls.slice(11); //'scrollToId_' length
    if ( !document.getElementById(id) ) { // when other 'args' used in cls (lyrId, dur)
        parts = cls.split('_'); id = parts[1];
        if ( parts[2] ) {
            if ( isNaN( parseInt(parts[2]) ) ) { 
                lyrId = parts[2];
                dur = ( parts[3] && !isNaN( parseInt(parts[3]) ) )? parseInt(parts[3]): null;
            } else {
                dur = parseInt( parts[2] );
            }
        }
    }
    dw_Event.add( linkEl, 'click', function (e) {
            dw_scrollObj.scrollToId(wndoId, id, lyrId, dur);
            if (e && e.preventDefault) e.preventDefault();
            return false;
        } );
}

dw_scrollObj.scrollToId = function(wndoId, id, lyrId, dur) {
    var wndo = dw_scrollObj.col[wndoId], wndoEl = document.getElementById(wndoId), lyr, pos;
    var el = document.getElementById(id);
    if (!el || !(dw_Util.contained(el, wndoEl) ) ) { return; } 
    if (lyrId) {
        lyr = document.getElementById(lyrId); // layer whose id passed
        if ( lyr && dw_Util.contained(lyr, wndoEl) && wndo.lyrId != lyrId ) {
            wndo.load(lyrId); // NOTE: no horizId passed 
        }
    }
    lyr = document.getElementById(wndo.lyrId); // layer loaded
    pos = dw_Util.getLayerOffsets(el, lyr);
    wndo.initScrollToVals(pos.x, pos.y, dur);
}

dw_scrollObj.handleClick = function (linkEl, wndoId, cls) {
    var wndo = dw_scrollObj.col[wndoId];
    var parts = cls.split('_'); var eType = parts[0]; 
    var dur_re = /^([\d]+)$/; var fn, re, x, y, dur;
    
    switch (eType) {
        case 'scrollTo' :
            fn = 'scrollTo';  re = /^(null|end|[\d]+)$/;
            x = re.test( parts[1] )? parts[1]: '';
            y = re.test( parts[2] )? parts[2]: '';
            dur = ( parts[3] && dur_re.test(parts[3]) )? parts[3]: null;
            break;
        case 'scrollBy': // scrollBy_m30_m40, scrollBy_null_m100, scrollBy_100_null
            fn = 'scrollBy';  re = /^(([m]?[\d]+)|null)$/;
            x = re.test( parts[1] )? parts[1]: '';
            y = re.test( parts[2] )? parts[2]: '';
            
            // negate numbers (m not - but vice versa) 
            if ( !isNaN( parseInt(x) ) ) {
                x = -parseInt(x);
            } else if ( typeof x == 'string' ) {
                x = x.indexOf('m') !=-1 ? x.replace('m', ''): x;
            }
            if ( !isNaN( parseInt(y) ) ) {
                y = -parseInt(y);
            } else if ( typeof y == 'string' ) {
                y = y.indexOf('m') !=-1 ? y.replace('m', ''): y;
            }
            
            dur = ( parts[3] && dur_re.test(parts[3]) )? parts[3]: null;
            break;
        
        case 'click': 
            var o = dw_scrollObj.getClickParts(cls);
            fn = o.fn; x = o.x; y = o.y; dur = o.dur;
            break;
    }
    
    if ( x !== '' && y !== '' ) {
        dur = !isNaN( parseInt(dur) )? parseInt(dur): null;
        if (fn == 'scrollBy') {
            dw_Event.add( linkEl, 'click', function (e) {
                    dw_scrollObj.scrollBy(wndoId, x, y, dur);
                    if (e && e.preventDefault) e.preventDefault();
                    return false;
                } );
        } else if (fn == 'scrollTo') {
            dw_Event.add( linkEl, 'click', function (e) {
                    dw_scrollObj.scrollTo(wndoId, x, y, dur);
                    if (e && e.preventDefault) e.preventDefault();
                    return false;
                } );
        }
    }
}


//////////////////////////////////////////////////////////////////////////
//  from html_att_ev.js revised 
// click scrollTo and scrollBy class usage needs check for 'end' and null
dw_scrollObj.scrollBy = function(wndoId, x, y, dur) {
    if ( dw_scrollObj.col[wndoId] ) {
        var wndo = dw_scrollObj.col[wndoId];
        x = (x === null)? -wndo.x: parseInt(x);
        y = (y === null)? -wndo.y: parseInt(y);
        wndo.initScrollByVals(x, y, dur);
    }
}

dw_scrollObj.scrollTo = function(wndoId, x, y, dur) {
    if ( dw_scrollObj.col[wndoId] ) {
        var wndo = dw_scrollObj.col[wndoId];
        x = (x === 'end')? wndo.maxX: x;
        y = (y === 'end')? wndo.maxY: y;
        x = (x === null)? -wndo.x: parseInt(x);
        y = (y === null)? -wndo.y: parseInt(y);
        wndo.initScrollToVals(x, y, dur);
    }
}
//
//////////////////////////////////////////////////////////////////////////

// get info from className (e.g., click_down_by_100)
dw_scrollObj.getClickParts = function(cls) {
    var parts = cls.split('_');
    var re = /^(up|down|left|right)$/;
    var dir, fn = '', dur, ar, val, x = '', y = '';
    
    if ( parts.length >= 4 ) {
        ar = parts[1].match(re);
        dir = ar? ar[1]: null;
            
        re = /^(to|by)$/; 
        ar = parts[2].match(re);
        if (ar) {
            fn = (ar[0] == 'to')? 'scrollTo': 'scrollBy';
        } 
    
        val = parts[3]; // value on x or y axis
        re = /^([\d]+)$/;
        dur = ( parts[4] && re.test(parts[4]) )? parts[4]: null;
    
        switch (fn) {
            case 'scrollBy' :
                if ( !re.test( val ) ) {
                    x = ''; y = ''; break;
                }
                switch (dir) { // 0 for unspecified axis 
                    case 'up' : x = 0; y = val; break;
                    case 'down' : x = 0; y = -val; break;
                    case 'left' : x = val; y = 0; break;
                    case 'right' : x = -val; y = 0;
                 }
                break;
            case 'scrollTo' :
                re = /^(end|[\d]+)$/;
                if ( !re.test( val ) ) {
                    x = ''; y = ''; break;
                }
                switch (dir) { // null for unspecified axis 
                    case 'up' : x = null; y = val; break;
                    case 'down' : x = null; y = (val == 'end')? val: -val; break;
                    case 'left' : x = val; y = null; break;
                    case 'right' : x = (val == 'end')? val: -val; y = null;
                 } 
                break;
         }
    }
    return { fn: fn, x: x, y: y, dur: dur }
}

dw_scrollObj.handleControlVis = function(controlsId, wndoId, axis) {
    var wndo = dw_scrollObj.col[wndoId];
    var el = document.getElementById(controlsId);
    if ( ( axis == 'v' && wndo.maxY > 0 ) || ( axis == 'h' && wndo.maxX > 0 ) ) {
        el.style.visibility = 'visible';
    } else {
        el.style.visibility = 'hidden';
    }
}
