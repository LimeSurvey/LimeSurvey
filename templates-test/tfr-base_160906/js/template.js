/**
 * TFR Responsive Theme by Tools For Research (c) 2015 - 2016
 */



//* !init */

$(document).ready(function(){

    //* !Focus on the first input field cq answer */
    function focusFirst(Event){
        $('#limesurvey :input:visible:enabled:first').focus();
    }
    focusFirst();
    
    //* !Remove stuff with enabled js */
    // Remove .no-js from body when javascript is enabled
    $('body').removeClass('no-js');
    
    // Remove elements from the DOM when javascript is enabled
    $('.no-js').detach();
    
    
    //* !Navigator & Index in the navbar */
    // Make sure the original elements are triggered when their placeholders are clicked from within the navbar
    // Code from default template
    
    // load survey
    if ($('#loadallbtnlink').length > 0){
        $('#loadallbtnlink').on('click', function(){
            $('#loadallbtn').trigger('click');
        });
    }

    // save survey
    if ($('#saveallbtnlink').length > 0){
        $('#saveallbtnlink').on('click', function(){
            $('#saveallbtn').trigger('click');
        });
    }

    // clearall
    if ($('#clearallbtnlink').length > 0){
        $('#clearallbtnlink').on('click', function(){
            $('#clearall').trigger('click');
        });
    }

    // Question index
    if($('.linkToButton').length > 0){
        $('.linkToButton').on('click', function(){
            $btnToClick = $($(this).attr('data-button-to-click'));
            $btnToClick.trigger('click');
            return false;
        });
    }
    
    
    //* !Dynamic Navbar Height */
    // Always give the content enough headroom, even with long surveynames
    var navbarHeight = $('#surveyNavbar').innerHeight();
    $('#mainContainer').css( 'margin-top', navbarHeight+'px' );


    //* !Remove elements */
    
    //* !  Remove surveyHeader */
    // When there is no surveyName and no surveyDescription
    // This will not happen very often; it is logical to have at least a surveyname, right?
    if(!$.trim($('#surveyName').html())){
        if(!$.trim($('#surveyDescription').html())){
            $('#surveyHeader').detach();
        }
    }
    
    //* !  Remove .radio and .checkbox on divs, lis and tds within answers */
    // Because BS default styling is interfering
    // This removal is twofold: first we remove the class on the div or tablecell itself
    // Then we remove the class on the nested input
    // This way we have an unstyled clean base to work from
    // As fallback we override it with css so there is less of a style-jump when refreshing the page
    $('div.radio, li.radio, td.radio').each(function(){
        // Remove on the tablecell
        $(this).removeClass('radio');
        // Remove on the input
        $(this).find('input').removeClass('radio');
    });
    $('div.checkbox, li.checkbox, td.checkbox').each(function(){
        // Remove on the tablecell
        $(this).removeClass('checkbox');
        // Remove on the input
        $(this).find('input').removeClass('checkbox');
    });
    // Even better to remove this junk from views via pull request
    
    //* !  Remove .well on tables */
    // Remove BS classes on answers-lists
    // Better to override it with css so there is no style-jump when refreshing the page
    // Better to remove this junk from views via pull request
    $('table').each(function(){
        $(this).find('tr').removeClass('well');
    });

    //* !  Remove excessive Bootstrap classes */
    // Remove BS classes on answers-lists
    //$('.answers-list').each(function(){
    //    $(this).find('div').removeClass('col-xs-12 col-sm-2 col-md-1 col-sm-6');
    //});

    //* !  Remove .text-right on thead th in arrays */
    // Remove wrong given class text-center to the th in arrays*
    // * like array-multi-flexi / numeric-item
    $('thead').each(function(){
        $(this).find('th').removeClass('text-right');
    });

    //* !  Remove .text-center on tbody th in arrays */
    // Remove wrong given class text-center to the th in arrays*
    // * like array-inc-same-decrease
    $('tbody').each(function(){
        $(this).find('th').removeClass('text-center');
    });

    //* !Make the label clickable */
    // Code from default template
    $('.label-clickable').each(function(){
        var $that    = $(this);
        var $inputEl = $("#"+$that.attr('id').replace("label-", ""));
        $that.on('click', function(){
            console.log($inputEl.attr('id'));
            $inputEl.trigger( "click" );
        });
    });


    //* !Tooltip */
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })


    //* !Animated Navigation */
    // Handles animation of the navigation menu when user scrolls.
    // Thanks to Teehan & Lax and Alex Cican for coining this - https://github.com/alexcican/lab/tree/gh-pages/teehan_lax_navigation
    $(function() {
    
    var previousScroll = 0, // previous scroll position
        menuOffset = 50, // height of menu (once scroll passed it, menu is hidden)
        hideShowOffset = 5; // scrolling value after which triggers hide/show menu
    
    // on scroll hide/show menu
    $(window).scroll(function() {
      if ($('nav #navbar').hasClass('in')) {
        // do nothing; main navigation is being shown
      } else {
        var currentScroll = $(this).scrollTop(), // gets current scroll position
            scrollDifference = Math.abs(currentScroll - previousScroll); // calculates how fast user is scrolling
    
        // if scrolled past menu
        if (currentScroll > menuOffset) {
          // if scrolling faster than hideShowOffset hide/show menu
          if (scrollDifference >= hideShowOffset) {
            if (currentScroll > previousScroll) {
              // scrolling down; hide menu
              $('nav').addClass('compressed');
            } else {
              // scrolling up; show menu
              $('nav').removeClass('compressed');
            }
          }
        }
    
        // if user is at the bottom of document show menu
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
          $('nav').removeClass('compressed');
        }
    
        // replace previous scroll position with new one
        previousScroll = currentScroll;
      }
    })
    
    });



//* !Non Tackled code */

    //// iPad has width 768, Google Nexus 10 width 800
    //// It's OK to keep tables on pads.
    //if($(window).width() < 768)
    //{
    //    if($('.no-more-tables, .array-by-columns-table').length > 0)
    //    {
    //        $('.no-more-tables, .array-by-columns-table').find('td').each(function(){
    //            $that = $(this);
    //            $label = $that.data('title');
    //            $input = $that.find('input');
    //            if($input.is(':checkbox') || $that.hasClass('radio'))
    //            {
    //                $that.find('.hide').removeClass('hide');
    //            }
    //            else
    //            {
    //                // TODO: Remove this logic for screen reader
    //                // Only used for array dual scale and array columns now.
    //                $that.find('label').prepend($label);
    //            }
    //
    //        });
    //    }
    //
    //    // Brutally remake the array-by-columns question type to divs,
    //    // because you can't wrap table columns
    //    $('.array-by-columns-table').each(function() {
    //        replaceColumnWithDiv(this);
    //    });
    //
    //}

    ////* !Emtip wizardry */
    //if($('.emtip').length>0)
    //{
    //    // On Document Load
    //    $('.emtip').each(function(){
    //        if($(this).hasClass('error'))
    //        {
    //            $(this).parents('div.questionhelp').removeClass('text-info').addClass('text-danger');
    //        }
    //        if($(this).hasClass('good'))
    //        {
    //            $(this).parents('div.questionhelp').removeClass('text-danger').addClass('text-info');
    //        }
    //    });
    //
    //    // On em change
    //    $('.emtip').each(function(){
    //        $(this).on('classChangeError', function() {
    //            $parent = $(this).parent('div.questionhelp');
    //            $parent.removeClass();
    //            $parent.addClass('text-danger',1);
    //
    //            if ($parent.hasClass('hide-tip'))
    //            {
    //                $parent.removeClass('hide-tip',1);
    //                $parent.addClass('tip-was-hidden',1);
    //            }
    //
    //            $questionContainer = $(this).parents('div.question-container');
    //            $questionContainer.addClass('input-error');
    //        });
    //
    //        $(this).on('classChangeGood', function() {
    //            $parent = $(this).parents('div.questionhelp');
    //            $parent.removeClass('text-danger');
    //            $parent.addClass('text-success');
    //            if ($parent.hasClass('tip-was-hidden'))
    //            {
    //                $parent.removeClass('tip-was-hidden').addClass('hide-tip');
    //            }
    //            $questionContainer = $(this).parents('div.question-container');
    //            $questionContainer.removeClass('input-error');
    //        });
    //    });
    //}

    //// Survey list footer
    //if($('#surveyListFooter').length>0)
    //{
    //    $surveyListFooter = $('#surveyListFooter');
    //    $('#outerframeContainer').after($surveyListFooter);
    //}


});

//* !Error Modal */
window.alert = function(message) {
    // Generate the content of the Modal
    $('#bootstrapAlertBoxModal .modal-body p').text(message || "");
    
    $(document).ready(function(){
        // Show Modal
        $('#bootstrapAlertBoxModal').modal('show');
        
        // Scroll to first error
        if($('.input-error').length > 0) {
            $('#bootstrapAlertBoxModal').on('hidden.bs.modal', function () {
                console.log('answer error found');
                $firstError = $(".input-error").first();
                $pixToScroll = ( $firstError.offset().top - 100 );
                $('html, body').animate({
                     scrollTop: $pixToScroll + 'px'
                 }, 'fast');
                $firstError.focus();
            });
        }
    });
};

///**
// * Remake table @that with divs, by column
// * Used by array-by-column question type on
// * small screen
// *
// * TODO: remove all the HTML from this function.
// *
// * @param {object} that The table jQuery object
// * @return void
// */
//function replaceColumnWithDiv(that) {
//    var newHtml = '';
//    var nrOfColumns = $(that).find('tr:first th').length;
//    newHtml += "<div class='array-by-columns-div'>";
//    for (var i = 0; i < nrOfColumns; i++)
//    {
//        // Fetch each column from the table and put content in div
//        newHtml += "<div class='well radio-list array" + (i % 2 === 0 ? "2" : "1") + " '>";
//        $(that).find('tr > *:nth-child('+ (i + 2) + ')').each(function(j) {
//            // First one is header
//            if (j === 0) {
//                newHtml += "<div class='answertext'>";
//                newHtml += $(this).html();
//                newHtml += "</div>";
//            }
//            else {
//                newHtml += "<div class='radio-item radio'>";
//                newHtml += $(this).html();
//                newHtml += "</div>";
//            }
//        });
//        newHtml += "</div>";
//    }
//    newHtml += "</div>";
//    $(that).replaceWith(newHtml);
//}
