<div class="col-xs-12" >
    <div class="button-toolbar" :id="elId+'-language-selector'">
        <div class="btn-group">
            <?php foreach($this->aData['languagelist'] as $lang): ?>
                <button
                    :key="language+'-button'"
                    class="btn btn-default"
                    @click.prevent="setCurrentLanguage(language)"
                >
                    <!-- TODO: Mark active class="'btn btn-'+(language==currentLanguage ? 'primary active' : 'default')"-->
                    <?= getLanguageNameFromCode($lang, false); ?>
                </button>
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
