
var SideMenuMovement = function(
    sidemenuSelector, 
    sideBodySelector, 
    dragButtonSelector, 
    collapseButtonSelector, 
    uncollapsedHomeSelector, 
    quickMenuSelector, 
    options){
    
    //define options, or standardized values
    options = options || {};
    options.fixedTopMargin = options.fixedTopMargin || $('#questiongroupbarid').height()+2;
    options.baseWidth = options.baseWidth || 320;
    options.collapsedWidth = options.collapsedWidth || 55;

    var 
        isRTL       = ($('html').attr('dir') == 'rtl'),
    //define DOM Variables
        oSideMenu           = $(sidemenuSelector),
        oSideBody           = $(sideBodySelector),
        oDragButton         = $(dragButtonSelector),
        oCollapseButton     = $(collapseButtonSelector),
        oUnCollapsedHome    = $(uncollapsedHomeSelector),
        oQuickMenu          = $(quickMenuSelector),
    
    //define calculateble values
        wWidth      = $('html').width(),
        wHeight     = $('html').height(),
        dHeight     = oSideBody.parent().height(),
    
    //define runtimevariables
        offsetX     = 0,
        offsetY     = 0,
        position    = 0,
    
    //define rtl-specific classes
        chevronClosed = (isRTL ? 'fa-chevron-left' : 'fa-chevron-right'),
        chevronOpened = (isRTL ? 'fa-chevron-right' : 'fa-chevron-left'),

//////define methods
    //setter methods to set the items
        setBody = function(newValue){
            if(isRTL) {
                oSideBody.css({'margin-right': (newValue+10)+"px"});
            } else {
                oSideBody.css({'margin-left': (newValue+10)+"px"});
            }

        },
        setMenu = function(newValue){
            oSideMenu.css({'width': newValue+"px"});
        },
        setDraggable = function(newValue){
            // oDragButton.css({'left': (newValue)+"px"})
        },
        collapseSidebar = function(force){
            force = force || false;
            // console.log("collapsing",oCollapseButton.data('collapsed'));
            oQuickMenu.css('display','');
            var collapsedWidth = isRTL ? wWidth-options.collapsedWidth : options.collapsedWidth;
            setDivisionOn(collapsedWidth,false);
            if(oCollapseButton.data('collapsed') != 1 || force){ 
                oCollapseButton.closest('div').css({'width':'100%'});
                oSideMenu.find('.side-menu-container').css({'display': 'none'});
                oCollapseButton.find('i').removeClass(chevronOpened).addClass(chevronClosed);
                oUnCollapsedHome.css({display: 'none'});
                oCollapseButton.data('collapsed', 1);
            }
        },
        unCollapseSidebar = function(position){
            setDivisionOn(position,true);
            // console.log(oCollapseButton.data('collapsed'));
            oQuickMenu.css('display','none');
            if(oCollapseButton.data('collapsed') != 0){
                oCollapseButton.closest('div').css({'width':''});
                oSideMenu.find('.side-menu-container').css({'display': ''});
                oCollapseButton.find('i').removeClass(chevronClosed).addClass(chevronOpened);
                oUnCollapsedHome.css({display: 'inline-block'});
                oCollapseButton.data('collapsed', 0);
            }
        },

    //definer and mutators
        defineOffset = function(oX,oY){
            offsetX = oX;
            offsetY = oY;
        },
        getSavedOffset = function(){
            var savedOffset = null;
            try{
                var savedOffset = parseInt(localStorage.getItem('ls_admin_view_sidemenu'));
            } catch(e){}

            var startOffset = (isNaN(savedOffset) || !savedOffset) ? options.baseWidth : savedOffset;

            // console.log('startOffset', startOffset)
            startOffset = isRTL ? wWidth-startOffset : startOffset;

            return startOffset;
        },

    //utility and calculating methods
        calculateValue = function(xClient){
            if(isRTL){
                xClient = (wWidth-xClient);
                var sidebarWidth = xClient+(xClient>options.collapsedWidth ? (50-offsetX) : 5);
                var sidebodyMargin = sidebarWidth+Math.floor(wWidth/200);
                var buttonLeftTop = Math.abs(wWidth-(xClient-offsetX));
            } else {
                var sidebarWidth = xClient+(xClient>options.collapsedWidth ? (50-offsetX) : 5);
                var sidebodyMargin = sidebarWidth+Math.floor(wWidth/200);
                var buttonLeftTop = xClient-offsetX;
            }
            return {sidebar : sidebarWidth, body : sidebodyMargin, button: buttonLeftTop};
        },
        saveOffsetValue = function(offset){
            try{
                localStorage.setItem('ls_admin_view_sidemenu',''+offset);
            } catch(e){}
        },
        setDivisionOn = function(xClient,save){
            save = save || false;
            var oValues = calculateValue(xClient);
            setBody(oValues.body);
            setMenu(oValues.sidebar);
            setDraggable(oValues.button);
            if(save){
                saveOffsetValue(xClient);
            }
        },

    //eventHandler
        onDblClick = function(e){
            var baseWidth = isRTL ? wWidth-options.baseWidth : options.baseWidth;
            unCollapseSidebar(baseWidth);
            window.localStorage.setItem('ls_admin_view_sidemenu',null);
        },
        onClickCollapseButton = function(e){
            if(oCollapseButton.data('collapsed')==0 ){ 
                collapseSidebar();
            } else {
                var setWidth = getSavedOffset();
                // console.log('setWidth',setWidth);
                unCollapseSidebar(setWidth);
            }
        },
        onDragStartMethod = function(e){
            // console.log('dragstart triggered', e);
            defineOffset(e.offsetX, e.offsetY);
            $('body').on('mousemove.touched', onDragMethod)
            $('body').on('mouseup.touched', onDragEndMethod)
        },
        onDragMethod = function(e){
            // console.log('drag triggered', e.screenX);

            position =  e.screenX;
            setDivisionOn(position);
        },
        onDragEndMethod = function(e){
            // console.log('dragend triggered', e.screenX);
            position =  e.screenX;
            if(position <  wWidth/8 ){
                collapseSidebar();
            } else {
                unCollapseSidebar(position);
            }
             $('body').off('.touched');
        };
    
    var startOffset = getSavedOffset();

    if(startOffset <  wWidth/8 || oCollapseButton.data('collapsed')==1 ){
        collapseSidebar(true);
    } else {
        unCollapseSidebar(startOffset);
    }

    oDragButton
        .on('dblclick', onDblClick)
        .on('mousedown', onDragStartMethod)
        .on('dragstart', function(e){e.preventDefault();return false;});
    oCollapseButton
        .on('click', onClickCollapseButton);
};

var WindowBindings = function(){
    var surveybar = $('.surveybar'),
        sideBody = $('.side-body'),
        sidemenu = $('#sideMenu'),
        sidemenuContainer = $('#sideMenuContainer'),
        upperContainer = $('#in_survey_common'),
    
    //calculated vars
        maxHeight =  $(window).height() - $('#in_survey_common').offset().top - 10,
        basePosition = {top: 0, left: 0},
    //methods
        //create the first setting and calculate therefor
        setInitial = function(){
            basePosition = sidemenuContainer.offset();
            onWindowResize();
            onWindowScroll();
        },
        //Stick the side menu and the survey bar to the top
        onWindowScroll = function(e){
            var $toTop = (surveybar.offset().top - $(window).scrollTop());
            var topPosition = (basePosition.top - $(window).scrollTop());
                sidemenuContainer.css({position:"fixed", top: topPosition});

            if($toTop <= 0)
            {
                surveybar.addClass('navbar-fixed-top');
                sidemenuContainer.css({position:"fixed", top: "45px"});
            }

            if ($(window).scrollTop() <= 45)
            {
                surveybar.removeClass('navbar-fixed-top');
            }
        },
        //fixSizings
        onWindowResize = function(){
            maxHeight       = ($(window).height() - (basePosition.top-5));
            // console.log("body", $('body').height());
            // console.log("base", basePosition.top);
            // console.log("footer", $('footer').height());
            // console.log("maxHeight", maxHeight);

            //maxHeightInside = (maxHeight - $('#in_survey_common').offset().top-2);
            sidemenu.css({'height': maxHeight, "overflow-y": 'auto'});
            sidemenuContainer.css({'max-height': (maxHeight)});
        }
    
    setInitial();
    $(window).on('scroll',onWindowScroll);
    $(window).on('resize',onWindowResize);
};


/**
 * Side Menu
 */
    
$(document).ready(function(){
   if($('#sideMenuContainer').length >0){
        new SideMenuMovement(
            '#sideMenuContainer', 
            '.side-body', 
            '#scaleSidebar', 
            '#chevronClose', 
            '#sidemenu-home',
            '#quick-menu-container',
            {baseWidth: 320});
        new WindowBindings();
    }
});
