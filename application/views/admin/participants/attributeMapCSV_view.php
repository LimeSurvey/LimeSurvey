<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('styleurl')."admin/default/adminstyle.css" ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('styleurl')."admin/default/attributeMapCSV.css" ?>" />
        <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery.js" ?>" type="text/javascript"></script>
        <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery-ui.js" ?>" type="text/javascript"></script>
        <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery.qtip.js" ?>" type="text/javascript"></script>
        <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery.ui.sortable.js" ?>" type="text/javascript"></script>
        <script src="<?php echo $this->config->item('adminscripts')."attributeMapCSV.js" ?>" type="text/javascript"></script>
        <script type="text/javascript">mapCSVcancelled

            var copyUrl = "<?php echo site_url("admin/participants/uploadCSV");?>";
            var displayParticipants = "<?php echo site_url("admin/participants/displayParticipants");?>";
            var mapCSVcancelled = "<?php echo site_url("admin/participants/mapCSVcancelled");?>";
            var characterset = "<?php echo $this->input->post('characterset'); ?>";
            var okBtn = "<?php echo $clang->gT("OK") ?>";
            var processed = "<?php echo $clang->gT("Summary") ?>";
            var summary = "<?php echo $clang->gT("Upload summary") ?>";
            var seperator = "<?php echo $this->input->post('seperatorused'); ?>";
            var thefilepath = "<?php echo $fullfilepath ?>";
       </script>
  </head>
<body>
<div class='header ui-widget-header'><strong><?php echo sprintf($clang->gT("Select attributes to copy with your %s participant(s)"),$linecount);?></strong></div>
<div class="main">
    <div id="csvattribute">
    <div class="heading"><?php echo $clang->gT("CSV headings"); ?></div>
        <ul class="csvatt">
        <?php
            foreach($firstline as $key=>$value)
            {
                echo "<li id='cs_".$value."' name='cs_".$value."' >".$value."</li>";
             }?>
        </ul>
     </div>
<div id="newcreated"><div class="heading"><?php echo $clang->gT("Attributes to be created") ?></div>
     <ul class="newcreate" id="sortable" style ="height: 40px">
    </ul>
</div>
    <div id="centralattribute"><div class="heading"><?php echo $clang->gT("Central attribute");?></div>
    <ul class="cpdbatt">
    <?php
        foreach($attributes as $key=>$value)
        {
            echo "<li id='c_".$value['attribute_id']."' name='c_".$key."' >".$value['attribute_name']."</li>";
        }
    ?>
    </ul>
    </div>
    </ul>
  </div>
   <p><input type="button" name="attmap" id="attmap" value="Continue" />
   <input type="button" name="attmapcancel" id="attmapcancel" value="Cancel" />
   </p>
    <?php $ajaxloader = array(
          'src' => 'images/ajax-loader.gif',
          'alt' => 'Ajax Loader',
          'title' => 'Ajax Loader'
          );?>
    <div id="processing" title="<?php echo $clang->gT("Processing .....") ?>" style="display:none">
    <?php echo img($ajaxloader); ?>
    </div>
 </body>
</html>







