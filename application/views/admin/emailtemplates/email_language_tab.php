<?php
    $tabs = array(
        'invitation' => array(
            'title' => $clang->gT("Invitation"),
            'subject' => $clang->gT("Invitation email subject:"),
            'body' => $clang->gT("Invitation email body:"),
            'attachments' => $clang->gT("Invitation attachments:"),
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
            'title' => $clang->gT("Reminder"),
            'subject' => $clang->gT("Reminder email subject:"),
            'body' => $clang->gT("Reminder email body:"),
            'attachments' => $clang->gT("Reminder attachments:"),
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
            'title' => $clang->gT("Confirmation"),
            'subject' => $clang->gT("Confirmation email subject:"),
            'body' => $clang->gT("Confirmation email body:"),
            'attachments' => $clang->gT("Confirmation attachments:"),
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
            'title' => $clang->gT("Registration"),
            'subject' => $clang->gT("Registration email subject:"),
            'body' => $clang->gT("Registration email body:"),
            'attachments' => $clang->gT("Registration attachments:"),
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
            'title' => $clang->gT("Basic admin notification"),
            'subject' => $clang->gT("Basic admin notification subject:"),
            'body' => $clang->gT("Basic admin notification email body:"),
            'attachments' => $clang->gT("Basic notification attachments:"),
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
            'title' => $clang->gT("Detailed admin notification"),
            'subject' => $clang->gT("Detailed admin notification subject:"),
            'body' => $clang->gT("Detailed admin notification email body:"),
            'attachments' => $clang->gT("Detailed notification attachments:"),
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
    
    
    echo "<div id='tab-$grouplang'>";
    echo "<div class='tabsinner' id='tabsinner-$grouplang'>";
?>

            
                
                    <ul>
                        <?php
                        foreach ($tabs as $tab => $details)
                        {
                            echo "<li><a href='#tab-$grouplang-$tab'>{$details['title']}</a></li>";
                        }
                        ?>
                    </ul>

                    <?php
                    foreach ($tabs as $tab => $details)
                    {
                        $this->renderPartial('/admin/emailtemplates/email_language_template_tab', compact('ishtml', 'surveyid' , 'esrow', 'grouplang', 'tab', 'details', 'clang'));
                    }
                    ?>
                </div>
            </div>
