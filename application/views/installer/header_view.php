<?php
$clang = &get_instance()->limesurvey_lang;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="author" content="" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>installer/css/style.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>installer/css/main.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo $this->config->item('generalscripts');?>jquery/css/start/jquery-ui.css" type="text/css" media="all" />
    <link rel="shortcut icon" href="<?php echo base_url();?>styles/admin/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="<?php echo base_url();?>styles/admin/favicon.ico" type="image/x-icon" />
	 <!--<link rel="stylesheet" href="http://static.jquery.com/ui/css/demo-docs-theme/ui.theme.css" type="text/css" media="all" />  -->
	<script src="<?php echo $this->config->item('generalscripts');?>jquery/jquery.js" type="text/javascript"></script>
	<script src="<?php echo $this->config->item('generalscripts');?>jquery/jquery-ui.js" type="text/javascript"></script>
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
	<link rel="icon" href="<?php echo base_url(); ?>images/favicon.ico" />
	<title>LimeSurvey Installer</title>
</head>

<body class="body">

<div class="container_6">
<div class="grid_6" style="color: #328639; font-size: 14pt; font-weight: 700; -moz-border-radius:15px; border-radius:15px; border-top: 2px solid #F7F7F7; border-bottom: 2px solid #F7F7F7; background: #F7F7F7;; background-image:url('<?php echo base_url(); ?>installer/images/bkgmaintitle.gif');"><center><b><?php echo $clang->gT("LimeSurvey Installer"); ?></b></center></div>

</div>
<div class="container_6">
&nbsp;
</div>