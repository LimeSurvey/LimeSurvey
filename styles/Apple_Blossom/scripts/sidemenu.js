/**
 * Side Menu
 */
$(document).ready(function(){
    var close = $('#chevronClose');
    var stretch = $('#chevronStretch');
    var sideBody = $('.side-body');
    var sideMenu = $('#sideMenu');
    var absoluteWrapper = $('.absolute-wrapper');
    var sidemenusContainer = $('.sidemenuscontainer');
    var accordionContainer = $('#accordion-container');

    // Check if we have a right-to-left language
    var rtl = $("html").attr('dir') === "rtl";

    if (rtl) {
        var left_or_right_250 = {right: -250};
        var left_or_right_0 = {right: 0};
    }
    else {
        var left_or_right_250 = {left: -250};
        var left_or_right_0 = {left: 0};
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
          opacity: 0.5
        }, left_or_right_250));

        $thatWidth = sideBody.width();
        sideBody.width($thatWidth);

        sideBody.css($.extend({
          width: $thatWidth + 250
        }, left_or_right_250));
        sideBody.parent().css( "overflow-x", "hidden" );

        $that.removeClass("hideside");
        $that.addClass("showside");

        absoluteWrapper.css($.extend({
          opacity: 0.5
        }, left_or_right_250));

        sidemenusContainer.css({
            opacity: 0,
        });
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
            console.log('targetHeight: '+targetHeight);
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
            opacity: 0.5
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
                   500, function() {
                   });

                   absoluteWrapper.animate($.extend({
                       opacity: 0.5
                   }, left_or_right_250),
                   500, function() {
                   });

                   sidemenusContainer.animate({
                       opacity: 0,
                   }, 500);

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
                opacity: 1
            }, left_or_right_0),
            500, function() {
            });

            $thatWidth = sideBody.width();
            sideBody.width($thatWidth);

            sideBody.animate($.extend({
                width: $thatWidth - 250
            }, left_or_right_0),
            500, function() {
            });

            absoluteWrapper.animate($.extend({
                opacity: 1
            }, left_or_right_0),
            500, function() {
            });

            sidemenusContainer.animate({
                opacity: 1,
            }, 500);

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
                opacity: 1,
                width: $('body').width(),
            }, 500, function() {
            });

            absoluteWrapper.animate({
                opacity: 1,
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

    /**
    * Stretch the accordion
    */
    jQuery(document).on('click', '.handleAccordion.opened', function(){
                console.log('stretched accordion');
        // Disable this feature for RTL for now
        if (rtl) {
            return;
        }

        $('.handleAccordion').addClass('disabled');

        accordionContainer.css({
            position: 'absolute',
            right: 0,
        });

        accordionContainer.width(accordionContainer.width());
        accordionContainer.height((sideBody.height()-50));

        accordionContainer.animate(
            {
                width: '100%',
            }, 500, function() {
                $('.handleAccordion span').removeClass('glyphicon-chevron-left').addClass('glyphicon-chevron-right');
                $('.handleAccordion').removeClass('opened').addClass('stretched');
                $('.handleAccordion').removeClass('disabled');
        });

        // jQgrid is so jQgriding its jQgrid...
        if($('#panelintegration').length){
            $('#gbox_urlparams').width('90%');
            $('#gview_urlparams').width('90%');
            $('.ui-state-default.ui-jqgrid-hdiv').width('90%');
            $('.ui-jqgrid-htable.table').width('90%');
            $('.ui-jqgrid-labels th').width('14%');
            $('.ui-jqgrid-bdiv').width('100%');
            $('#urlparams').width('90%');
            $('.jqgfirstrow').width('14%');
            $('#pagerurlparams').width('90%');
        }
    });

    /**
    * Unstretched the accordion
    */
    jQuery(document).on('click', '.handleAccordion.stretched', function(){
        $('.handleAccordion').addClass('disabled');
        accordionContainer.animate(
            {
                width: '41.66666666666667%',// Bootstrap value for col-sm-5
                //width: '33.33333333333333%', // Bootstrap value for col-sm-4
            }, 500, function() {
                $('.handleAccordion span').removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-left');
                $('.handleAccordion').removeClass('stretched').addClass('opened');
                $('.handleAccordion').removeClass('disabled');

                accordionContainer.css({
                    position: 'static',
                });

        });
    });

    /**
     * Adjust height of side body when opening the accordion (to push the footer)
     */
    $('#accordion').on('shown.bs.collapse', function () {
        adjustSideBodyHeight($('#accordion'), 200, 0);
    })

    /**
     * Unfix the side menu when opening question explorer
     */
     var $explorer = $('#explorer');
     var $sidemenu  = $('#sideMenu');

    function afterOpenExplorer() {

        // If the side bar is fixed to top, we must unfix it first
        if ( $sidemenu.hasClass('fixed-top'))
        {
         toTop = ( $(window).scrollTop() + 45 ); // 45px is the heigh of the top menu bar
         $sidemenu.css({position:"absolute", top: toTop+"px"});
        }
        $sidemenu.addClass('exploring');

        // Adjust height of side body when opening the sidemenu (to push the footer)
        adjustSideBodyHeight($sidemenu, 0, 500); // 500ms is the time of the show question animation
     }

     if ( $("#open-explorer").length ) {
        $('#explorer-lvl1').collapse({"toggle": true, 'parent': '#explorer'});
        afterOpenExplorer();
     }

     if ( $("#open-questiongroup").length ) {
         $gid = $("#open-questiongroup").data('gid');
         $questionGroup = $('#questions-group-'+$gid);
         $groupCaret = $('#caret-'+$gid);
         $questionGroup.toggle(0);
         $groupCaret.toggleClass('fa-caret-right');
         $groupCaret.toggleClass('fa-caret-down');
     }

     $explorer.on('shown.bs.collapse', function () {
         afterOpenExplorer();
     });

     $explorer.on('hidden.bs.collapse', function(){
         $sidemenu.removeClass('exploring');
     });

     // Opening the questions list of the group
     $('.explorer-group').click(function(){
         $that = $(this);
         $gid = $that.data('question-group-id');
         $questionGroup = $('#questions-group-'+$gid);
         $groupCaret = $('#caret-'+$gid);
         $questionGroup.toggle(500);
         $groupCaret.toggleClass('fa-caret-right');
         $groupCaret.toggleClass('fa-caret-down');
         adjustSideBodyHeight($sidemenu, 0, 500); //500 ms for the open animation to complete
         return false;
     });


          var windowswidth = window.innerWidth;
          var sideBodyWidth = sideBody.width();
          console.log('sideBodyWidth start: '+sideBodyWidth);
          $( window ).resize(function() {
              //console.log('sideBodyWidth before: '+sideBodyWidth);
              //console.log( windowswidth - window.innerWidth);
              sideBody.width( sideBodyWidth - (windowswidth - window.innerWidth) );
              windowswidth = window.innerWidth;
              sideBodyWidth = sideBody.width();
              //console.log('sideBodyWidth after: '+sideBodyWidth);

              if( sideBodyWidth < 1420 )
              {
                  if(accordionContainer.hasClass('col-md-6'))
                  {
                      $('#accordion-container').removeClass('col-md-6').addClass('col-md-12');
                  }
              }
              else
              {
                 if(accordionContainer.hasClass('col-md-12'))
                 {
                    $('#accordion-container').removeClass('col-md-12').addClass('col-md-6');
                 }
              }
          });


});


/**
 * Stick the side menu and the survey bar to the top
 */
$(function()
{
    if ( $('.surveybar').length ) {
        var surveybar = $('.surveybar');
        var sidemenu = $('.side-menu');

        $(window).scroll(function() {
            $toTop = (surveybar.offset().top - $(window).scrollTop());

            if($toTop <= 0)
            {
                surveybar.addClass('navbar-fixed-top');

                // We fix the side menu only if not exploring the questions
                if( ! sidemenu.hasClass('exploring'))
                {
                    sidemenu.css({position:"fixed", top: "45px"}); // 45px is the height of menu bar
                    sidemenu.addClass('fixed-top');
                }
            }

            if ($(window).scrollTop() <= 45)
            {
                surveybar.removeClass('navbar-fixed-top');
                sidemenu.css({position:"absolute", top: "auto"});
                sidemenu.removeClass('fixed-top');
            }

            // When exploring questions, we need to be sure that no empty white space will left on top of the side bar
            if (sidemenu.hasClass('exploring'))
            {
                $sideMenutoTop = (sidemenu.offset().top - $(window).scrollTop());
                console.log($sideMenutoTop);

                if ($sideMenutoTop > 0 && surveybar.hasClass('navbar-fixed-top') )
                {
                    toTop = ( $(window).scrollTop() + 45 ); // 45px is the heigh of the top menu bar
                    sidemenu.css({position:"absolute", top: toTop+"px"});
                }
            }
        });
    }
});
