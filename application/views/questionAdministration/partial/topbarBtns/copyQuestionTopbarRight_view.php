<!-- Close -->
<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'close-button',
        'text' => gT('Close'),
        'icon' => 'ri-close-fill',
        'link' => $closeUrl,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);
?>

<!-- Save and close -->
<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'id' => 'save-and-close-button-copy-question',
        'name' => 'save-and-close-button-copy-question',
        'text' => gT('Save and close'),
        'icon' => 'ri-check-fill',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
        ],
    ]
);
?>

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
                if (data.hasOwnProperty('message') && data.message === null) {
                      $("#submit-copy-question").click();
                  } else {
                      $('#question-title-warning').text(data.hasOwnProperty('message') ? data.message : data);
                      $('#question-title-warning').removeClass('d-none');
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
