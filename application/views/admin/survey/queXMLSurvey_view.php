<div class='side-body'>
    <?php echo CHtml::form(["admin/export/sa/quexml/surveyid/{$surveyid}/"], 'post'); ?>
    <h3 class="col-12">
        <?php eT("queXML PDF export"); ?>
        <button role="button" type='submit' class="btn btn-primary float-end">
            <?php eT("queXML PDF export"); ?>
        </button>
    </h3>
    <div class="mb-3 row"><label class="form-label" for='save_language'><?php eT("Language selection"); ?></label>
        <div class="">
            <select class="form-select" name='save_language'>
                <?php foreach ($slangs as $lang) {
                    if ($lang == $baselang) { ?>
                        <option value='<?php echo $lang; ?>'
                                selected='selected'><?php echo getLanguageNameFromCode($lang, false); ?></option>
                    <?php } else { ?>
                        <option value='<?php echo $lang; ?>'><?php echo getLanguageNameFromCode($lang,
                                false); ?></option>
                    <?php }
                } ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="mb-3 col-12"><label class="form-label" for='queXMLStyle'><?php eT("Style:"); ?></label>
            <div class="">
                <textarea class="form-control" rows="10" id='queXMLStyle'
                          name='queXMLStyle'><?php echo $queXMLStyle; ?> </textarea>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- First column -->
        <div class="col-md-6">
            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLSingleResponseAreaHeight'><?php eT("Minimum height of single choice answer boxes"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLSingleResponseAreaHeight'
                       name='queXMLSingleResponseAreaHeight' value="<?php echo $queXMLSingleResponseAreaHeight; ?>"/>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLSingleResponseHorizontalHeight'><?php eT("Minimum height of subquestion items"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLSingleResponseHorizontalHeight'
                       name='queXMLSingleResponseHorizontalHeight'
                       value="<?php echo $queXMLSingleResponseHorizontalHeight; ?>"/>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLQuestionnaireInfoMargin'><?php eT("Margin before questionnaireInfo element (mm)"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLQuestionnaireInfoMargin'
                       name='queXMLQuestionnaireInfoMargin' value="<?php echo $queXMLQuestionnaireInfoMargin; ?>"/>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLResponseTextFontSize'><?php eT("Answer option / subquestion font size"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLResponseTextFontSize'
                       name='queXMLResponseTextFontSize' value="<?php echo $queXMLResponseTextFontSize; ?>"/>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLResponseLabelFontSize'><?php eT("Answer label font size (normal)"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLResponseLabelFontSize'
                       name='queXMLResponseLabelFontSize' value="<?php echo $queXMLResponseLabelFontSize; ?>"/>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLResponseLabelFontSizeSmall'><?php eT("Answer label font size (small)"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLResponseLabelFontSizeSmall'
                       name='queXMLResponseLabelFontSizeSmall'
                       value="<?php echo $queXMLResponseLabelFontSizeSmall; ?>"/>
            </div>

            <div class="mb-3 row">
                <label class="form-label" for='queXMLSectionHeight'><?php eT("Minimum section height (mm)"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLSectionHeight'
                       name='queXMLSectionHeight' value="<?php echo $queXMLSectionHeight; ?>"/>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLBackgroundColourSection'><?php eT("Background colour for sections (0 black - 255 white)"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLBackgroundColourSection'
                       name='queXMLBackgroundColourSection' value="<?php echo $queXMLBackgroundColourSection; ?>"/>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLBackgroundColourQuestion'><?php eT("Background colour for questions (0 black - 255 white)"); ?></label>
                <input class="col-12 form-control" type='text' size='10' id='queXMLBackgroundColourQuestion'
                       name='queXMLBackgroundColourQuestion' value="<?php echo $queXMLBackgroundColourQuestion; ?>"/>
            </div>
        </div>
        <!-- Second column -->
        <div class="col-md-6">
            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLAllowSplittingSingleChoiceHorizontal'><?php eT("Allow array style questions to be split over multiple pages"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'queXMLAllowSplittingSingleChoiceHorizontal',
                        'checkedOption' => $queXMLAllowSplittingSingleChoiceHorizontal == 1,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ],
                    ]); ?>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLAllowSplittingSingleChoiceVertical'><?php eT("Allow single choice questions to be split over multiple pages"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'queXMLAllowSplittingSingleChoiceVertical',
                        'checkedOption' => $queXMLAllowSplittingSingleChoiceVertical == 1,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ],
                    ]); ?>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLAllowSplittingMatrixText'><?php eT("Allow Multiple short text / numeric questions to be split over multiple pages"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'queXMLAllowSplittingMatrixText',
                        'checkedOption' => $queXMLAllowSplittingMatrixText == 1,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ],
                    ]); ?>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="form-label"
                       for='queXMLAllowSplittingVas'><?php eT("Allow slider questions to be split over multiple pages"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'queXMLAllowSplittingVas',
                        'checkedOption' => $queXMLAllowSplittingVas == 1,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="form-label" for='queXMLPageOrientation'><?php eT("Page orientation:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'queXMLPageOrientation',
                        'checkedOption' => $queXMLPageOrientation,
                        'selectOptions' => [
                            "P" => gT("Portrait", 'unescaped'),
                            "L" => gT("Landscape", 'unescaped')
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="form-label" for='queXMLPageFormat'><?php eT("Page format:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'queXMLPageFormat',
                        'checkedOption' => $queXMLPageFormat,
                        'selectOptions' => [
                            "A4"       => gT("A4", 'unescaped'),
                            "A3"       => gT("A3", 'unescaped'),
                            "USLETTER" => gT("US Letter", 'unescaped')
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="form-label" for='queXMLEdgeDetectionFormat'><?php eT("Edge detection format:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'queXMLEdgeDetectionFormat',
                        'checkedOption' => $queXMLEdgeDetectionFormat,
                        'selectOptions' => [
                            "lines" => gT("Corner lines", 'unescaped'),
                            "boxes" => gT("Corner boxes", 'unescaped')
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 ">
            <input type='hidden' name='ok' value='Y'/>
        </div>
    </div>
    <?= CHtml::endForm() ?>
    <div class="row">
        <div class="col-12">
            <div class="row">
                <label class="form-label"><?php eT("Reset to default settings:"); ?></label>
                <div>
                         <?php echo CHtml::form(array("admin/export/sa/quexmlclear/surveyid/{$surveyid}/"), 'post', array('id'=>'quexmlclearform'));
                         echo CHtml::htmlButton(gT('Reset now'), [
                             'type' => 'submit',
                             'class' => 'btn btn-danger',
                         ]);
                         ?>
                         <?= CHtml::endForm() ?>
                </div>
            </div>
         </div>
    </div>
</div>