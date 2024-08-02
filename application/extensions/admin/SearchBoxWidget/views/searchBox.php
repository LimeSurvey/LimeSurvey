<!-- Search Box -->

<!-- Begin Form -->
<div class="row">
    <div class="menu col-12">
        <?php $form = $this->beginWidget('CActiveForm', ['action' => App()->createUrl($this->formUrl), 'method' => 'get', 'htmlOptions' => ['id' => 'survey-search'],]); ?>
        <div class="row">

            <!-- select state -->
            <div class="col-1">
                <h2><?php eT('All surveys'); ?></h2>
            </div>
            <?php if ($this->onlyfilter) : ?>
                <div class="offset-8 col-2">
                    <div  class="pull-right">
                        <div class="dropdown">
                            <select name="active" id='survey_active' class="form-select">
                                <option value="" <?= empty($this->model->active) ? "selected" : '' ?>>
                                    <?= gT('Status') ?>
                                </option>
                                <option value="Y" <?= $this->model->active === "Y" ? "selected" : '' ?>>
                                    <?= gT('Active') ?>
                                </option>
                                <option value="R" <?= $this->model->active === "R" ? "selected" : '' ?>>
                                    <?= gT('Running') ?>
                                </option>
                                <option value="N" <?= $this->model->active === "N" ? "selected" : '' ?>>
                                    <?= gT('Inactive') ?>
                                </option>
                                <option value="E" <?= $this->model->active === "E" ? "selected" : '' ?>>
                                    <?= gT('Expired') ?>
                                </option>
                                <!--                            <option value="S" --><?php //= $this->model->active === "S" ? "selected" : '' ?><!-->-->
                                <!--                                --><?php //= gT('Active but not yet started') ?>
                                <!--                            </option>-->
                            </select>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <!-- select group -->
                <div class="col-6">
                    <div  class="pull-right">

                        <div class="search-bar">
                            <?= $form->textField($this->model, 'searched_value', ['class' => 'form-control', 'placeholder' => 'Search']) ?>
                            <i class="ri-search-line"></i>
                        </div>
                        <div class="dropdown">
                            <select name="active" id='survey_active' class="form-select">
                                <option value="" <?= empty($this->model->active) ? "selected" : '' ?>>
                                    <?= gT('Status') ?>
                                </option>
                                <option value="Y" <?= $this->model->active === "Y" ? "selected" : '' ?>>
                                    <?= gT('Active') ?>
                                </option>
                                <option value="R" <?= $this->model->active === "R" ? "selected" : '' ?>>
                                    <?= gT('Running') ?>
                                </option>
                                <option value="N" <?= $this->model->active === "N" ? "selected" : '' ?>>
                                    <?= gT('Inactive') ?>
                                </option>
                                <option value="E" <?= $this->model->active === "E" ? "selected" : '' ?>>
                                    <?= gT('Expired') ?>
                                </option>
                            </select>
                        </div>
                        <div class="dropdown">
                            <select name="gsid" id='survey_gsid' class="form-select">
                                <option value=""><?= gT('Group') ?></option>
                                <?php foreach (SurveysGroups::getSurveyGroupsList() as $gsid => $group_title) : ?>
                                    <option value="<?= $gsid ?>" <?= ($gsid === $this->model->gsid) ? "selected" : "" ?>><?= CHtml::encode($group_title) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button id="survey_reset" class="btn btn-outline-secondary menu-button b-none <?= !empty(App()->request->getParam('Survey')) ? '' : 'd-none' ?>">
                            <i class="ri-refresh-line"></i>
                            <?= gT('Reset') ?>
                        </button>

                    </div>
                </div>

                <div class="col-4">
                    <div class="pull-right">
                        <a href="<?= Yii::app()->createUrl('surveyAdministration/newSurvey') ?>" class="btn btn-outline-secondary menu-button purple purple-bg">
                            <i class="ri-add-line"></i>
                            <?= gT('Create survey') ?>
                        </a>
                        <a href="<?= Yii::app()->createUrl('admin/surveysgroups/sa/create') ?>" class="btn btn-outline-secondary menu-button">
                            <i class="ri-add-line"></i>
                            <?= gT('Create survey group') ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($this->switch) : ?>
                <div class="col-1">
                    <div class="pull-right menu-icon">
                        <i class="view-switch ri-grid-fill <?php echo SettingsUser::getUserSettingValue('welcome_page_widget') == 'box-widget' ? 'purple' : ''?>" data-action="box-widget"></i>
                        <i class="view-switch ri-menu-line <?php echo SettingsUser::getUserSettingValue('welcome_page_widget') == 'list-widget' ? 'purple' : ''?>" data-action="list-widget"></i>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
