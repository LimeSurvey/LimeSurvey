<!DOCTYPE html>
<html lang="<?php echo $adminlang; ?>"<?php echo $languageRTL;?> >
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php 
    
        // jQuery plugins
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-cookie');
        App()->getClientScript()->registerPackage('qTip2');

        // Bootstrap
        App()->bootstrap->register();   
        App()->getClientScript()->registerPackage('lime-bootstrap');

        // Right to Left
        if (getLanguageRTL($_SESSION['adminlang']))
        {        
            App()->getClientScript()->registerPackage('adminstyle-rtl');
        }
        
        // Printable
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "printablestyle.css", 'print');
    ?>
    <?php echo $datepickerlang;?>
    <title><?php echo $sitename;?></title>
    <link rel="shortcut icon" href="<?php echo $baseurl;?>styles/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo $baseurl;?>styles/favicon.ico" type="image/x-icon" />
    <?php echo $firebug ?>
    <?php $this->widget('ext.LimeScript.LimeScript'); ?>
    <?php $this->widget('ext.LimeDebug.LimeDebug'); ?>
</head>
<body>
	
<?php $this->widget('ext.FlashMessage.FlashMessage'); ?>

<style>


/**
 * 	Welcome page
 */


.jumbotron
{
	background-color : transparent;
	text-align: center;
}

.welcome #lime-logo
{
    -webkit-animation: fadein 1s; /* Safari, Chrome and Opera > 12.1 */
       -moz-animation: fadein 1s; /* Firefox < 16 */
        -ms-animation: fadein 1s; /* Internet Explorer */
         -o-animation: fadein 1s; /* Opera < 12.1 */
            animation: fadein 1s;	
}

.alert 
{
	position : relative;
    -webkit-animation: slidefromtop 1s; /* Safari, Chrome and Opera > 12.1 */
       -moz-animation: slidefromtop 1s; /* Firefox < 16 */
        -ms-animation: slidefromtop 1s; /* Internet Explorer */
         -o-animation: slidefromtop 1s; /* Opera < 12.1 */
            animation: slidefromtop 1s;		
}

.welcome .panel 
{
	position : relative;
	top : 50px;
	opacity: 0;
	box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);
}


@media screen and (min-width: 1280px) and (max-width: 1440px) 
{
    .welcome .panel 
    {
        min-height: 188px;    
    }
}

.welcome .panel-body
{
	text-align : center;
}

.welcome .panel-body img
{
	height: 4em;
	margin-bottom : 1em;
}

.survey-action .panel-body img
{
    height : 3em;
}

div.panel.disabled, div.panel.disabled  *{
    opacity : 0.5 ;
    border: none;
}

div.panel.disabled a{
    cursor:default;
    
}

/**
 *     User control
 */

@media screen and (min-width: 1280px) and (max-width: 1366px) 
{
    #user-control-table .form-group label
    {
          min-width: 80px;
    }
    
    #add_user_btn
    {
        margin-top: 1.5em;
    }
}

/**
 * 	Edit group
 */
#edit-group .tab-pane
{
	padding: 1em;
}

.htmleditorboot
{
	padding-top : 2em;
}

/**
 * 	Edit question
 */
#edit-question-body
{
	min-height: 1200px;
}

/**
 * 	Login
 */

#profile-img
{
    min-height : 80px;    
}

@media screen and (min-width: 1280px) and (max-width: 1680px) 
{
    #profile-img
    {
        min-height : 0;    
    }
}


.login-pannel
{
	margin-top: 40px;
}

.welcome .login-pannel .panel-body  img{
	margin-bottom: 0px;
}

.login-title 
{
	border-bottom : solid 1px #DADADA; 
}

.login-content 
{
	
	text-align : left;
	padding : 1em;
}


.login-submit 
{
	border-top : solid 1px #DADADA;
	text-align: right; 
}

#s2id_loginlang
{
	border :none;
	padding : 0 ;
}


.side-body, .full-page-wrapper
{
	position : relative;
	margin-bottom : 65px;
}


.message-box
{
	border : 1px solid #89C68D;
	color : #2D2D2D;

	position : relative;
    -webkit-animation: slidefromtop 1s; /* Safari, Chrome and Opera > 12.1 */
       -moz-animation: slidefromtop 1s; /* Firefox < 16 */
        -ms-animation: slidefromtop 1s; /* Internet Explorer */
         -o-animation: slidefromtop 1s; /* Opera < 12.1 */
            animation: slidefromtop 1s;		
	
}

.message-box-error
{
	border : 1px solid #A0352F;
}


.panel-clickable:hover
{
    cursor: pointer; cursor: hand; 
}

.pagination 
{
    font-size: 1.2em;
}


</style>









<script>
// MegaMenu
$(document).ready(function(){
    
    $(".panel-clickable").click(function(){
        $that = $(this);
        if($that.attr('aria-data-url')!=''){
            window.location.href = $that.attr('aria-data-url');
        }
    });
    
    
    $('#question_type_button  li a').click(function(){
        $(".btn:first-child .buttontext").text($(this).text());
        $('#question_type').val($(this).attr('aria-data-value'));
        updatequestionattributes();
   });
    
	$('#template').on('change', function(event){
		templatechange($(this).val());
	});
	
	$("#save-form-button").on('click', function(){
		var formid = '#'+$(this).attr('aria-data-form-id');
		$form = $(formid);
		$form.find('[type="submit"]').trigger('click');;
	});
		
	
	$('#save-button').on('click', function()
	{
	    if($(this).attr('data-use-form-id')==1)
	    {
	        formId = '#'+$(this).attr('data-form-to-save');
	        $(formId).submit();
	    }
	    else
	    {
		  $form = $('.side-body').find('form');
		  $form.submit();
		}
		
	});
	
	$('#sort-questions-button').on('click', function(e){
        var url = $(this).attr('aria-url');    
        $(location).attr('href',url)		
	});
	
	$('#pannel-1').animate({opacity: 1, top: '0px'}, 200, function(){
			$('#pannel-2').animate({opacity: 1, top: '0px'}, 200, function(){
				$('#pannel-3').animate({opacity: 1, top: '0px'}, 200, function(){
					$('#pannel-4').animate({opacity: 1, top: '0px'}, 200, function(){
						$('#pannel-5').animate({opacity: 1, top: '0px'}, 200, function(){
							$('#pannel-6').animate({opacity: 1, top: '0px'}, 200, function(){});
						});
					});
				});
			});
	});
	
         
$('.btntooltip').tooltip();


$('.open-preview').on('click', function(){
	//http://local.lsinst/LimeSurvey_206/index.php/survey/index/action/previewquestion/sid282267/gid/1/qid/1
	//http://local.lsinst/limesurvey/   /index.php/survey/index/action/previewquestion/sid/282267/gid/1/qid/1
	
													///survey/index/action/previewquestion/sid/838454/gid/7/qid/174
		
		
		//var frameSrc = '<?php echo $this->createUrl("survey/index/action/previewquestion/sid/"); ?>';
		//frameSrc += '/'+$(this).attr("aria-data-sid")+'/gid/'+$(this).attr("aria-data-gid")+'/qid/'+$(this).attr("aria-data-qid");
		
		var frameSrc = $(this).attr("aria-data-url");
		
		$('#frame-question-preview').attr('src',frameSrc);
		//$('#myModalLabel').append(frameSrc);
		$('#question-preview').modal('show');
	});



// Collapse in editarticle 
$('#questionTypeContainer').css("overflow","visible");
$('#collapseOne').on('shown.bs.collapse', function () {
	$('#questionTypeContainer').css("overflow","visible")
})

$('#collapseOne').on('hide.bs.collapse', function () {
  $('#questionTypeContainer').css("overflow","hidden")
})
	
<?php 
    $surveyid = (isset($surveyid))?$surveyid:'';
?>
/* Switch format group */
$('#switchchangeformat').on('switchChange.bootstrapSwitch', function(event, state) {
    $.ajax({
        url : '<?php echo $this->createUrl("admin/survey/sa/changeFormat/surveyid/".$surveyid); ?>',
        type : 'GET',
        dataType : 'html', 
        
        // html contains the buttons
        success : function(html, statut){
        },
        error :  function(html, statut){
            alert('error');
        }
    });
   
});

/* Slide last survey / question  */
function rotateLast(){
    $rotateShown = $('.rotateShown');
    $rotateHidden = $('.rotateHidden');
    $rotateShown.hide('slide', { direction: 'left', easing: 'easeInOutQuint'}, 500, function(){
        $rotateHidden.show('slide', { direction: 'right', easing: 'easeInOutQuint' }, 1000)
    });
    
    $rotateShown.removeClass('rotateShown').addClass('rotateHidden');
    $rotateHidden.removeClass('rotateHidden').addClass('rotateShown');
    window.setTimeout( rotateLast, 5000 );
    /*
    window.setTimeout( function(){
    $rotateHidden.removeClass('rotateHidden').addClass('rotateShown');
    window.setTimeout( rotateLast, 2000 );
    },  2000 );     
     */
 }

 if ( $( "#last_question" ).length ) {
     $('.rotateHidden').hide();
     window.setTimeout( rotateLast, 2000 );
 }
    
});	



var frameSrc = "/login";
</script>

































	
	
<?php if(isset($formatdata)) { ?>
    <script type='text/javascript'>
        var userdateformat='<?php echo $formatdata['jsdate']; ?>';
        var userlanguage='<?php echo $adminlang; ?>';
    </script>
    <?php } ?>
