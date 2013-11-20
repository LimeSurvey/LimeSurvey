<script type="text/javascript">
    var redUrl = "<?php echo $this->createUrl("/admin/participants/sa/displayParticipants"); ?>";
    var surveyId = "<?php echo $survey_id ?>";
    var redirect = "<?php echo $redirect ?>";
    if(redirect=='TRUE')
    {
        redUrl = "<?php echo $this->createUrl("/admin/tokens/sa/browse/surveyid/{$survey_id}"); ?>";
    }
    var copyUrl = "<?php echo $this->createUrl("/admin/participants/sa/addToTokenattmap"); ?>";

    var participant_id = "<?php echo $participant_id; ?>";

    /* SCRIPT TEXT */
    var attributesMappedText = "<?php $clang->eT("All the attributes are automatically mapped") ?>";
    var mustPairAttributeText= "<?php $clang->eT("You have to pair it with one attribute of the token table") ?>";
    var onlyOneAttributeMappedText="<?php $clang->eT("Only one central attribute is mapped with token attribute") ?>";
    var cannotAcceptTokenAttributesText="<?php $clang->eT("This list cannot accept token attributes.") ?>";

    </script>
</head>
<body>
    <div class='header ui-widget-header'>
        <strong>
            <?php $clang->eT("Map your participant attributes to an existing token attribute or create a new one"); ?>
        </strong>
    </div>
<?php
	$columncount = 0;
	if (!empty($selectedcentralattribute))
		$columncount = $columncount + 2;
	if (!empty($selectedtokenattribute))
		$columncount++;
	$columnstyle = "attrcol_".$columncount;
?>

    <div id="centralattribute" class="<?php echo $columnstyle ?>">
        <div class="heading"><?php $clang->eT("Unmapped participant attributes"); ?></div>
        <ul id="cpdbatt">
            <?php
            foreach ($selectedcentralattribute as $key => $value)
            {
                ?>
                <li id='c_<?php echo $key; ?>'><?php echo $value; ?></li>
                <?php
            }
            ?>
        </ul>
    </div>
    <div id="newcreated" class="<?php echo $columnstyle ?>">
        <div class="heading"><?php $clang->eT("Token attributes to create"); ?></div>
        <ul class="newcreate" id="sortable" style ="height:40px">
        </ul>
    </div>
    <div id="tokenattribute" class="<?php echo $columnstyle ?>">
        <div class="heading">
            <?php $clang->eT("Existing token attributes"); ?>
        </div>
        <ul class="tokenatt">
            <?php
            foreach ($selectedtokenattribute as $key => $value)
            {
                echo "<li id='t_" . $key . "'>" . $value . "</li>";
            }
            ?>
        </ul>
        <?php if(!empty($selectedtokenattribute)) { ?>
        <br />
        <div class='explanation'>
            <input type='checkbox' id='overwriteman' name='overwriteman' /> <label for='overwriteman'><?php $clang->eT("Overwrite existing token attribute values if a participant already exists?") ?></label>
            <br /><input type='checkbox' id='createautomap' name='createautomap' /> <label for='createautomap'><?php $clang->eT("Make these mappings automatic in future") ?></label><br />&nbsp;
        </div>
        <?php
        } else {echo "<br />&nbsp;";}
        if(!empty($alreadymappedattributename)) {
        ?>
        <br />
        <div class='heading'><?php $clang->eT("Pre-mapped attributes") ?></div><br />
        <ul class="notsortable">
            <?php
            foreach ($alreadymappedattributename as $key => $value)
            {
                ?>
                <li title='This attribute is already mapped' id=''><?php echo $value; ?></li>
                <?php
            }
            ?>
        </ul>
        <div class='explanation'>
            <input type='checkbox' id='overwrite' name='overwrite' /> <label for='overwrite'><?php $clang->eT("Overwrite existing auto mapped attribute values if a participant already exists?") ?></label>
        </div>
        <?php
        }
        ?>
        <div class='heading'><?php $clang->eT("Standard token fields") ?></div><br />
        <ul class="standardfields">
            <li id='t_token'><?php $clang->eT("Token") ?></li>
        </ul>
        <div class='explanation'>
            <input type='checkbox' id='overwritest' name='overwritest' /> <label for='overwritest'><?php $clang->eT("Overwrite existing standard field values if a participant already exists?") ?></label>
            <br /><?php $clang->eT("Note: Standard token fields cannot be automatically mapped") ?>
        </div>

    </div>
	<p>
		<input type="button" name="goback" onclick="history.back();" id="back" value="<?php $clang->eT('Back')?>" />
        <input type='button' name='reset' onClick='window.location.reload();' id='reset' value="<?php $clang->eT('Reset') ?>" />
        <input type="button" name="attmap" id="attmap" value="<?php $clang->eT('Continue')?>" />
   	</p>
    <?php
    $ajaxloader = array(
        'src' => Yii::app()->getConfig('adminimageurl') . '/ajax-loader.gif',
        'alt' => 'Ajax loader',
        'title' => 'Ajax loader'
    );
    ?>
    <div id="processing" title="<?php $clang->eT("Processing...") ?>" style="display:none">
    <?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
    </div>

</body>
</html>
