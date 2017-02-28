<script type='text/javascript'>
         var qtypes = new Array();
         var qnames = new Array();
         var qhelp = new Array();
         var qcaption = new Array();


        function OtherSelection(QuestionType)
        {
            if (QuestionType == undefined)
            {
                //console.log('Error: OtherSelection: QuestionType must not be undefined');
                return;
            }

            if (QuestionType == '') { QuestionType=document.getElementById('question_type').value;}
            if (QuestionType == '<?php echo Question::QT_M_MULTIPLE_CHOICE; ?>' || QuestionType == '<?php echo Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS; ?>' || QuestionType == '<?php echo Question::QT_L_LIST_DROPDOWN; ?>' || QuestionType == '<?php echo Question::QT_EXCLAMATION_LIST_DROPDOWN; ?>')
            {
                document.getElementById('OtherSelection').style.display = '';
                document.getElementById('Validation').style.display = 'none';
                document.getElementById('MandatorySelection').style.display='';
            }
            else if (QuestionType == '<?php echo Question::QT_W; ?>' || QuestionType == '<?php echo Question::QT_Z_LIST_RADIO_FLEXIBLE; ?>')
            {
                document.getElementById('OtherSelection').style.display = '';
                document.getElementById('Validation').style.display = 'none';
                document.getElementById('MandatorySelection').style.display='';
            }
            else if (QuestionType == '<?php echo Question::QT_VERTICAL_FILE_UPLOAD; ?>')
            {
                document.getElementById('OtherSelection').style.display = 'none';
                document.getElementById('Validation').style.display = 'none';
                document.getElementById('MandatorySelection').style.display='none';
            }
            else if (QuestionType == '<?php echo Question::QT_F_ARRAY_FLEXIBLE_ROW; ?>' || QuestionType == '<?php echo Question::QT_H_ARRAY_FLEXIBLE_COLUMN; ?>')
            {
                document.getElementById('OtherSelection').style.display = 'none';
                document.getElementById('Validation').style.display = 'none';
                document.getElementById('MandatorySelection').style.display='';
            }
            else if (QuestionType == '<?php echo Question::QT_COLON_ARRAY_MULTI_FLEX_NUMBERS; ?>' || QuestionType == '<?php echo Question::QT_SEMICOLON_ARRAY_MULTI_FLEX_TEXT; ?>')
            {
                document.getElementById('OtherSelection').style.display = 'none';
                document.getElementById('Validation').style.display = '';
                document.getElementById('MandatorySelection').style.display='';
            }
            else if (QuestionType == '<?php echo Question::QT_1_ARRAY_MULTISCALE; ?>')
            {
                document.getElementById('OtherSelection').style.display = 'none';
                document.getElementById('Validation').style.display = 'none';
                document.getElementById('MandatorySelection').style.display='';
            }
            else if (QuestionType == '<?php echo Question::QT_S_SHORT_FREE_TEXT; ?>' || QuestionType == '<?php echo Question::QT_T_LONG_FREE_TEXT; ?>' || QuestionType == '<?php echo Question::QT_U_HUGE_FREE_TEXT; ?>' || QuestionType == '<?php echo Question::QT_N_NUMERICAL; ?>' || QuestionType=='' || QuestionType=='<?php echo Question::QT_K_MULTIPLE_NUMERICAL_QUESTION; ?>')
            {
                document.getElementById('Validation').style.display = '';
                document.getElementById('OtherSelection').style.display ='none';
                if (document.getElementById('ON'))  { document.getElementById('ON').checked = true;}
                document.getElementById('MandatorySelection').style.display='';
            }
            else if (QuestionType == '<?php echo Question::QT_X_BOILERPLATE_QUESTION; ?>')
            {
                document.getElementById('Validation').style.display = 'none';
                document.getElementById('OtherSelection').style.display ='none';
                document.getElementById('MandatorySelection').style.display='none';
            }
            else if (QuestionType == '<?php echo Question::QT_Q_MULTIPLE_SHORT_TEXT; ?>')
            {
                document.getElementById('Validation').style.display = '';
                document.getElementById('OtherSelection').style.display ='none';
                document.getElementById('MandatorySelection').style.display='';
            }
            else
            {
                document.getElementById('OtherSelection').style.display = 'none';
                if (document.getElementById('ON'))  { document.getElementById('ON').checked = true;}
                document.getElementById('Validation').style.display = 'none';
                document.getElementById('MandatorySelection').style.display='';
            }
        }
        OtherSelection('<?php echo $type; ?>');
</script>
