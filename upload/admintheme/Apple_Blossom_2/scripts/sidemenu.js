/**
 * Side Menu
 */

$('.navbar-toggle').click(function () 
{
    $('.navbar-nav').toggleClass('slide-in');
    $('.side-body').toggleClass('body-slide-in');
    $('#search').removeClass('in').addClass('collapse').slideUp(200);
});

/**
 *  Close sidemenu
 */
jQuery(document).on('click', '.hideside', function(){    
    $that = $('.toggleside'); 

	// Move the side menu
    $('.side-menu').animate({
      opacity: 0.5,
      left: "-250",
        }, 500, function() {
           $that.removeClass("hideside");
           $that.addClass("showside");
            $('#chevronside').removeClass('glyphicon-chevron-left');
            $('#chevronside').addClass("glyphicon-chevron-right");
    });

	// Move the side body
    $('.side-body').animate({
      left: "-125",
        }, 500, function() {
    });        

    $('.absolute-wrapper').animate({
      opacity: 0.5,
      left: "-250",
        }, 500, function() {
    });        
    
    $('.sidemenuscontainer').animate({
        opacity: 0,
    }, 500);           
});


/**
 * If the side bar state is set to  "close" on page load, it closes the side menu 
 */
$(document).ready(function(){
    if ( $("#close-side-bar").length ) {
        $that = $('.toggleside');
    
        $('.side-menu').css({
          opacity: 0.5,
          left: -250,
        });
    
        $('.side-body').css({
          left: -125,
        });        
    
        $that.removeClass("hideside");
        $that.addClass("showside");
        $('#chevronside').removeClass('glyphicon-chevron-left');
        $('#chevronside').addClass("glyphicon-chevron-right");
    
        $('.absolute-wrapper').css({
          opacity: 0.5,
          left: -250,
            });        
        
        $('.sidemenuscontainer').css({
            opacity: 0,
        });                   
    }
});

/**
 * Show the side menu
 */     
jQuery(document).on('click', '.showside', function(){
    $that = $('.toggleside');
    $('.side-menu').animate({
      opacity: 1,
      left: "0",
        }, 500, function() {
        $that.removeClass("showside");
        $that.addClass("hideside");
        $('#chevronside').removeClass('glyphicon-chevron-right');
        $('#chevronside').addClass("glyphicon-chevron-left");               
    });


	$('.side-body').animate({
	  left: "0",
	    }, 500, function() {
	}); 
	
	$('.absolute-wrapper').animate({
	  opacity: 1,
	  left: "0",
	    }, 500, function() {
	});         
	
	$('.sidemenuscontainer').animate({
	    opacity: 1,
	}, 500);        
});


/**
 * Stick the side menu and the survey bar to the top
 */
$(function() 
{
  $(window).scroll(function() { //when window is scrolled
	    $toTop = ($('.surveybar').offset().top - $(window).scrollTop());
	
	    if($toTop <= 0)
	    {
	        $('.surveybar').addClass('navbar-fixed-top');
	        $('.side-menu').css({position:"fixed", top: "45px"});
	    }
	    
	    if( $(window).scrollTop() <= 45)
	    {
	        $('.surveybar').removeClass('navbar-fixed-top');
	        $('.side-menu').css({position:"absolute", top: "auto"});
	    }
	});
});