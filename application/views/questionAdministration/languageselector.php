<div class="col-xs-12" <?= count($oSurvey->allLanguages)==1?'style="display:none"':'';?>>
    <div class="button-toolbar">
        <div class="btn-group" role="group" data-toggle="buttons">
            <?php foreach($oSurvey->allLanguages as $lang): ?>
                <?php if ($lang === $oSurvey->language): ?>
                    <label
                        class="btn btn-default lang-switch-button active"
                        data-lang="<?= $lang; ?>"
                    >
                    <input 
                        type="radio" 
                        name="lang-switch-button" 
                        value=""
                        checked="checked"
                    />
                        <?= getLanguageNameFromCode($lang, false); ?>
                    </label>
                <?php else: ?>
                    <label
                        class="btn btn-default lang-switch-button"
                        data-lang="<?= $lang; ?>"
                    >
                        <input 
                            type="radio" 
                            name="lang-switch-button" 
                            value=""
                        />
                        <?= getLanguageNameFromCode($lang, false); ?>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
            <!-- TODO: Chunk languages
            <button
                v-if="getInChunks.length > 1"
                class="btn btn-default dropdown-toggle"
                data-toggle="dropdown"
            >
                More Languages
                <span class="caret"></span>
            </button>
             <ul class="dropdown-menu">
                <li
                    v-for="(languageTerm, language) in getInChunks[1]"
                    :key="language+'-dropdown'"
                    @click.prevent="setCurrentLanguage(language)"
                >
                    <a href="#">{{ languageTerm }}</a>
                </li>
            </ul>
            -->
        </div>
    </div>
    <hr/>
</div>
