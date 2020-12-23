<?php
/**
 * Question group bar
 * Copied from LS
 *
 * First usage for copy questions
 */

/**  @var string $closeBtnUrl */

?>

<div class='menubar surveybar' id="questiongroupbarid">
    <div class='row container-fluid'>

        <!-- Right Buttons -->
        <div class="col-sm-4 pull-right text-right">

            <!-- Save and close -->
                <a id="save-and-close-button-copy-question" class="btn btn-default" role="button">
                    <i class="fa fa-check-square"></i>
                    <?php eT("Save and close");?>
                </a>
 
            <!-- Close -->
                <a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>" role="button">
                    <span class="fa fa-close"></span>
                    <?php eT("Close");?>
                </a>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#save-and-close-button-copy-question").click(function(event) {
            event.preventDefault();
            $('#question-code-unique-warning').addClass('hidden');

            const sid = $('input[name=surveyId]').val();
            const qid = 0;
            const code = $('input[name=title]').val();

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
