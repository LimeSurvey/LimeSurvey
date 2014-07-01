<script type="text/javascript">
    var redUrl = "<?php echo $this->createUrl("/admin/participants/sa/displayParticipants"); ?>";
    var copyUrl = "<?php echo $this->createUrl("/admin/participants/sa/addToCentral"); ?>";
    
    var surveyId = "<?php echo Yii::app()->request->getQuery('sid'); ?>";

    /* LANGUAGE */
    var attributesMappedText = "<?php $clang->eT("There are no unmapped attributes") ?>";
    var cannotAcceptTokenAttributesText="<?php $clang->eT("This list cannot accept token attributes.") ?>";

</script>
    <div class='header ui-widget-header'>
        <strong>
            <?php $clang->eT("Map your token attributes to an existing participant attribute or create a new one"); ?>
        </strong>
    </div>
    <div class="draggable-container">
        <div id="tokenattribute" class="attribute-column">
            <div class="heading"><?php $clang->eT("Unmapped token attributes") ?></div>
            <div id="tokenatt" class="tokenatt droppable">
            <?php
            if (!empty($tokenattribute))
            {
                foreach ($tokenattribute as $key => $value)
                {
                    echo "<div title='".$clang->gT("Drag this attribute to another column to map it to the central participants database")."' id='t_" . $value . "' data-name=\"$key\" class=\"token-attribute attribute-item draggable\">" . $key . "</div>"; 
                }
            }
            ?>
            </div>

        </div>
        <div id="newcreated" class="attribute-column">
            <div class="heading"><?php $clang->eT("Participant attributes to create") ?></div>
            <div class="newcreate droppable" style ="height: 40px">
            </div>
        </div>
        <div id="centralattribute" class="attribute-column">
            <div class="heading"><?php $clang->eT("Existing participant attributes")?></div>
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
                    <label for='overwriteman'><?php $clang->eT("Overwrite existing attribute values if a participant already exists?") ?></label>
                </div>
                <div class="explanation-row">
                    <input type='checkbox' id='createautomap' name='createautomap' />
                    <label for='createautomap'><?php $clang->eT("Make these mappings automatic in future") ?></label>
                </div>
            </div>
            <?php } else { ?>
            
            <?php }
            if(!empty($alreadymappedattributename)) {
                ?>
                <div class='heading'><?php $clang->eT("Pre-mapped attributes") ?></div><br />
                <div class="notsortable">
                <?php
                foreach ($alreadymappedattributename as $key => $value)
                {
                    echo "<div title='".$clang->gT("This attribute is automatically mapped")."' data-name='$value' class=\"already-mapped-attribute attribute-item\" >" . $alreadymappedattdescription[$value] . "</div>";
                }
                ?>
                </div>
                <div class='explanation'>
                    <div class="explanation-row">
                        <input type='checkbox' id='overwrite' name='overwrite' />
                        <label for='overwrite'><?php $clang->eT("Overwrite existing attribute values if a participant already exists?") ?></label>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div style="clear: both;"></div>
    </div>
    <p>
        <input type="button" name="goback" id="back" value="<?php $clang->eT('Back')?>" />
        <input type='button' name='reset' onClick='window.location.reload();' id='reset' value="<?php $clang->eT('Reset') ?>" />
        <input type="button" name="attmap" id="attmap" value="<?php $clang->eT('Continue') ?>" />
    </p>
    
    <?php
    $ajaxloader = array(
        'src' => Yii::app()->getConfig('adminimageurl') . '/ajax-loader.gif',
        'alt' => 'Ajax Loader',
        'title' => 'Ajax Loader'
    );
    ?>
    <div id="processing" title="<?php $clang->eT("Processing...") ?>" style="display:none">
<?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
    </div>
