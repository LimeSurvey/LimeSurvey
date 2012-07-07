
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
    var attributesMappedText = "<?php $clang->et("All the attributes are automatically mapped") ?>";
    var mustPairAttributeText= "<?php $clang->et("You have to pair it with one attribute of the token table") ?>";
    var onlyOneAttributeMappedText="<?php $clang->et("Only one central attribute is mapped with token attribute ") ?>";
    var cannotAcceptTokenAttributesText="<?php $clang->et("This list cannot accept token attributes.") ?>";
    var addElementBelowText="<?php $clang->et("You have to add the element below the list") ?>";

</script>
</head>
<body>
    <?php if (!empty($tokenattribute))
    { ?>
        <div id="tokenattribute">
            <div class="heading">Token Attributes</div>
            <ul id="tokenatt">
                <?php
                foreach ($tokenattribute as $key => $value)
                {
                    echo "<li id='t_" . $value . "' name=\"$key\">" . $value . "</li>"; //Passing attribute description as name of the attribute
                }
                ?>
            </ul>
            <ul class="notsortable">
                <?php
                foreach ($alreadymappedattributename as $key => $value)
                {
                    echo "<li title='This attribute is already mapped' id='' name='' >" . $value . "</li>";
                }
                ?>
            </ul>
        </div>
    <?php } ?>
<?php if (!empty($tokenattribute))
{ ?>
        <div id="newcreated"><div class="heading">Attributes to be created</div>
            <ul class="newcreate" id="sortable" style ="height: 40px">
            </ul>
        </div>
<?php } ?>
            <?php if (!empty($tokenattribute))
            { ?>
        <div id="centralattribute"><div class="heading">Central Attribute</div>
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
        </div>
    <?php } ?>
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
