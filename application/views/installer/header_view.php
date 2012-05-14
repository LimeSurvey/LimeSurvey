<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="author" content="" />
    <link rel="stylesheet" href="<?php echo Yii::app()->getConfig('adminstyleurl'); ?>grid.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo Yii::app()->getConfig('adminstyleurl'); ?>adminstyle.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo Yii::app()->getConfig('adminstyleurl');?>jquery-ui/jquery-ui.css" type="text/css" media="all" />
    <link rel="shortcut icon" href="<?php echo Yii::app()->baseUrl; ?>/styles/admin/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo $this->createUrl('/');?>styles/admin/favicon.ico" type="image/x-icon" />
	 <!--<link rel="stylesheet" href="http://static.jquery.com/ui/css/demo-docs-theme/ui.theme.css" type="text/css" media="all" />  -->
	<script src="<?php echo Yii::app()->getConfig('generalscripts');?>jquery/jquery.js" type="text/javascript"></script>
	<script src="<?php echo Yii::app()->getConfig('generalscripts');?>jquery/jquery-ui.js" type="text/javascript"></script>
    <script type="text/javascript">
	$(function() {

		$( "#progressbar" ).progressbar({
			value: <?php echo $progressValue ; ?>
		});

        $(".on").animate({
					color: "#0B55C4"
				}, 1000 );

        $('.demo').find('a:first').button().end().
            find('a:eq(1)').button().end().
            find('a:eq(2)').button();
	});

 	</script>
	<link rel="icon" href="<?php echo Yii::app()->baseUrl; ?>/images/favicon.ico" />
	<title><?php $clang->eT("LimeSurvey installer"); ?></title>
</head>

<body class="body">

<div class="container_6">
<div class="grid_6 header"><?php $clang->eT("LimeSurvey installer"); ?></div>

</div>
<div class="container_6">
&nbsp;
</div>