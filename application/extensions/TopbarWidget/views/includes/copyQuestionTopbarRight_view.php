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
            $('#question-title-warning').text("");
            $('#question-title-warning').addClass('hidden');
            const sid = $('input[name=surveyId]').val();
            const qid = 0;
            const code = $('input[name=question\\[title\\]]').val();

            $.ajax({
              url: "<?= Yii::app()->createUrl('questionAdministration/checkQuestionValidateTitle'); ?>",
              method: 'GET',
              data: {
                sid,
                qid,
                code
              },
              success: (data) => {
                if (data) {
                    $('#question-title-warning').text(data);
                    $('#question-title-warning').removeClass('hidden');
                } else {
                    $("#submit-copy-question").click();
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
