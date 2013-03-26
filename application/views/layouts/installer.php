<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="author" content="" />
    <link rel="stylesheet" href="<?php echo Yii::app()->getConfig('adminstyleurl'); ?>grid.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo Yii::app()->getConfig('adminstyleurl'); ?>adminstyle.css" type="text/css" media="all" />
    <link rel="shortcut icon" href="<?php echo Yii::app()->baseUrl; ?>/styles/admin/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo $this->createUrl('/');?>styles/admin/favicon.ico" type="image/x-icon" />
	 <!--<link rel="stylesheet" href="http://static.jquery.com/ui/css/demo-docs-theme/ui.theme.css" type="text/css" media="all" />  -->
    <?php
        App()->getClientScript()->registerPackage('jqueryui');
        /*
        $script = "$(function() {
        $('.on').animate({
					color: '#0B55C4'
				}, 1000 );

        $('.demo').find('a:first').button().end().
            find('a:eq(1)').button().end().
            find('a:eq(2)').button();
        });";
         *
         */

    ?>
    <script type="text/javascript">


 	</script>
	<link rel="icon" href="<?php echo Yii::app()->baseUrl; ?>/images/favicon.ico" />
	<title><?php $this->lang->eT("LimeSurvey installer"); ?></title>
</head>

<body class="body">

<div class="container_6">
<div class="grid_6 header"><?php $this->lang->eT("LimeSurvey installer"); ?></div>

</div>
<div class="container_6">
&nbsp;
</div>
<?php echo $content; ?>
<div class="container_6">
&nbsp;
</div>
<div class="container_6">
<div class="grid_6">
    <center>
        <br />
        <img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/poweredby.png" alt="Powered by LimeSurvey" />
    </center>
</div>

</div>


</body>
</html>