<?php

/* @var $survey Survey */
/* @var $languageToTranslate  string  e.g. 'de' 'it' ... */
/* @var $additionalLanguages array */
/* @var $viewData array contains all necessary data for tabs and it content*/

?>

<div class="side-body">
    <h3><span class="ri-global-line text-success" ></span>&nbsp;&nbsp;<?php eT("Translate survey"); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
            echo CHtml::form(
                ["quickTranslation/index",'surveyid' => $survey->sid],
                'get',
                ['id' => 'translatemenu', 'class' => 'form-inline']
            );
            ?>
            <!-- select box for languages 'class' => 'form-group' -->
            <div class="row row-cols-lg-auto g-1 align-items-center mb-3">
                <div class="col-12">
                <?php
                echo CHtml::tag('label', array('for' => 'translationlanguage', 'class' => 'text-nowrap col col-form-label col-form-label-sm'), gT("Translate to") . ":");
                ?>
                </div>
                <div class="col-12">
                    <?php
                echo CHtml::openTag(
                    'select',
                    array(
                        'id' => 'translationlanguage',
                        'name' => 'lang',
                        'class' => 'form-select',
                        'onchange' => "$(this).closest('form').submit();"
                    )
                );
                if (count($additionalLanguages) > 1) {
                    echo CHtml::tag(
                        'option',
                        array(
                            'selected' => empty($languageToTranslate),
                            'value' => ''
                        ),
                        gT("Please choose...")
                    );
                }
                foreach ($additionalLanguages as $lang) {
                    $supportedLanguages = getLanguageData(false, Yii::app()->session['adminlang']);
                    $tolangtext = $supportedLanguages[$lang]['description'];
                    echo CHtml::tag(
                        'option',
                        array(
                            'selected' => ($languageToTranslate == $lang),
                            'value' => $lang
                        ),
                        $tolangtext
                    );
                }
                echo CHtml::closeTag('select');
                ?>
                </div>
            </div>
            <?php
                echo CHtml::endForm();
            ?>
        </div>
    </div>

    <div class="row">

        <?php
            $this->renderpartial('translateformheader_view', ['viewData' => $viewData]);
        ?>

    </div>

</div> <!-- close div sidebody -->
