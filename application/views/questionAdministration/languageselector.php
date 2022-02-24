<div class="form-group col-sm-6 col-lg-7 text-right" <?= count($oSurvey->allLanguages)==1?'style="display:none"':'';?>>
    <label>&nbsp;</label>
    <div class="button-toolbar">
        <div class="btn-group" role="group" data-toggle="buttons">
            <button id="language-dropdown" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span id="language-dropdown-text"><?= getLanguageNameFromCode($oSurvey->language, false); ?></span> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <?php foreach ($oSurvey->allLanguages as $lang): ?>
                    <li class="<?= $lang === $oSurvey->language ? ' active' : '' ?>">
                        <a href="#" class="lang-switch-button" data-lang="<?= $lang; ?>">
                            <?= getLanguageNameFromCode($lang, false); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
