
var SideMenuMovement = function(sidemenuSelector, sideBodySelector, dragButtonSelector, options){
    
    //define options, or standardized values
    options = options || {};
    options.fixedTopMargin = options.fixedTopMargin || $('#questiongroupbarid').height()+2;
    options.baseWidth = options.baseWidth || 320;
    options.rtl = options.rtl || false;

    var 
    //define DOM Variables
        oSideMenu   = $(sidemenuSelector),
        oSideBody   = $(sideBodySelector),
        oDragButton = $(dragButtonSelector),
    
    //define calculateble values
        wWidth      = $('html').width(),
        wHeight     = $('html').height(),
        dHeight     = oSideBody.parent().height(),
    
    //define runtimevariables
        offsetX     = 0,
        offsetY     = 0,
        position    = 0,

//////define methods
    //setter methods to set the items
        setBody = function(newValue){
            if(options.rtl) {
                oSideBody.css({'margin-right': (newValue+10)+"px"});
            } else {
                oSideBody.css({'margin-left': (newValue+10)+"px"});
            }

        },
        setMenu = function(newValue){
            oSideMenu.css({'width': newValue});
        },
        setDraggable = function(newValue){
          //  oDragButton.css({'left': (newValue)+"px"})
        },
        collapseSidebar = function(position){
            setDivisionOn(50,true);
            if(oSideMenu.data('collapsed') != true){ 
                oSideMenu.find('.sidemenuscontainer').css({'margin-left': '86px',});
                oSideMenu.data('collapsed',true).css('overflow','hidden');
            }
        },
        unCollapseSidebar = function(position){
            setDivisionOn(position,true);
            if(oSideMenu.data('collapsed') != false){
                oSideMenu.find('.sidemenuscontainer').css({'margin-left': 'initial',});
                oSideMenu.data('collapsed',false).css('overflow','initial');
            }
        },

    //definer and mutators
        defineOffset = function(oX,oY){
            offsetX = oX;
            offsetY = oY;
        },

    //utility and calculating methods
        calculateValue = function(xClient){

            var sidebarWidth = xClient+(xClient>50 ? (50-offsetX) : 1);
            var sidebodyMargin = sidebarWidth+Math.floor(wWidth/200);
            var buttonLeftTop = xClient-offsetX;

            return {sidebar : sidebarWidth, body : sidebodyMargin, button: buttonLeftTop};
        },
        saveOffsetValue = function(offset){
            try{
                window.localStorage.setItem('ls_admin_view_sidemenu',offset);
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
            setDivisionOn(options.baseWidth);
            window.localStorage.setItem('ls_admin_view_sidemenu',null);
        },
        onDragStartMethod = function(e){
            // console.log('dragstart triggered', e);
            defineOffset(e.offsetX, e.offsetY);
        },
        onDragMethod = function(e){
            // console.log('drag triggered', e);
            position =  e.clientX;
            setDivisionOn(position);
        },
        onDragEndMethod = function(e){
            // console.log('dragend triggered', e);
            position =  e.clientX;
            if(position <  wWidth/8 ){
                collapseSidebar(position);
            } else {
                unCollapseSidebar(position);
            }
        };

    try{
        var savedOffset = window.localStorage.getItem('ls_admin_view_sidemenu');
    } catch(e){}

    var startOffset = parseInt(savedOffset) || options.baseWidth;
    
    if(startOffset <  wWidth/8 ){
        collapseSidebar(position);
    } else {
        unCollapseSidebar(position);
    }

    oDragButton
        .on('dblclick', onDblClick)
        .on('dragstart', onDragStartMethod)
        .on('drag', onDragMethod)
        .on('dragend', onDragEndMethod);
}

var WindowBindings = function(){
    var surveybar = $('.surveybar'),
        sideBody = $('.side-body'),
        sidemenu = $('#sideMenuContainer'),
        upperContainer = $('#in_survey_common'),
    
    //calculated vars
        maxHeight =  $(window).height() - $('#in_survey_common').offset().top - 10,
    
    //methods
        //Stick the side menu and the survey bar to the top
        onWindowScroll = function(e){
            $toTop = (surveybar.offset().top - $(window).scrollTop());

            if($toTop <= 0)
            {
                surveybar.addClass('navbar-fixed-top');
                sidemenu.css({position:"fixed", top: "45px"});
            }

            if ($(window).scrollTop() <= 45)
            {
                surveybar.removeClass('navbar-fixed-top');
                sidemenu.css({position:"absolute", top: "auto", 'height': ($(window).height() - 45)+"px"});
                sidemenu.removeClass('fixed-top');
            }
        },
        //fixSizings
        onWindowResize = function(){
            maxHeight =  ($(window).height() - $('#in_survey_common').offset().top -1);
            sidemenu.find('#fancytree').css({'max-height': (maxHeight/4)+"px", 'overflow': 'auto' });
        }
    onWindowResize();
    $(window).on('scroll',onWindowScroll);
    $(window).on('resize',onWindowResize);
};


/**
 * Side Menu
 */
    
$(document).ready(function(){
   
    new SideMenuMovement('#sideMenuContainer', '.side-body', '#scaleSidebar', {baseWidth: 320});
    new WindowBindings();

    var close = $('#chevronClose');
    var stretch = $('#scaleSidebar');
    var sideBody = $('.side-body');
    var sideMenu = $('#sideMenu');
    var absoluteWrapper = $('.absolute-wrapper');
    var sidemenusContainer = $('.sidemenuscontainer');
    var quickmenuContainer = $('#quick-menu-container');

    // Check if we have a right-to-left language
    var rtl = $("html").attr('dir') === "rtl";

    if (rtl) {
        var left_or_right_250 = {right: -250};
        var left_or_right_0 = {right: 0};
        var margin_left_or_right = {'margin-right': '320px'};
    }
    else {
        var left_or_right_250 = {left: -250};
        var left_or_right_0 = {left: 0};
        var margin_left_or_right = {'margin-left': '320px'};
    }

    /**
    * If the side bar state is set to  "close" on page load, it closes the side menu
    */
    if ( $("#close-side-bar").length ) {
        $('#chevronStretch').removeClass('opened');
        $('#chevronClose').removeClass('opened');

        $('#chevronStretch').addClass('closed');
        $('#chevronClose').addClass('closed');

        $that = $('.toggleside');

        $('.side-menu').css($.extend({
          //opacity: 0.5
        }, left_or_right_250));

        $thatWidth = sideBody.width();
        sideBody.width($thatWidth);

        //sideBody.css($.extend({
          //width: $thatWidth + 250
        //}, left_or_right_250));
        sideBody.parent().css( "overflow-x", "hidden" );

        $that.removeClass("hideside");
        $that.addClass("showside");

        absoluteWrapper.css($.extend({
          //opacity: 0.5
        }, left_or_right_250));

        sidemenusContainer.hide();
        quickmenuContainer.show();
    }
    else {
        quickmenuContainer.hide();
    }

    // To prevent the user to try to open or close it before the animation ended (because of  $('.side-body').width())
    function disableChevrons() {
        close.addClass('disabled');
        stretch.addClass('disabled');
    }

    function enableChevrons() {
        close.removeClass('disabled');
        stretch.removeClass('disabled');
    }

    function chevronChangeState(toRemove, toAdd) {
        close.removeClass(toRemove);
        stretch.removeClass(toRemove);
        close.addClass(toAdd);
        stretch.addClass(toAdd);
    }

    /**
     * Adjust height of side body compared to a target, can apply a correction in pixel
     * $target : the target element
     * $correction : correction in pixels
     * $timeout : time to wait before measuring the target height, can be usefull when animations apply to target
     */
    function adjustSideBodyHeight($target, $correction, $timeout) {
        setTimeout(function(){
            sideBodyHeight = sideBody.height();
            targetHeight = $target.height();
            //alert(sidemenuHeight);
            if( sideBodyHeight < ( targetHeight + $correction ) )
            {
                sideBody.height( sideBodyHeight + ( targetHeight - sideBodyHeight ) + $correction );
            }
        }, $timeout);
    }

    /**
    *  Close sidemenu
    */
    jQuery(document).on('click', '#chevronClose.opened', function(){
        disableChevrons();

        // Move the side menu
        sideMenu.animate($.extend({
            //opacity: 0.5
            }, left_or_right_250),
            500,
            function() {}
        );

        // To animate correctly the side body, we first must give it a fixed width
        $thatWidth = sideBody.width();
        sideBody.width($thatWidth);

        // Move the side body
        sideBody.animate(
           $.extend({
               width: $thatWidth + 250
           }, left_or_right_250),
           500,
           function() {}
       );

       absoluteWrapper.animate($.extend({
           //opacity: 0.5
           }, left_or_right_250),
           500,
           function() {}
       );

       sidemenusContainer.fadeOut();
       quickmenuContainer.fadeIn();

       chevronChangeState('opened', 'closed');
       enableChevrons();
    });

    /**
    * Unstreched side menu
    */
    jQuery(document).on('click', '#chevronClose.stretched', function(){
        disableChevrons();
        sideMenu.animate({
                width: 300,
            }, 500, function() {
            });

            absoluteWrapper.animate({
                width: 300,
            }, 500, function() {
            });

            sidemenusContainer.animate({
                width: 300,
            }, 500);

            chevronChangeState('stretched', 'opened');
            enableChevrons();
    });

    /**
    * Show the side menu
    */
    jQuery(document).on('click', '#chevronStretch.closed', function(){
        disableChevrons();

        sideMenu.animate($.extend({
                //opacity: 1
            }, left_or_right_0),
            500, function() {
        });

        $thatWidth = sideBody.width();
        sideBody.width($thatWidth);

        sideBody.animate($.extend({
            width: $thatWidth - 250
        }, left_or_right_0, margin_left_or_right),
        500, function() {
        });

        absoluteWrapper.animate($.extend({
            //opacity: 1
        }, left_or_right_0),
        500, function() {
        });

        sidemenusContainer.fadeIn();
        quickmenuContainer.fadeOut();

        chevronChangeState('closed', 'opened');
        enableChevrons();
    });

    /**
    * Stretch the side menu
    */
    jQuery(document).on('click', '#chevronStretch.opened', function(){
        disableChevrons();

        sideMenu.animate({
                backgroundColor: "white",
                //opacity: 1,
                width: $('body').width(),
            }, 500, function() {
            });

            absoluteWrapper.animate({
                //opacity: 1,
                backgroundColor: "white",
                width: $('body').width(),
            }, 500, function() {
            });

            sidemenusContainer.animate({
                opacity: 1,
                backgroundColor: "white",
                width: $('body').width(),
            }, 500);

            chevronChangeState('opened', 'stretched');
            enableChevrons();
    });
});



var drop_delete_fn = function () {};

/**
 * Drag-n-drop functionality for quick-menu
 * @todo Add this to plugin ExtraQuickMenuItems? Needs to be on every admin page.
 */
function dragstart_handler(ev) {

    // Use to set a image during dragging
    //var img = new Image();
    //img.src = '/limesurvey/styles/Sea_Green/images/donate.png';
    //ev.dataTransfer.setDragImage(img, 10, 10);

    ev.dataTransfer.dropEffect = 'move';
    ev.dataTransfer.effectAllowed = 'move';
    var html = $(ev.target).prop('outerHTML');
    ev.dataTransfer.setData("text/plain", html);

    drop_delete_fn = function () {
        $(ev.target).remove();
    }
}

function dragover_handler(ev) {
    ev.preventDefault();
    $(ev.target).css('background-color', 'black');
    return false;
}

function dragleave_handler(ev) {
    $(ev.target).css('background-color', 'white');
    ev.preventDefault();
    return false;
}

function drop_handler(ev) {
    ev.preventDefault();
    // TODO: Why is ev.target not <a>, but <div>?
    var $target = $(ev.target).parent().parent();
    var data = ev.dataTransfer.getData("text");
    $(ev.target).css('background-color', 'white');

    if (data.indexOf("quick-menu-item") < 0)
    {
        return;
    }

    $target.after(data);
    drop_delete_fn();

    // Delete left-over tooltip
    $('.tooltip.fade').remove();
    doToolTip();

    // Collect button name and order number
    var data = {};
    $('.quick-menu-item').each(function(i, item) {
        var name = $(item).data('button-name');
        data[name] = i;
    });

    $.ajax({
        method: 'POST',
        url: saveQuickMenuButtonOrderLink,
        data: {buttons: data}
    }).done(function(response) {
        // Show save confirmation?
    });

    //ev.dataTransfer.clearData();
}