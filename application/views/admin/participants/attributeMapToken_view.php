<script type="text/javascript">
    var redUrl = "<?php echo $this->createUrl("/admin/participants/sa/displayParticipants"); ?>";
    var copyUrl = "<?php echo $this->createUrl("/admin/participants/sa/addToCentral"); ?>";
    
    var surveyId = "<?php echo Yii::app()->request->getQuery('sid'); ?>";

    /* LANGUAGE */
    var attributesMappedText = "<?php eT("There are no unmapped attributes") ?>";
    var cannotAcceptTokenAttributesText="<?php eT("This list cannot accept token attributes.") ?>";

</script>
    <div class='header ui-widget-header'>
        <strong>
            <?php eT("Map your token attributes to an existing participant attribute or create a new one"); ?>
        </strong>
    </div>
    <div class="draggable-container">
        <div id="tokenattribute" class="attribute-column">
            <div class="heading"><?php eT("Unmapped token attributes") ?></div>
            <div id="tokenatt" class="tokenatt droppable">
            <?php
            if (!empty($tokenattribute))
            {
                foreach ($tokenattribute as $key => $value)
                {
                    echo "<div title='".gT("Drag this attribute to another column to map it to the central participants database")."' id='t_" . $value . "' data-name=\"$key\" class=\"token-attribute attribute-item draggable\">" . $key . "</div>"; 
                }
            }
            ?>
            </div>

        </div>
        <div id="newcreated" class="attribute-column">
            <div class="heading"><?php eT("Participant attributes to create") ?></div>
            <div class="newcreate droppable" style ="height: 40px">
            </div>
        </div>
        <div id="centralattribute" class="attribute-column">
            <div class="heading"><?php eT("Existing participant attributes")?></div>
            <div class="centralatt">
                <?php
                if (!empty($attribute))
                {
                    foreach ($attribute as $key => $value)
                    {
                        echo "<div class=\"mappable-attribute-wrapper droppable\"><div id='c_" . $key . "' data-name='c_" . $key . "' class=\"mappable-attribute attribute-item\" >" . $value . "</div></div>";
                    }
                }
                ?>
            </div>
            
            <?php if (!empty($attribute)) { ?>
            <div class='explanation'>
                <div class="explanation-row">
                    <input type='checkbox' id='overwriteman' name='overwriteman' />
                    <label for='overwriteman'><?php eT("Overwrite existing attribute values if a participant already exists?") ?></label>
                </div>
                <div class="explanation-row">
                    <input type='checkbox' id='createautomap' name='createautomap' />
                    <label for='createautomap'><?php eT("Make these mappings automatic in future") ?></label>
                </div>
            </div>
            <?php } else { ?>
            
            <?php }
            if(!empty($alreadymappedattributename)) {
                ?>
                <div class='heading'><?php eT("Pre-mapped attributes") ?></div><br />
                <div class="notsortable">
                <?php
                foreach ($alreadymappedattributename as $key => $value)
                {
                    echo "<div title='".gT("This attribute is automatically mapped")."' data-name='$value' class=\"already-mapped-attribute attribute-item\" >" . $alreadymappedattdescription[$value] . "</div>";
                }
                ?>
                </div>
                <div class='explanation'>
                    <div class="explanation-row">
                        <input type='checkbox' id='overwrite' name='overwrite' />
                        <label for='overwrite'><?php eT("Overwrite existing attribute values if a participant already exists?") ?></label>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div style="clear: both;"></div>
    </div>
    <p>
        <input type="button" name="goback" id="back" value="<?php eT('Back')?>" />
        <input type='button' name='reset' onClick='window.location.reload();' id='reset' value="<?php eT('Reset') ?>" />
        <input type="button" name="attmap" id="attmap" value="<?php eT('Continue') ?>" />
    </p>
    
    <?php
    $ajaxloader = array(
        'src' => Yii::app()->getConfig('adminimageurl') . '/ajax-loader.gif',
        'alt' => 'Ajax Loader',
        'title' => 'Ajax Loader'
    );
    ?>
    <div id="processing" title="<?php eT("Processing...") ?>" style="display:none">
<?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
    </div>
