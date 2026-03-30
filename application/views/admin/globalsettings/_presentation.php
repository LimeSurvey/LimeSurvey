<?php
/**
 * This view generate the presentation tab inside global settings.
 *
 *
 */

?>
<div class="container">
    <div class="row">
        <div class="col-6">
            <div class="mb-3">
                <label class=" form-label" for='repeatheadings'><?php eT("Repeat headings in array questions every X subquestions:"); ?></label>
                <div class="">
                    <input class="form-control" id='repeatheadings' name='repeatheadings' value='<?php echo Yii::app()->getConfig('repeatheadings'); ?>'
                           size='4' maxlength='4'/>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='pdffontsize'><?php eT("Font size of PDFs:"); ?></label>
                <div class="">
                    <input class="form-control" type='text' id='pdffontsize' name='pdffontsize'
                           value="<?php echo htmlspecialchars((string) Yii::app()->getConfig('pdffontsize')); ?>"/>
                </div>
            </div>


            <div class="mb-3">
                <label class=" form-label" for='pdflogowidth'><?php eT("Width of PDF header logo:"); ?></label>
                <div class="">
                    <input class="form-control" type='text' size='5' id='pdflogowidth' name='pdflogowidth'
                           value="<?php echo htmlspecialchars((string) Yii::app()->getConfig('pdflogowidth')); ?>"/>

                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='pdfheadertitle'><?php eT("PDF header title (if empty, site name will be used):"); ?></label>
                <div class="">
                    <input class="form-control" type='text' id='pdfheadertitle' size='50' maxlength='256' name='pdfheadertitle'
                           value="<?php echo htmlspecialchars((string) Yii::app()->getConfig('pdfheadertitle')); ?>"/>

                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='pdfheaderstring'><?php eT("PDF header string (if empty, survey name will be used):"); ?></label>
                <div class="">
                    <input class="form-control" type='text' id='pdfheaderstring' size='50' maxlength='256' name='pdfheaderstring'
                           value="<?php echo htmlspecialchars((string) Yii::app()->getConfig('pdfheaderstring')); ?>"/>

                </div>
            </div>
        </div>
        <div class="col-6">

            <div class="mb-3">
                <label class=" form-label" for='pdfshowsurveytitle'><?php eT("Show survey title in export PDFs:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'pdfshowsurveytitle',
                        'ariaLabel'=> gT('Show survey title in export PDFs:'),
                        'checkedOption' => Yii::app()->getConfig('pdfshowsurveytitle') === 'Y' ? '1' : 0,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='pdfshowheader'><?php eT("Show header in answers export PDFs:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'pdfshowheader',
                        'ariaLabel'=> gT('Show header in answers export PDFs:'),
                        'checkedOption' => Yii::app()->getConfig('pdfshowheader') === 'Y' ? '1' : 0,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='bPdfQuestionFill'><?php eT("Add gray background to questions in PDF:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'bPdfQuestionFill',
                        'checkedOption' => Yii::app()->getConfig('bPdfQuestionFill'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='bPdfQuestionBold'><?php eT("PDF questions in bold:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'bPdfQuestionBold',
                        'ariaLabel'=> gT('PDF questions in bold:'),
                        'checkedOption' => Yii::app()->getConfig('bPdfQuestionBold'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='bPdfQuestionBorder'><?php eT("Borders around questions in PDF:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'bPdfQuestionBorder',
                        'ariaLabel'=> gT('Borders around questions in PDF:'),
                        'checkedOption' => Yii::app()->getConfig('bPdfQuestionBorder'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class=" form-label" for='bPdfResponseBorder'><?php eT("Borders around responses in PDF:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'bPdfResponseBorder',
                        'ariaLabel'=> gT('Borders around responses in PDF:'),
                        'checkedOption' => Yii::app()->getConfig('bPdfResponseBorder'),
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (Yii::app()->getConfig("demoMode") == true): ?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
