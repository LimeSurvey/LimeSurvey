<script type="text/javascript">
    var redUrl = "<?php echo Yii::app()->baseUrl . "/index.php/admin/participants/displayParticipants"; ?>";
    var surveyId = "<?php echo $survey_id ?>";
    var redirect = "<?php echo $redirect ?>";
    if(redirect=='TRUE')
    {
        redUrl = "<?php echo Yii::app()->baseUrl . "/index.php/admin/tokens/browse/surveyid" . '/' . $survey_id; ?>";
    }
    var copyUrl = "<?php echo Yii::app()->baseUrl . "/index.php/admin/participants/addToTokenattmap"; ?>";
    var participant_id = "<?php echo $participant_id; ?>";

    /* SCRIPT TEXT */
    var attributesMappedText = "<?php $clang->et("All the attributes are automatically mapped") ?>";
    var mustPairAttributeText= "<?php $clang->et("You have to pair it with one attribute of the token table") ?>";
    var onlyOneAttributeMappedText="<?php $clang->et("Only one central attribute is mapped with token attribute ") ?>";
    var cannotAcceptTokenAttributesText="<?php $clang->et("This list cannot accept token attributes") ?>";

    </script>
<?php
	$columncount = 0;
	if (!empty($selectedcentralattribute))
		$columncount = $columncount + 2;
	if (!empty($selectedtokenattribute))
		$columncount++;
	$columnstyle = "attrcol_".$columncount;
?>
<div class='header ui-widget-header'>
    <strong>
        <?php echo $count ?>
    </strong>
</div>
<div class="main">
	<p><?php $clang->eT("Select any attributes you'd like use in your survey by dropping the attribute in the right hand column."); ?><br />
		<?php $clang->eT("Click on 'Continue' when you are done."); ?>
	</p>
    <?php
    if (!empty($selectedcentralattribute))
    {
        ?>
        <div id="centralattribute" class="<?php echo $columnstyle ?>">
            <div class="heading"><?php $clang->eT("Already mapped"); ?></div>
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
        </div>
        <?php
        if (!empty($selectedcentralattribute))
        {
            ?>
            <div id="newcreated" class="<?php echo $columnstyle ?>">
                <div class="heading"><?php $clang->eT("Attributes to be created"); ?></div>
                <ul class="newcreate" id="sortable" style ="height:40px">
                </ul>
            </div>
            <?php
        }
    }
    if (!empty($selectedtokenattribute))
    {
        ?>
        <div id="tokenattribute" class="<?php echo $columnstyle ?>">
            <div class="heading">
                <?php $clang->eT("Token table attribute"); ?>
            </div>
            <ul class="tokenatt">
                <?php
                foreach ($selectedtokenattribute as $key => $value)
                {
                    echo "<li id='t_" . $value . "'>" . $value . "</li>";
                }
                ?>
            </ul>
        </div>
    <?php }
    ?>
	<div id="controllingarea">
		<input type="button" name="goback" onclick="history.back();" id="back" value="<?php $clang->eT('Back')?>" />
		<input type="button" name="attmap" id="attmap" value="<?php $clang->eT('Continue')?>" />
   	</div>
    <?php
    $ajaxloader = array(
        'src' => Yii::app()->baseUrl . '/images/ajax-loader.gif',
        'alt' => 'Ajax loader',
        'title' => 'Ajax loader'
    );
    ?>
    <div id="processing" title="<?php $clang->eT("Processing...") ?>" style="display:none">
<?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
    </div>
</div>
