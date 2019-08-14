<?php
    /**
     * @var QuestionTheme $oQuestionTheme
     */
?>
<h3><?php eT('Available question themes:'); ?></h3>
<div class="row">
    <div class="col-sm-12 content-right">

        <div id="templates_no_db" class="grid-view">
            <table class="items table">
                <thead>
                <tr>
                    <th><?php eT('Preview'); ?></th>
                    <th><?php eT('Folder'); ?></th>
                    <th><?php eT('Description'); ?></th>
                    <th><?php eT('Type'); ?></th>
                    <th><?php eT('Extends'); ?></th>
                    <th></th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($oQuestionTheme->getAvailableQuestions() as $oTheme): ?>
                    <?php // echo $oTemplate; ?>
                    <tr class="odd">
                        <td class="col-md-1">preview</td>
                        <td class="col-md-2"><?php echo $oTheme->name; ?></td>
                        <td class="col-md-3"><?php echo $oTheme->description; ?></td>
                        <td class="col-md-2"><?php eT('XML themes'); ?></td>
                        <td class="col-md-2"><?php echo $oTheme->extends; ?></td>
                        <td class="col-md-1"><?php echo $oTheme->manifestbuttons; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>