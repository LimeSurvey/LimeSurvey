<?php
    $tabs = array(
        'invitation' => array(
            'title' => gT("Invitation"),
            'subject' => gT("Invitation email subject:"),
            'body' => gT("Invitation email body:"),
            'attachments' => gT("Invitation attachments:"),
            'field' => array(
                'subject' => 'surveyls_email_invite_subj',
                'body' => 'surveyls_email_invite'
            ),
            'default' => array(
                'subject' => $aDefaultTexts['invitation_subject'],
                'body' => $aDefaultTexts['invitation']
            )
        ),
        'reminder' => array(
            'title' => gT("Reminder"),
            'subject' => gT("Reminder email subject:"),
            'body' => gT("Reminder email body:"),
            'attachments' => gT("Reminder attachments:"),
            'field' => array(
                'subject' => 'surveyls_email_remind_subj',
                'body' => 'surveyls_email_remind'
            ),
            'default' => array(
                'subject' => $aDefaultTexts['reminder_subject'],
                'body' => $aDefaultTexts['reminder']
            )
        ),
        'confirmation' => array(
            'title' => gT("Confirmation"),
            'subject' => gT("Confirmation email subject:"),
            'body' => gT("Confirmation email body:"),
            'attachments' => gT("Confirmation attachments:"),
            'field' => array(
                'subject' => 'surveyls_email_confirm_subj',
                'body' => 'surveyls_email_confirm'
            ),
            'default' => array(
                'subject' => $aDefaultTexts['confirmation_subject'],
                'body' => $aDefaultTexts['confirmation']
            )
        ),
        'registration' => array(
            'title' => gT("Registration"),
            'subject' => gT("Registration email subject:"),
            'body' => gT("Registration email body:"),
            'attachments' => gT("Registration attachments:"),
            'field' => array(
                'subject' => 'surveyls_email_register_subj',
                'body' => 'surveyls_email_register'
            ),
            'default' => array(
                'subject' => $aDefaultTexts['registration_subject'],
                'body' => $aDefaultTexts['registration']
            )
        ),
        'admin_notification' => array(
            'title' => gT("Basic admin notification"),
            'subject' => gT("Basic admin notification subject:"),
            'body' => gT("Basic admin notification email body:"),
            'attachments' => gT("Basic notification attachments:"),
            'field' => array(
                'subject' => 'email_admin_notification_subj',
                'body' => 'email_admin_notification'
            ),
            'default' => array(
                'subject' => $aDefaultTexts['admin_notification_subject'],
                'body' => $aDefaultTexts['admin_notification']
            )
        ),
        'admin_detailed_notification' => array(
            'title' => gT("Detailed admin notification"),
            'subject' => gT("Detailed admin notification subject:"),
            'body' => gT("Detailed admin notification email body:"),
            'attachments' => gT("Detailed notification attachments:"),
            'field' => array(
                'subject' => 'email_admin_responses_subj',
                'body' => 'email_admin_responses'
            ),
            'default' => array(
                'subject' => $aDefaultTexts['admin_detailed_notification_subject'],
                'body' => $aDefaultTexts['admin_detailed_notification']
            )
        )
    );


    echo "<div id='tab-$grouplang' class='tab-pane fade in ".$active."'>";
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
