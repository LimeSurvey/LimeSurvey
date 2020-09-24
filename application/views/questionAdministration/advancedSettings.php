<div class="col-12 scope-apply-base-style scope-min-height">
    <div class="container-fluid" v-if="!loading && showAdvancedOptions" id="advanced-options-container">
        <div class="row scoped-tablist-container">
            <!--
            <template v-if="showSubquestionEdit || showAnswerOptionEdit">
                <ul class="nav nav-tabs scoped-tablist-subquestionandanswers" role="tablist">
                    <li 
                        v-if="showSubquestionEdit"
                        :class="currentTabComponent == 'subquestions' ? 'active' : ''"
                    >
                        <a href="#" @click.prevent.stop="selectCurrentTab('subquestions')" >{{"subquestions" | translate }}</a>
                    </li>
                    <li 
                        v-if="showAnswerOptionEdit"
                        :class="currentTabComponent == 'answeroptions' ? 'active' : ''"
                    >
                        <a href="#" @click.prevent.stop="selectCurrentTab('answeroptions')" >{{"answeroptions" | translate }}</a>
                    </li>
                </ul>
            </template>
            -->
            <!-- Advanced settings tabs -->
            <ul class="nav nav-tabs scoped-tablist-advanced-settings" role="tablist">
                <?php foreach ($advancedSettings as $category => $_) : ?>
                    <li role="presentation">
                        <a
                            href="#<?= $category; ?>"
                            aria-controls="<?= $category; ?>"
                            role="tab"
                            data-toggle="tab"
                            >
                            <?= $category; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content">
            <?php foreach ($advancedSettings as $category => $settings): ?>
                <div role="tabpanel" class="tab-pane" id="<?= $category; ?>">
                    <?php foreach ($settings as $setting): ?>
                        <?php $this->widget('ext.AdvancedSettingWidget.AdvancedSettingWidget', ['setting' => $setting]); ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
