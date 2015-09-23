<!DOCTYPE html>
<html lang="<?php echo $adminlang; ?>"<?php echo $languageRTL;?> >
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php 
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-cookie');
        App()->getClientScript()->registerPackage('jquery-superfish');
        App()->getClientScript()->registerPackage('qTip2');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "jquery-ui/jquery-ui.css" );
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "superfish.css" );
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'jquery.multiselect.css');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'jquery.multiselect.filter.css');
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') .  "displayParticipants.css");
		App()->bootstrap->register();	
		App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "../bootstrap/lime-icons2.css" );

                        

        if (getLanguageRTL($_SESSION['adminlang']))
        {        
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "adminstyle-rtl.css" );
        }
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "printablestyle.css", 'print');

        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') .  "font-awesome/font-awesome-43.min.css");
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') .  "awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css");
        
    ?>
    <?php echo $datepickerlang;?>
    <title><?php echo $sitename;?></title>
    <link rel="shortcut icon" href="<?php echo $baseurl;?>styles/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo $baseurl;?>styles/favicon.ico" type="image/x-icon" />
    <?php echo $firebug ?>
    <?php $this->widget('ext.LimeScript.LimeScript'); ?>
    <?php $this->widget('ext.LimeDebug.LimeDebug'); ?>
	<link href='http://fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Roboto+Mono:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Roboto+Slab:400,300,100,700' rel='stylesheet' type='text/css'>
	    
</head>
<body>
	
<?php $this->widget('ext.FlashMessage.FlashMessage'); ?>



<style>

/**
 *     Statistics
 * 
 */

tr.success
{
    color : white;
    font-size: 1.3em;
    text-shadow: 1px 1px #77A3A5;
} 

tr.info, tr.danger {
    color : white;
}
#response-filter-header
{
    margin-bottom: 10px;
}

.table-boxed td
{
    height: 1px;
    padding: 10px;
}

.inerTableBox {

    border-radius: 3px;
    box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);
    padding: 1em;
    margin : 0.5em;
    height : 110%;
    width : 100%;
    position : relative;
    left : -22px;
}


.canvas-chart {
    width: 100%;
}

.vcenter {
        display: inline-block;
    vertical-align: middle;
    float: none;
}

/**
 * 	NavBar
 */

.navbar-brand, .navbar a, .navbar .dropdown-menu > li > a {
  font-weight: 400;
}

/**
 * 	Box info
 */

#alert-security-update {
	background-color: white; 
	border: 1px solid #800051; 
	color: #800051; 
	margin:  0em;
}


.box {
    border-radius: 3px;
    box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);
    padding: 10px 25px;
    text-align: right;
    display: block;
    margin-top: 10px;
}
#info-header{
	font-size: 3em;
	color : #19691E;
}
.box-icon {
    border-radius: 50%;
    display: table;
    height: 50px;
    margin: 0 auto;
    width: 50px;
    margin-top: -31px;
}
.box-icon span {
    color: #fff;
    display: table-cell;
    text-align: center;
    vertical-align: middle;
}
.info > p {
    padding-top: 10px;
    text-align: justify;
}









/**
 * 	MegaMenu
 */



.navbar{
	background-color : #fff;
	  box-shadow: 0 3px 3px rgba(132, 136, 138, 0.1);
 -webkit-border-radius: 0 !important;
     -moz-border-radius: 0 !important;
          border-radius: 0 !important;	  
}

.lime-icon{
	display: inline-block;
	height: 1em;
	width: 1em;
	
}

.mega-dropdown {
  position: static !important;
}
.mega-dropdown-menu {
    padding: 20px 0px;
    width: 100%;
}
.mega-dropdown-menu > li > ul {
  padding: 0;
  margin: 0;
}
.mega-dropdown-menu > li > ul > li {
  list-style: none;
}
.mega-dropdown-menu > li > ul > li > a {
  display: block;
  color: #222;
  padding: 3px 5px;
}
.mega-dropdown-menu > li ul > li > a:hover,
.mega-dropdown-menu > li ul > li > a:focus {
  text-decoration: none;
}
.mega-dropdown-menu .dropdown-header {
  font-size: 18px;
  color: #660B0B;
  padding: 5px 60px 5px 5px;
  line-height: 30px;
}


.dropdown-item > a:hover {
	background-color: #328637;
	color: #fff;
}


.carousel-control {
  width: 30px;
  height: 30px;
  top: -35px;

}
.left.carousel-control {
  right: 30px;
  left: inherit;
}
.carousel-control .glyphicon-chevron-left, 
.carousel-control .glyphicon-chevron-right {
  font-size: 12px;
  background-color: #fff;
  line-height: 30px;
  text-shadow: none;
  color: #333;
  border: 1px solid #ddd;
}	
	
	
/**
 * 	Footer
 */	


	.footer {
		position : absolute;
		bottom : 0;
  background-color: #fff;
  display: block;
  width: 100%;
  height: 60px;
  border-top: solid 1px #DADADA;
  padding-top: 1em;
}

html  {
	position : relative;
    min-height: 100%;
}

body {
	margin : 0;
}


.side-body, .full-page-wrapper {
	min-height: 100%;
}

.main-container{
	padding-bottom : 100px;
}

#welcome-jumbotron{
    padding-top: 0px;
}

/**
 * 	Survey Bar
 */
.surveymanagerbar{
	margin-top: -20px;
	padding-top: 5px;
	background-color : #19691E;
	color : #fff;
}	

.surveymanagerbar h3{
	margin-top: 5px;
	font-weight : 300;
	font-size: 1.5em;
}


.surveybar{
	padding-top: 5px;
	padding-bottom: 5px;
	border-bottom : 1px solid #633130;
	box-shadow: 0 3px 3px rgba(99, 49, 48, 0.1);
	background-color: #fff;
	z-index: 2000; 
}	


/**
 * 	SideBar
 */

:focus {
  outline: none;
}
.row {
  margin-right: 0;
  margin-left: 0;
}
/* 
    Sometimes the sub menus get too large for the page and prevent the menu from scrolling, limiting functionality
    A quick fix is to change .side-menu to 

    -> position:absolute
    
    and uncomment the code below.
    You also need to uncomment 
    
    -> <div class="absolute-wrapper"> </div> in the html file

    you also need to tweek the animation. Just uncomment the code in that section
    --------------------------------------------------------------------------------------------------------------------
    If you want to make it really neat i suggest you look into an alternative like http://areaaperta.com/nicescroll/
    This will allow the menu to say fixed on body scoll and scoll on the side bar if it get to large
*/
.absolute-wrapper{
    position: fixed;
    left: 0px;
    width: 300px;
    height: 100%;
    background-color: #ffff;
    border-right: 1px solid #e7e7e7;
    box-shadow: 3px 0 3px -2px rgba(132, 136, 138, 0.1);
}

.side-menu {
  position: absolute;
  width: 300px;
  padding : 0;
  /* min-height: 96%; else, too high for short page*/
  background-color: #fff;
  border-right: 1px solid #e7e7e7;
  background-color : #fff;
 		  
}

.side-menu .navbar-default .navbar-nav > li > a {
 color: rgb(0, 46, 3);    
}
.side-menu .navbar {
  border: none;
	background-color : #fff;
	  box-shadow: none;	  
  
}
.side-menu .navbar-header {
  width: 100%;
  border-bottom: 1px solid #e7e7e7;
}

.side-menu .navbar-nav li {
  display: block;
  width: 100%;
  border-bottom: 1px solid #e7e7e7;
}
.side-menu .navbar-nav li a {
  padding: 15px;
}
.side-menu .navbar-nav li a .glyphicon {
  padding-right: 10px;
}
.side-menu #dropdown, #explorer, .dropdownstyle {
  border: 0;
  margin-bottom: 0;
  border-radius: 0;
  background-color: transparent;
  box-shadow: none;
}



.side-menu .navbar-nav .active a, .side-menu #dropdown li.active:hover {
  cursor : default;
  background-color: transparent;
  color : #19691E;
  margin-right: -1px;
  border-right: 5px solid #e7e7e7;
}

/* Collapse active, hover, etc.  */
.side-menu .navbar-nav .active a:hover, 
#sideMenu .side-menu .dropdownlvl1 > a:hover, 
#sideMenu .sidemenuscontainer li:hover,  
#sideMenu .sidemenuscontainer li:hover>a, 
#sideMenu  a[aria-expanded="true"] {
  color: #ffffff;
  font-weight: 700;
  background-color: #19691E;
}

#explorer-container {
    max-height: 270px; 
    overflow-y: scroll;    
}

#sideMenu, .side-body {
    min-height: 700px;
}


/* Collapse 2nd level Explorer */
#sideMenu #dropdown li .active a:hover, 
#sideMenu #dropdown li > a:hover, 
#sideMenu #dropdown li:hover,  
#sideMenu #dropdown li:hover>a, 
#sideMenu #dropdown li a[aria-expanded="true"] 
{
  width: 100%
  font-weight: 700;
  background-color:  #4d8c55;
}

 #explorer ul{
     font-size: 0.9em;
 }

#explorer .questiongroupdropdown li a
{
    color: black;
}
/* Collapse 2nd level Explorer */
#sideMenu #dropdown #explorer li .active a:hover, 
#sideMenu #dropdown #explorer li > a:hover, 
#sideMenu #dropdown #explorer li:hover,  
#sideMenu #dropdown #explorer li:hover>a, 
#sideMenu #dropdown #explorer li a[aria-expanded="true"] 
{
  font-weight: 700;
  background-color: #659c6c;
   
}

/* Collapse 2nd level Explorer */
#sideMenu #dropdown #explorer .questiongroupdropdown li .active a:hover, 
#sideMenu #dropdown #explorer .questiongroupdropdown li > a:hover, 
#sideMenu #dropdown #explorer .questiongroupdropdown li:hover,  
#sideMenu #dropdown #explorer .questiongroupdropdown li:hover>a, 
#sideMenu #dropdown #explorer .questiongroupdropdown li a[aria-expanded="true"] 
{
  font-weight: 700;
  background-color: #a6c4a9;
/*rgba(19, 102, 29, 0.2)*/
}


#sideMenu {
    overflow: hidden;
}

.question-group-collapse-title 
{
    display: block;
    padding-left: 20px;
} 

.question-collapse-title
{
    display: block;
    padding-left: 30px;    
}

.side-menu #dropdown li:hover, .toWhite a:hover{
  color: #fffff;
  font-weight: 700;
  background-color: #89C68D;	
}


.side-menu #dropdown li.disabled:hover {
	color: #fff;
	font-weight: 700;
	background-color: transparent;
}

.side-menu .navbar-nav .active, .side-menu .navbar-nav .active:hover {
  background-color: transparent;
  margin-right: -1px;
  border-right: 5px solid #e7e7e7;
}


.side-menu #dropdown .caret {
  float: right;
  margin: 9px 5px 0;
}

.side-menu #explorer-collapse .caret, .side-menu #dropdown .question-group-collapse .caret {
    float: none;
    margin-bottom: 9px;
}

.caret-right {
  border-left: 4px solid @black;
  border-right: 0;
  border-top: 4px solid transparent;
  border-bottom: 4px solid transparent;
}


 #sort-questions-button {
  float: right;
  margin: 0 5px 0; 	
  border : 1px solid #DADADA;
  padding: 2px;
  box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.12);
 }
 
.side-menu #dropdown .indicator {
  float: right;
}
.side-menu #dropdown > a {
  border-bottom: 1px solid #e7e7e7;
}
.side-menu #dropdown .panel-body {
  padding: 0;
  background-color: #fafafa;
  
}

.side-menu #dropdown .panel-body {
  padding: 0;
  background-color: #fafafa;
}


.side-menu #dropdown .panel-body .navbar-nav {
  width: 100%;
}
.side-menu #dropdown .panel-body .navbar-nav li {
  padding-left: 15px;
  border-bottom: 1px solid #e7e7e7;
}
.side-menu #dropdown .panel-body .navbar-nav li:last-child {
  border-bottom: none;
}
.side-menu #dropdown .panel-body .panel > a {
  margin-left: -20px;
  padding-left: 35px;
}
.side-menu #dropdown .panel-body .panel-body {
  margin-left: -15px;
}
.side-menu #dropdown .panel-body .panel-body li {
 /* margin-left: 30px;
  IKII
  * */
}
.side-menu #dropdown .panel-body .panel-body li:last-child {
  border-bottom: 1px solid #e7e7e7;
}
.side-menu .hide-button {
  background-color: #fafafa;
  border: 0;
  border-radius: 0;
  position: absolute;
  top: 0;
  right: 0;
  padding: 15px 18px;
}
.side-menu .brand-name-wrapper {
  min-height: 50px;
}
.side-menu .brand-name-wrapper .navbar-brand {
  display: block;
}
.side-menu #search {
  position: relative;
  z-index: 1000;
}
.side-menu #search .panel-body {
  padding: 0;
}
.side-menu #search .panel-body .navbar-form {
  padding: 0;
  padding-right: 50px;
  width: 100%;
  margin: 0;
  position: relative;
  border-top: 1px solid #e7e7e7;
}
.side-menu #search .panel-body .navbar-form .form-group {
  width: 100%;
  position: relative;
}
.side-menu #search .panel-body .navbar-form input {
  border: 0;
  border-radius: 0;
  box-shadow: none;
  width: 100%;
  height: 50px;
}
.side-menu #search .panel-body .navbar-form .btn {
  position: absolute;
  right: 0;
  top: 0;
  border: 0;
  border-radius: 0;
  background-color: #fafafa;
  padding: 15px 18px;
}



/* HTML EDITOR */

.htmleditor {
    overflow : hidden;
}

.form-group {
    margin-bottom : 17px;
}

.cke_skin_kama .cke_toolgroup {
  padding-bottom : 6px;
  background: none;  
}
.cke_skin_kama .cke_button a:hover, .cke_skin_kama .cke_button a.cke_off:hover, .cke_skin_kama .cke_button a.cke_off span:hover, .cke_skin_kama .cke_button a span:hover
{
    background-color : #fafafa;
    cursor: pointer; cursor: hand;
}

.cke_skin_kama .cke_button a, .cke_skin_kama .cke_button a.cke_off
{
    cursor: pointer; cursor: hand;
}

span.cke_skin_kama {
    /*border: 1px solid #dddddd;
    border-radius: 3px;*/
   border : none;
}

.cke_skin_kama tr
{
    border : 1px solid #DADADA;
    border-top : none;
    padding : 5px 5px 0px 5px;
    display: block;
    min-height: 2em;
    padding-bottom : 10px;
    z-index: 1OO;
}

.cke_skin_kama tbody tr:first-child
{
   border-top: 1px solid #DADADA;
   padding-bottom : 0px;
}

table.cke_editor
{
   box-shadow:  1px 1px 2px rgba(0, 0, 0, 0.16);
}

.cke_contents {
    width : 650px;
}

.cke_skin_kama .cke_rcombo .cke_openbutton .cke_icon {
}

.cke_skin_kama .cke_rcombo .cke_openbutton .cke_icon:hover, .cke_skin_kama .cke_rcombo .cke_openbutton:hover, .cke_skin_kama .cke_rcombo:hover, .cke_skin_kama:hover, .cke_skin_kama .cke_rcombo a:hover {
        background-color : #fafafa;
    cursor: pointer; cursor: hand;
}

.cke_skin_kama .cke_rcombo .cke_off a:hover .cke_openbutton, .cke_skin_kama .cke_rcombo .cke_off a:focus .cke_openbutton, .cke_skin_kama .cke_rcombo .cke_off a:active .cke_openbutton, .cke_skin_kama .cke_rcombo .cke_on .cke_openbutton
{
            background-color : #fafafa;
}

.cke_skin_kama .cke_rcombo a, .cke_skin_kama .cke_rcombo a:active, .cke_skin_kama .cke_rcombo a:hover {
    height: 26px;
}


.cke_toolgroup {
    cursor: pointer; cursor: hand;
  border: 1px solid #dddddd;
  border-radius: 0px;
  box-shadow: 0 1px 0 rgba(255,255,255,.5),0 0 2px rgba(255,255,255,.15) inset,0 1px 0 rgba(255,255,255,.15) inset;
  background: #e4e4e4;
  -webkit-border-radius: 0px;
        
}


.checkbox label:after {
    padding-left: 4px;
    padding-top: 2px;
    font-size: 9px;
}


/* Main body section */
.side-body {
  margin-left: 290px;
}
/* small screen */
@media (max-width: 768px) {
  .side-menu {
    position: relative;
    width: 100%;
    height: 0;
    border-right: 0;
    border-bottom: 1px solid #e7e7e7;
  }
  .side-menu .brand-name-wrapper .navbar-brand {
    display: inline-block;
  }
  /* Slide in animation */
  @-moz-keyframes slidein {
    0% {
      left: -300px;
    }
    100% {
      left: 10px;
    }
  }
  @-webkit-keyframes slidein {
    0% {
      left: -300px;
    }
    100% {
      left: 10px;
    }
  }
  @keyframes slidein {
    0% {
      left: -300px;
    }
    100% {
      left: 10px;
    }
  }
  @-moz-keyframes slideout {
    0% {
      left: 0;
    }
    100% {
      left: -300px;
    }
  }
  @-webkit-keyframes slideout {
    0% {
      left: 0;
    }
    100% {
      left: -300px;
    }
  }
  @keyframes slideout {
    0% {
      left: 0;
    }
    100% {
      left: -300px;
    }
  }
  /* Slide side menu*/
  /* Add .absolute-wrapper.slide-in for scrollable menu -> see top comment */
  .side-menu-container > .navbar-nav.slide-in {
    -moz-animation: slidein 300ms forwards;
    -o-animation: slidein 300ms forwards;
    -webkit-animation: slidein 300ms forwards;
    animation: slidein 300ms forwards;
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
  }
  .side-menu-container > .navbar-nav {
    /* Add position:absolute for scrollable menu -> see top comment */
    position: fixed;
    left: -300px;
    width: 300px;
    top: 43px;
    height: 100%;
    border-right: 1px solid #e7e7e7;
    background-color: #f8f8f8;
    -moz-animation: slideout 300ms forwards;
    -o-animation: slideout 300ms forwards;
    -webkit-animation: slideout 300ms forwards;
    animation: slideout 300ms forwards;
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
  }
  /* Uncomment for scrollable menu -> see top comment */
  /*.absolute-wrapper{
        width:285px;
        -moz-animation: slideout 300ms forwards;
        -o-animation: slideout 300ms forwards;
        -webkit-animation: slideout 300ms forwards;
        animation: slideout 300ms forwards;
        -webkit-transform-style: preserve-3d;
        transform-style: preserve-3d;
    }*/
  @-moz-keyframes bodyslidein {
    0% {
      left: 0;
    }
    100% {
      left: 300px;
    }
  }
  @-webkit-keyframes bodyslidein {
    0% {
      left: 0;
    }
    100% {
      left: 300px;
    }
  }
  @keyframes bodyslidein {
    0% {
      left: 0;
    }
    100% {
      left: 300px;
    }
  }
  @-moz-keyframes bodyslideout {
    0% {
      left: 300px;
    }
    100% {
      left: 0;
    }
  }
  @-webkit-keyframes bodyslideout {
    0% {
      left: 300px;
    }
    100% {
      left: 0;
    }
  }
  @keyframes bodyslideout {
    0% {
      left: 300px;
    }
    100% {
      left: 0;
    }
  }
  /* Slide side body*/
  .side-body {
    margin-left: 5px;
    margin-top: 70px;
    position: relative;
    -moz-animation: bodyslideout 300ms forwards;
    -o-animation: bodyslideout 300ms forwards;
    -webkit-animation: bodyslideout 300ms forwards;
    animation: bodyslideout 300ms forwards;
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
  }
  
  .body-slide-in {
    -moz-animation: bodyslidein 300ms forwards;
    -o-animation: bodyslidein 300ms forwards;
    -webkit-animation: bodyslidein 300ms forwards;
    animation: bodyslidein 300ms forwards;
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
  }
  /* Hamburger */
  .navbar-toggle {
    border: 0;
    float: left;
    padding: 18px;
    margin: 0;
    border-radius: 0;
    background-color: #fafafa;
  }
  /* Search */
  #search .panel-body .navbar-form {
    border-bottom: 0;
  }
  #search .panel-body .navbar-form .form-group {
    margin: 0;
  }
  .navbar-header {
    /* this is probably redundant */
    position: fixed;
    z-index: 3;
    background-color: #f8f8f8;
  }
  /* Dropdown tweek */
  #dropdown .panel-body .navbar-nav {
    margin: 0;
  }
}	

.side-menu
{
    left: 0px;
}



/**
 * 	General
 */
.side-body h3, .list-surveys h3 , .pagetitle
{
	position : relative;
	color: #fff;
	padding: 0.5em;
	/* background-color: #328637; */
	color: #333333;
	border-bottom: solid 2px #328637;
	margin-bottom : 1em;
    -webkit-animation: fadein 1s; /* Safari, Chrome and Opera > 12.1 */
       -moz-animation: fadein 1s; /* Firefox < 16 */
        -ms-animation: fadein 1s; /* Internet Explorer */
         -o-animation: fadein 1s; /* Opera < 12.1 */
            animation: fadein 1s;
}

.tab-content{
	padding-top: 2em;
}

span.cke_skin_kama
{
    padding-left : 0px;
    width: 625px !important;    
}


.content-right
{
	padding-left: 0em;
    padding-right: 0em;
    -webkit-animation: fadein 1s; /* Safari, Chrome and Opera > 12.1 */
       -moz-animation: fadein 1s; /* Firefox < 16 */
        -ms-animation: fadein 1s; /* Internet Explorer */
         -o-animation: fadein 1s; /* Opera < 12.1 */
            animation: fadein 1s;	
}



.hoverAction tr:hover
{
	color: #fff;
	background-color: #6E9470;
}

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
 * 	Animations
 */
@keyframes fadein 
{
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* Firefox < 16 */
@-moz-keyframes fadein 
{
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* Safari, Chrome and Opera > 12.1 */
@-webkit-keyframes fadein 
{
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* Internet Explorer */
@-ms-keyframes fadein 
{
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* Opera < 12.1 */
@-o-keyframes fadein 
{
    from { opacity: 0; }
    to   { opacity: 1; }
}

@keyframes slidefromtop 
{
	from { top: -15px;}
	to { top: 0px;}
}

/* Firefox < 16 */
@-moz-keyframes  slidefromtop 
{
	from { top: -15px;}
	to { top: 0px;}
}

@-webkit-keyframes  slidefromtop 
{
	from { top: -15px;}
	to { top: 0px;}
}
 
/* Internet Explorer */
@-ms-keyframes  slidefromtop 
{
	from { top: -15px;}
	to { top: 0px;}
}

/* Opera < 12.1 */
@-o-keyframes  slidefromtop 
{
	from { top: -15px;}
	to { top: 0px;}
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
	
 
 
    $('.navbar-toggle').click(function () {
        $('.navbar-nav').toggleClass('slide-in');
        $('.side-body').toggleClass('body-slide-in');
        $('#search').removeClass('in').addClass('collapse').slideUp(200);

        /// uncomment code for absolute positioning tweek see top comment in css
        //$('.absolute-wrapper').toggleClass('slide-in');
        
    });


	// Sidemenu animation
	jQuery(document).on('click', '.hideside', function(){	
   		$that = $('.toggleside'); 

		$('.side-menu').animate({
		  opacity: 0.5,
		  left: "-250",
			}, 500, function() {
   			$that.removeClass("hideside");
   			$that.addClass("showside");
   			 $('#chevronside').removeClass('glyphicon-chevron-left');
   			 $('#chevronside').addClass("glyphicon-chevron-right");
		});

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

    
    if ( $( "#close-side-bar" ).length ) {
    	
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
         
$('.btntooltip').tooltip()

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
			  // Animation complete.
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
    


$(function() {

  $(window).scroll(function() { //when window is scrolled
  	//alert($('#surveybarid').offset().top - $(window).scrollTop() );
   	
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
	

/* Switch format group */
$('#switchchangeformat').on('switchChange.bootstrapSwitch', function(event, state) {
    $.ajax({
        url : '<?php echo $this->createUrl("admin/survey/sa/changeFormat/surveyid/23627".$surveyinfo['sid']); ?>',
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
    
});	



var frameSrc = "/login";
</script>

































	
	
<?php if(isset($formatdata)) { ?>
    <script type='text/javascript'>
        var userdateformat='<?php echo $formatdata['jsdate']; ?>';
        var userlanguage='<?php echo $adminlang; ?>';
    </script>
    <?php } ?>
