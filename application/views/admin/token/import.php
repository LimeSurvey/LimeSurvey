<!-- Import.php -->
<div class='side-body'>
    <h3><?php eT("Import survey participants from CSV file"); ?></h3>

    <div class="row">
        <div class="col-12 content-right">
            <?php if (empty($sError)) : ?>
                <div class="jumbotron message-box">
                    <h2>gT("Participant file upload")</h2>
                </div>
            <?php else:?>
                <div class="jumbotron message-box message-box-error">
                    <h2>gT("Participant file upload")</h2>
                    <p class="lead text-danger"><?php gT("Error"); ?></p>
                    <p><?php echo $sError; ?></p>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>
