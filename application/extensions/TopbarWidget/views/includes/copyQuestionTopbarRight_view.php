<!-- Close -->
<a class="btn btn-default" href="<?php echo $closeUrl; ?>" role="button">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</a>

<!-- Save and close -->
<a id="save-and-close-button-copy-question" class="btn btn-success" role="button">
    <i class="fa fa-check"></i>
    <?php eT("Save and close");?>
</a>

<script type="text/javascript">
    $(document).ready(function() {
        $("#save-and-close-button-copy-question").click(function(event) {
            event.preventDefault();
            $('#question-code-unique-warning').addClass('hidden');

            const sid = $('input[name=surveyId]').val();
            const qid = 0;
            const code = $('input[name=question\\[title\\]]').val();

            $.ajax({
              url: "<?= Yii::app()->createUrl('questionAdministration/checkQuestionCodeUniqueness'); ?>",
              method: 'GET',
              data: {
                sid,
                qid,
                code
              },
              success: (data) => {
                if (data === 'true') {
                    document.getElementById("submit-copy-question").click();
                } else {
                    $('#question-code-unique-warning').removeClass('hidden');
                }
              },
              error: (data) => {
                alert('Internal error: ' + data);
                throw 'abort';
              }
            });
            return false;
        });
    });
</script>