<?php
/**
 * @var $tgis AdminController
 */
?>

<script type="text/javascript">
    var sImageURL = '';
    var duplicatelabelcode = '<?php eT('Error: You are trying to use duplicate label codes.', 'js'); ?>';
    var otherisreserved = '<?php eT("Error: 'other' is a reserved keyword.", 'js'); ?>';
    var quickaddtitle = '<?php eT('Quick-add subquestion or answer items', 'js'); ?>';
</script>

<div class="col-12 list-surveys">
    <?= // DO NOT REMOVE This is for automated testing to validate we see that page
    viewHelper::getViewTestTag('createLabelSets') ?>

    <div class="row">
        <div class="col-12 content-right">
            <!-- Tabs -->
            <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection" role="tablist">
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link active"
                        id="edit-labels-tab-main"
                        href="#neweditlblset0"
                        data-bs-toggle="tab"
                        role="tab"
                        aria-selected="true"
                        aria-controls="neweditlblset0"
                        tabindex="0"
                    >
                        <?php echo $tabitem; ?>
                    </a>
                </li>
                <?php if ($action === "newlabelset" && Permission::model()->hasGlobalPermission('labelsets', 'import')): ?>
                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link"
                            id="edit-labels-tab-import"
                            href="#neweditlblset1"
                            data-bs-toggle="tab"
                            role="tab"
                            aria-selected="false"
                            aria-controls="neweditlblset1"
                            tabindex="-1"
                        >
                            <?php eT("Import label set(s)"); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>


            <!-- Tabs content -->
            <div class="tab-content">
                <div
                    id="neweditlblset0"
                    class="tab-pane fade show active"
                    role="tabpanel"
                    aria-labelledby="edit-labels-tab-main"
                >
                    <!-- Form -->
                    <?php echo CHtml::form(["admin/labels/sa/process"], 'post', ['class' => 'form form30 ', 'id' => 'labelsetform', 'onsubmit' => "return isEmpty(document.getElementById('label_name'), '" . gT("Error: You have to enter a name for this label set.", "js") . "')"]); ?>
                    <!-- Set name -->
                    <div class="row">
                        <div class="mb-3 col-lg-6">
                            <label class="form-label" for='label_name'><?php eT("Set name:"); ?></label>
                            <div class="">
                                <?php echo CHtml::textField('label_name', $lbname ?? "", ['maxlength' => 100, 'size' => 50, 'class' => 'form-control']); ?>
                            </div>
                        </div>

                        <!-- Languages -->
                        <div class="mb-3 col-lg-6">
                            <label class=" form-label"><?php eT("Languages:"); ?></label>
                            <div class="">
                                <?php
                                $aAllLanguages = getLanguageDataRestricted(false, 'short');
                                if (isset($esrow)) {
                                    unset($aAllLanguages[$esrow['language']]);
                                }
                                Yii::app()->getController()->widget('yiiwheels.widgets.select2.WhSelect2',
                                    [
                                        'asDropDownList' => true,
                                        'htmlOptions'    => ['multiple' => 'multiple', 'style' => "width: 80%", 'required' => 'required'],
                                        'data'           => $aAllLanguages,
                                        'value'          => $langidsarray,
                                        'name'           => 'languageids',
                                        'pluginOptions'  => [
                                            'placeholder' => gT('Select languages', 'unescaped'),
                                        ]
                                    ]); ?>
                                <input type='hidden' name='oldlanguageids' id='oldlanguageids' value='<?php echo $langids; ?>'/>
                            </div>
                        </div>
                    </div>


                    <p>
                    <input type='submit' class="d-none" value='<?php if ($action === "newlabelset") {
                            eT("Save");
                        } else {
                            eT("Update");
                        } ?>'/>
                        <input type='hidden' name='action' value='<?php if ($action === "newlabelset") {
                            echo "insertlabelset";
                        } else {
                            echo "updateset";
                        } ?>'/>

                        <?php if ($action === "editlabelset") { ?>
                            <input type='hidden' name='lid' value='<?php echo $lblid; ?>'/>
                        <?php } ?>
                    </p>
                    <?php echo CHtml::endForm() ?>
                </div>
                <!-- Import -->
                <?php if ($action === "newlabelset" && Permission::model()->hasGlobalPermission('labelsets', 'import')): ?>
                    <div
                        id="neweditlblset1"
                        class="tab-pane fade"
                        role="tabpanel"
                        aria-labelledby="edit-labels-tab-import"
                    >
                        <?php echo CHtml::form(["admin/labels/sa/import"], 'post', ['enctype' => 'multipart/form-data', 'class' => 'form', 'id' => 'importlabels', 'name' => "importlabels"]); ?>
                        <div class="mb-3 col-6">
                            <label class="form-label" for='the_file'>
                                <?php echo gT("Select label set file (*.lsl):") . '<br>' . sprintf(gT("(Maximum file size: %01.2f MB)"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                            </label>
                            <input id='the_file' class="form-control col" name='the_file' type='file' accept=".lsl"/>
                            <div class="col"></div>
                            <div class="col"></div>
                        </div>
                        <div class="mb-3">
                            <label class=" form-label" for='checkforduplicates'>
                                <?php eT("Don't import if label set already exists:"); ?>
                            </label>
                            <input id="ytcheckforduplicates" name="checkforduplicates" type="hidden" value="0" >
                            <input id="checkforduplicates" name="checkforduplicates" type="checkbox" value="1" checked>
                        </div>

                        <div class="mb-3">
                            <div class="">
                            <input type='submit' class='btn btn-outline-secondary' value='<?php eT("Import label set(s)"); ?>'/>
                                <input type='hidden' name='action' value='importlabels'/>
                            </div>
                        </div>
                        <?php echo CHtml::endForm() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script type="text/javascript">
    (function() {
        var $tabList = $('#edit-survey-text-element-language-selection');
        if ($tabList.length === 0) {
            return;
        }

        function getTabs() {
            return $tabList
                .find('[role="tab"]')
                .filter(':visible')
                .filter(function() {
                    return !$(this).hasClass('disabled')
                        && !$(this).is('[disabled]')
                        && $(this).attr('aria-disabled') !== 'true';
                });
        }

        function setActiveTab($next) {
            var $tabs = getTabs();
            $tabs.attr({
                'tabindex': '-1',
                'aria-selected': 'false'
            }).removeClass('active');

            $next.attr({
                'tabindex': '0',
                'aria-selected': 'true'
            }).addClass('active').trigger('focus');

            if (window.bootstrap && window.bootstrap.Tab) {
                window.bootstrap.Tab.getOrCreateInstance($next[0]).show();
            } else if (typeof $next.tab === 'function') {
                $next.tab('show');
            } else {
                $next.trigger('click');
            }
        }

        $tabList.on('keydown', '[role="tab"]', function(event) {
            var key = event.key;
            if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].indexOf(key) === -1) {
                return;
            }

            var $tabs = getTabs();
            if ($tabs.length < 2) {
                return;
            }

            var $current = $(this);
            var currentIndex = $tabs.index($current);
            if (currentIndex < 0) {
                return;
            }

            event.preventDefault();

            var nextIndex = currentIndex;
            if (key === 'ArrowLeft' || key === 'ArrowUp') {
                nextIndex = (currentIndex - 1 + $tabs.length) % $tabs.length;
            } else if (key === 'ArrowRight' || key === 'ArrowDown') {
                nextIndex = (currentIndex + 1) % $tabs.length;
            } else if (key === 'Home') {
                nextIndex = 0;
            } else if (key === 'End') {
                nextIndex = $tabs.length - 1;
            }

            setActiveTab($tabs.eq(nextIndex));
        });
    })();
</script>
</div>
