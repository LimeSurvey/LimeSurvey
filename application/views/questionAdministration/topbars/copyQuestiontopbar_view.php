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
        $("#save-and-close-button-copy-question").click(function() {
            //$("#form_copy_question").submit();
            document.getElementById("submit-copy-question").click();
        });
    });
</script>
