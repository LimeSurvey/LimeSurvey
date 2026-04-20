<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('dumpdb');
?>

<?php if (!$htmlContent) { ?>

    <div class="row">
        <div class="col-12">
            <!-- Data redundancy check -->
            <div class="jumbotron message-box">
                <h2><?php eT("Database size check"); ?></h2>
                <p class="lead">
                    <?php eT("This check evaluates the database size to determine if an immediate download is possible or if a manual backup is necessary."); ?>
                </p>
                <p>
                    <?php echo "DB Size: MB " . $dbSize; ?>
                    <?php if ($downloadable) { ?>
                        <?php
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text' => gT("Your database can be downloaded now!"),
                            'type' => 'success',
                        ]);
                        ?>
                        <?php echo CHtml::form(["admin/dumpdb", "sa" => 'outPutDatabase'], 'post'); ?>
                        <button
                            type='submit'
                            value='Y'
                            name='ok'
                            class="btn btn-info">
                            <?php eT("Yes - download now!"); ?>
                        </button>
                        </form>
                    <?php } else { ?>
                        <?php
                            $this->widget('ext.AlertWidget.AlertWidget', [
                                'text' => gT("Your database is too large for immediate download. Please use your database client to perform a manual backup."),
                                'type' => 'warning',
                            ]);
                        ?>
                    <?php } ?>
                </p>
            </div>
        </div>
    </div>
<?php } else {
    echo $htmlContent;
} ?>