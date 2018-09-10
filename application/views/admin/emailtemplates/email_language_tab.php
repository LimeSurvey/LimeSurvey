<?php
    $tabs = emailtemplates::getTabTypeArray();

    echo "<div id='tab-".CHtml::encode($grouplang)."' class='tab-pane fade in ".CHtml::encode($active)."'>";
?>



                    <ul class="nav nav-tabs">
                        <?php
                        $count = 0;
                        $state = 'active';
                        foreach ($tabs as $tab => $details)
                        {
                            
                            echo "<li role='presentation' class='$state'><a  data-toggle='tab' href='#tab-$grouplang-$tab'>{$details['title']}</a></li>";
                            if($count == 0){ $state = ''; $count++;}
                        }
                        ?>
                    </ul>

                    <div class="tab-content tabsinner" id='tabsinner-<?php echo $grouplang; ?>'>
                        <?php
                        $count = 0;
                        $active = 'active';                        
                        foreach ($tabs as $tab => $details)
                        {
                            $this->renderPartial('/admin/emailtemplates/email_language_template_tab', compact('ishtml', 'surveyid' , 'esrow', 'grouplang', 'tab', 'details', 'active'));
                            if($count == 0){ $active = ''; $count++;}
                        }
                        ?>
                    </div>
            </div>
