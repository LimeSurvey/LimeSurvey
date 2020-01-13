<?php
/**
 * Display the result of the exportation
 */
?>
<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12">
            
                <!-- Jumbotron -->
                <div class="jumbotron message-box">
                    <h2 class="text-success"><?php eT("Import question group") ?></h2>
                    <p class="lead text-success"><?php eT("Success") ?></p>
                    <p>
                        <?php eT("File upload succeeded.") ?>                    
                    </p>
                    <p>
                        <?php gT("Question group import summary") ?>         
                    </p>
                    
                    <!-- results -->
                    <p>
                        <ul class="list-unstyled">
                            <li><?php echo gT("Question groups") .": " .$aImportResults['groups'] ?></li>
                            <li><?php echo gT("Questions").": ".$aImportResults['questions'] ?></li>
                            <li><?php echo gT("Subquestions").": ".$aImportResults['subquestions'] ?></li>
                            <li><?php echo gT("Answers").": ".$aImportResults['answers'] ?></li>
                            <li><?php echo gT("Conditions").": ".$aImportResults['conditions'] ?></li>
                            <?php if (strtolower($sExtension)=='csv'):?>
                                    <li><?php echo gT("Label sets").": ".$aImportResults['labelsets']." (".$aImportResults['labels'].")" ?></li>
                            <?php endif;?>
                            <li><?php echo gT("Question attributes:") . $aImportResults['question_attributes'] ?></li>
                         </ul>                        
                    </p>
                    <p class="text-info"><?php eT("Question group import is complete.") ?></p>
                    
                    <!-- button -->
                    <p>
                        <a href="<?php echo $this->createUrl('admin/questiongroups/sa/view/surveyid/'.$surveyid.'/gid/'.$aImportResults['newgid']) ?>" class="btn btn-default btn-lg" ><?php eT("Go to question group") ?></a>                         
                    </p>
                </div>                                 
        </div>
    </div>
</div>
