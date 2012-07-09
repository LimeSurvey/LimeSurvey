
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getConfig('adminstyleurl') . "attributeMapToken.css" ?>" />
<script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery-ui.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery.ui.sortable.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('adminscripts') . "attributeMapToken.js" ?>" type="text/javascript"></script>
<script type="text/javascript">
    var redUrl = "<?php echo Yii::app()->baseUrl . "/index.php/admin/participants/displayParticipants"; ?>";
    var copyUrl = "<?php echo Yii::app()->baseUrl . "/index.php/admin/participants/addToCentral"; ?>";
    var surveyId = "<?php echo Yii::app()->request->getQuery('sid'); ?>";

    /* LANGUAGE */
    var attributesMappedText = "<?php $clang->et("There are no unmapped attributes") ?>";
    var mustPairAttributeText= "<?php $clang->et("You have to pair it with one attribute of the token table") ?>";
    var onlyOneAttributeMappedText="<?php $clang->et("Only one central attribute is mapped with token attribute") ?>";
    var cannotAcceptTokenAttributesText="<?php $clang->et("This list cannot accept token attributes.") ?>";
    var addElementBelowText="<?php $clang->et("You have to add the element below the list") ?>";

</script>
</head>
<body>
    <div class='header ui-widget-header'>
        <strong>
            <?php $clang->eT("Map your selected token attributes to an existing participant attribute or create a new one"); ?>
        </strong>
    </div>

    <div id="tokenattribute">
        <div class="heading"><?php $clang->eT("Unmapped token attributes") ?></div>
            <ul id="tokenatt">
            <?php
            if (!empty($tokenattribute))
            {
                foreach ($tokenattribute as $key => $value)
                {
                    echo "<li title='".$clang->gT("Drag this attribute to another column to map it to the central participants database")."' id='t_" . $value . "' name=\"$key\">" . $key . "</li>"; //Passing attribute description as name of the attribute
                }
            }
            ?>
            </ul>

        </div>
        <div id="newcreated"><div class="heading"><?php $clang->eT("Participant attributes to create") ?></div>
            <ul class="newcreate" id="sortable" style ="height: 40px">
            </ul>
        </div>
        <div id="centralattribute"><div class="heading"><?php $clang->eT("Existing participant attributes")?></div>
            <ul class="centralatt">
                <?php
                if (!empty($attribute))
                {
                    foreach ($attribute as $key => $value)
                    {
                        echo "<li id='c_" . $key . "' name='c_" . $key . "' >" . $value . "</li>";
                    }
                }
                ?>
            </ul>
            <?php
            if(empty($attribute)) {
                echo "<br />&nbsp;\n";
            }
            if(!empty($alreadymappedattributename)) {
                ?>
                <div class='heading'><?php $clang->eT("Pre-mapped attributes") ?></div><br />
                <ul class="notsortable">
                <?php
                foreach ($alreadymappedattributename as $key => $value)
                {
                    echo "<li title='".$clang->gT("This attribute is automatically mapped")."' id='' name='$value' >" . $alreadymappedattdescription[$value] . "</li>";
                }
                ?>
                </ul>
                <br /><input type='checkbox' id='overwrite' name='overwrite' /> <label for='overwrite'><?php $clang->eT("Overwrite existing attribute values?") ?></label>
                <?php
            }
            ?>
        </div>
    <p> <input type="button" name="attmap" id="attmap" value="Continue" /></p>
    <?php
    $ajaxloader = array(
        'src' => Yii::app()->baseUrl . '/images/ajax-loader.gif',
        'alt' => 'Ajax Loader',
        'title' => 'Ajax Loader'
    );
    ?>
    <div id="processing" title="<?php $clang->eT("Processing...") ?>" style="display:none">
<?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
    </div>
</body>
</html>
