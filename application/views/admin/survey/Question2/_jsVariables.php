<script>
    window.QuestionEditData = <?=json_encode($data)?>;
    window.questionEditButton = '<?=addslashes(str_replace(["\n\r","\n","\r"], '', $oQuestionSelector->getButtonOrSelect(true)));?>';
</script>