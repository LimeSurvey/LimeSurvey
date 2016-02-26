<script type="text/javascript">
    var redUrl = "<?php echo $this->createUrl("/admin/participants/sa/displayParticipants"); ?>";
    var surveyId = "<?php echo $survey_id ?>";
    var redirect = "<?php echo $redirect ?>";
    if(redirect=='TRUE')
    {
        redUrl = "<?php echo $this->createUrl("/admin/tokens/sa/browse/surveyid/{$survey_id}"); ?>";
    }
    var copyUrl = "<?php echo $this->createUrl("/admin/participants/sa/addToTokenattmap"); ?>";

    // Comma separated string of participant ids
    var participant_id = "<?php echo $participant_id; ?>";

    /* SCRIPT TEXT */
    var attributesMappedText = "<?php eT("All the attributes are automatically mapped") ?>";
    var mustPairAttributeText= "<?php eT("You have to pair it with one attribute of the token table") ?>";
    var onlyOneAttributeMappedText="<?php eT("Only one central attribute is mapped with token attribute") ?>";
    var cannotAcceptTokenAttributesText="<?php eT("This list cannot accept token attributes.") ?>";

    </script>
</head>
<body>
    <div class='header ui-widget-header'>
        <h3 class='pagetitle'>
            <?php eT("Map your participant attributes to an existing token attribute or create a new one"); ?>
        </h3>
    </div>
    <?php
        $columncount = 0;
        if (!empty($selectedcentralattribute))
        {
            $columncount = $columncount + 2;
        }
        if (!empty($selectedtokenattribute))
        {
            $columncount++;
        }
        $columnstyle = "attrcol_".$columncount;
    ?>

    <div class='row'>
        <div class='col-sm-4'>
            <div class='panel panel-primary' id="centralattribute" class="<?php echo $columnstyle ?>">
                <div class="panel-heading"><?php eT("Unmapped participant attributes"); ?></div>
                <div class='panel-body'>
                    <div id="cpdbatt">
                        <?php
                        foreach ($selectedcentralattribute as $key => $value)
                        {
                            ?>
                            <div class='panel panel-default' id='c_<?php echo $key; ?>'><div class='panel-body'><?php echo $value; ?></div></div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class='col-sm-4'>
            <div class='panel panel-primary' id="newcreated" class="<?php echo $columnstyle ?>">
                <div class="panel-heading"><?php eT("Token attributes to create"); ?></div>
                <div class='panel-body'>
                    <div class="newcreate" id="sortable" style ="height:40px">
                    </div>
                </div>
            </div>
        </div>
        <div class='col-sm-4'>
            <div id='tokenattribute'>
                <div class='panel panel-primary' id="">
                    <div class="panel-heading">
                        <?php eT("Existing token attributes"); ?> 
                    </div>
                    <div class='panel-body'>
                        <div class="tokenatt ui-sortable">
                            <?php
                                foreach ($selectedtokenattribute as $key => $value)
                                {
                                    echo "<div class='panel panel-default' id='t_" . $key . "'><div class='panel-body'>" . $value . "</div></div>";
                                }
                            ?>
                        </div>

                        <?php if(!empty($selectedtokenattribute)): ?>
                            <div class='explanation'>
                                <input type='checkbox' id='overwriteman' name='overwriteman' /> <label for='overwriteman'><?php eT("Overwrite existing token attribute values if a participant already exists?") ?></label>
                                <input type='checkbox' id='createautomap' name='createautomap' /> <label for='createautomap'><?php eT("Make these mappings automatic in future") ?></label>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($alreadymappedattributename)): ?>
                            <div class='panel-heading'>
                                <?php eT("Pre-mapped attributes") ?>
                            </div>
                            <div class='panel-body'>
                                <div class="notsortable">
                                    <?php
                                    foreach ($alreadymappedattributename as $key => $value)
                                    {
                                        ?>
                                        <div class='panel panel-default' title='This attribute is already mapped' id=''><div class='panel-body'><?php echo $value; ?></div></div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class='explanation'>
                                    <input type='checkbox' id='overwrite' name='overwrite' /> <label for='overwrite'><?php eT("Overwrite existing auto mapped attribute values if a participant already exists?") ?></label>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <div class='panel panel-primary'>
                    <div class='panel-heading'><?php eT("Standard token fields") ?></div>
                    <div class='panel-body'>
                        <div class="standardfields">
                            <div class='panel panel-default' id='t_token'><div class='panel-body'><?php eT("Token") ?></div></div>
                        </div>
                        <div class='explanation'>
                            <input type='checkbox' id='overwritest' name='overwritest' /> <label for='overwritest'><?php eT("Overwrite existing standard field values if a participant already exists?") ?></label>
                            <?php eT("Note: Standard token fields cannot be automatically mapped") ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class='form-group col-sm-12 text-center'>
        <input class='btn btn-default' type="button" name="goback" onclick="history.back();" id="back" value="<?php eT('Back')?>" />
        <input class='btn btn-default' type='button' name='reset' onClick='window.location.reload();' id='reset' value="<?php eT('Reset') ?>" />
        <input class='btn btn-default' type="button" name="attmap" id="attmap" value="<?php eT('Continue')?>" />
   </div>

    <?php
    $ajaxloader = array(
        'src' => Yii::app()->getConfig('adminimageurl') . '/ajax-loader.gif',
        'alt' => 'Ajax loader',
        'title' => 'Ajax loader'
    );
    ?>
    <div id="processing" title="<?php eT("Processing...") ?>" style="display:none">
    <?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
    </div>

    <div id='attribute-map-participant-modal' class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php eT("Map participant attributes"); ?></h4>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

</body>
</html>
