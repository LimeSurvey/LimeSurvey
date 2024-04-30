<h1 class="h1 pagetitle">
    <?php echo $this->title; ?>
    <span class="ls-separator visually-hidden"> : </span>
    <small class='d-block'><?php echo viewHelper::flatEllipsizeText($this->model->currentLanguageSettings->surveyls_title, TRUE, 60, '…') . " (" . gT("ID") . " " . $this->model->sid . ")"; ?></small>
</h1>
