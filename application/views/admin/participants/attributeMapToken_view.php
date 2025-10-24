<script type="text/javascript">
    var redUrl = "<?php echo $this->createUrl("/admin/participants/sa/displayParticipants"); ?>";
    var copyUrl = "<?php echo $this->createUrl("/admin/participants/sa/addToCentral"); ?>";

    var surveyId = "<?php echo (int) Yii::app()->request->getQuery('sid'); ?>";

    /* LANGUAGE */
    var attributesMappedText = "<?php eT("There are no unmapped attributes") ?>";
    var cannotAcceptTokenAttributesText="<?php eT("This list cannot accept survey participant attributes.") ?>";

</script>

<div class='header'>
    <h3 class='pagetitle' aria-level="1"><?php eT("Map your survey participant attributes to an existing participant attribute or create a new one"); ?></h3>
</div>

<div class="draggable-container">
    <div class='row'>
        <div class='col-md-4'>
            <div id="tokenattribute" class="card attribute-column">
                <div class="card-header "><?php eT("Unmapped survey participant attributes") ?></div>
                <div id="tokenatt" class="tokenatt droppable">
                    <?php
                        if (!empty($tokenattribute))
                        {
                            foreach ($tokenattribute as $key => $value)
                            {
                                echo "<div id='t_" . $value . "' data-name='" . $key . "' class='card token-attribute attribute-item draggable'><div title='".gT("Drag this attribute to another column to map it to the central participants database")."' data-name=\"$key\" class=\"card-body\">" . $key . "</div></div>";
                            }
                        }
                    ?>
                </div>

            </div>
        </div>

        <div class='col-md-4'>
            <div id="newcreated" class="card attribute-column">
                <div class="card-header "><?php eT("Participant attributes to create") ?></div>
                <div class="card-body newcreate droppable" style ="height: 40px">
                </div>
            </div>
        </div>

        <div class='col-md-4'>
            <div id="centralattribute" class="card attribute-column">
                <div class="card-header "><?php eT("Existing participant attributes")?></div>
                <div class="centralatt">
                    <?php
                    if (!empty($attribute))
                    {
                        foreach ($attribute as $key => $value)
                        {
                            echo "<div class='card mappable-attribute-wrapper droppable'><div class=\"card-body mappable-attribute attribute-item\" id='c_" . $key . "' data-name='c_" . $key . "'>" . $value . "</div></div>";
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
                    <div class='heading text-center'><?php eT("Pre-mapped attributes") ?></div>
                    <div class="notsortable">
                    <?php
                    foreach ($alreadymappedattributename as $key => $value)
                    {
                        echo "<div title='".gT("This attribute is automatically mapped")."' data-name='$value' class=\"already-mapped-attribute\" >" . $alreadymappedattdescription[$value] . "</div>";
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
        </div>
    </div>
    <div class='mb-3 col-12 text-center'>
        <input class='btn btn-outline-secondary' type="button" name="goback" onclick="history.back();" id="back" value="<?php eT('Back')?>" />
        <input class='btn btn-outline-secondary' type='button' name='reset' onClick='window.location.reload();' id='reset' value="<?php eT('Reset') ?>" />
        <input class='btn btn-outline-secondary' type="button" name="attmap" id="attmap" value="<?php eT('Continue')?>" />
   </div>

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
</div>
<div id='attribute-map-token-modal' class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT("Map survey participant attributes"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php eT("Close");?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
