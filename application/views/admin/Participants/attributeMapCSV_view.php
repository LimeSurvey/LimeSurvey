<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/adminstyle.css")?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/attributeMapCSV.css")?>" />
        <script src="<?php echo site_url("scripts/jquery/jquery.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery-ui.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery.qtip.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery.ui.sortable.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/admin/attributeMapCSV.js")?>" type="text/javascript"></script>
        <script type="text/javascript">
            var redUrl = "<?php echo site_url("admin/participants/summaryview");?>";
            var copyUrl = "<?php echo site_url("admin/participants/uploadCSV");?>";
            var characterset = "<?php echo $this->input->post('characterset'); ?>";
            var seperator = "<?php echo $this->input->post('seperatorused'); ?>";
            var thefilepath = "<?php echo $fullfilepath ?>";
       </script>
  </head>
<body>
    
<div class='header ui-widget-header'><strong><?php echo sprintf($clang->gT("Select attributes to copy with your %s participant(s)"),$linecount);?></strong></div>
<div class="main">
    <div id="csvattribute">
    <div class="heading"><?php echo $clang->gT("CSV Headings "); ?></div>
        <ul class="csvatt">
        <?php
            foreach($firstline as $key=>$value)
            {
                echo "<li id='cs_".$value."' name='cs_".$value."' >".$value."</li>";
             }?>
        </ul>
     </div>
<div id="newcreated"><div class="heading"><?php echo $clang->gT("Attribute's to be created") ?></div> 
     <ul class="newcreate" id="sortable" style ="height: 40px">
    </ul>
</div>
    <div id="centralattribute"><div class="heading"><?php echo $clang->gT("Central Attribute");?></div> 
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


    <p> <input type="button" name="attmap" id="attmap" value="Continue" /></p>
    <?php $ajaxloader = array(
          'src' => 'images/ajax-loader.gif',
          'alt' => 'Ajax Loader',
          'title' => 'Ajax Loader'
          );?>
    <div id="processing" title="<?php echo $clang->gT("Processing .....") ?>" style="display:none">
    <?php echo img($ajaxloader); ?>
    </div>
 </div>
</body>
</html>







