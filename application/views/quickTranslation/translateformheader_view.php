<?php

/* @var $viewData array */

extract($viewData);

?>
<div id="translationloading" style="width: 100%; font-weight: bold; color: #000; text-align: center;">
    <br/>
    <?php eT("Loading translations"); ?><br/><br/>
</div>

<?php echo CHtml::form(array("quickTranslation/index/surveyid/{$surveyid}/lang/{$tolang}"), 'post', array('name' => 'translateform', 'id' => 'translateform')); ?>
<input type='hidden' name='sid' value='<?php echo $surveyid; ?>'/>
<input type='hidden' name='action' value='translate'/>
<input type='hidden' name='actionvalue' value='translateSave'/>
<input type='hidden' name='tolang' value='<?php echo $tolang; ?>'/>
<input type='hidden' name='baselang' value='<?php echo $baselang; ?>'/>

<script type="text/javascript">
    sGoogleApiError = "<?php eT("There was an error using the Google API.");?>";
    sDetailedError = "<?php eT("Detailed Error");?>";
    translateJsonUrl = "<?php echo $this->createUrl("quickTranslation/ajaxtranslategoogleapi", ['surveyid' => $surveyid]); ?>";
</script>

<div id="translationtabs">
    <ul class="nav nav-tabs">
        <?php for ($i = 0, $len = count($tab_names); $i < $len; $i++) { ?>
            <li class="nav-item" >
                <a class="nav-link <?php echo ($i == 0) ? 'active' : '' ?>" data-bs-toggle="tab" href="#tab-<?php echo $tab_names[$i]; ?>">
                <span>
                    <?php echo $amTypeOptions[$i]["description"]; ?>
                </span>
                </a>
            </li>
        <?php } ?>
        <?php $i = 0; ?>
    </ul>
    <div class="tab-content">

        <!-- here translatetabs_view and inside of them translatefields_view should be rendered.
        The data for those is prepared in function displayUntranslatedFields in QuicktranslationController -->
        <?php

        foreach ($singleTabs as $tabData) {
            //find the correct singleTabdata
            $this->renderpartial('translatetabs_view', [
                'baselangdesc' => $baselangdesc,
                'tolangdesc' => $tolangdesc,
                'tabData' => $tabData
            ]);
        }
        ?>
    </div>
    <p>
        <input type='submit' class='standardbtn d-none' value='<?php eT("Save");?>' <?php if ($bReadOnly){?>disabled='disabled'<?php }?>/>
    </p>
</div>
<?php
    echo CHtml::endForm();
?>
