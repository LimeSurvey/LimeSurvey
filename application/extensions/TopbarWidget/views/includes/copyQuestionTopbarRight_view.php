<!-- Close -->
<a class="btn btn-outline-secondary" href="<?php echo $closeUrl; ?>">
    <i class="ri-close-fill"></i>
    <?php eT("Close");?>
</a>

<!-- Save and close -->
<a id="save-and-close-button-copy-question" class="btn btn-success">
    <i class="ri-check-fill"></i>
    <?php eT("Save and close");?>
</a>

<script type="text/javascript">
    $(document).ready(function() {
        $("#save-and-close-button-copy-question").click(function(event) {
            event.preventDefault();
            $('#question-title-warning').text("");
            $('#question-title-warning').addClass('d-none');
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
                    $('#question-title-warning').removeClass('d-none');
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
