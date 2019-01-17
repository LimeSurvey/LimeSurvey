<script>
    window.QuestionEditData = <?=json_encode($data)?>;
    window.questionEditButton = '<?=str_replace(["\n\r","\n","\r"], '', $oQuestionSelector->getButtonOrSelect(true));?>';
</script>