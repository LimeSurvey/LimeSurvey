<script type='text/javascript'>
        if (navigator.userAgent.indexOf("Gecko") != -1)
        window.addEventListener("load", init_gecko_select_hack, false);
        
         var qtypes = new Array();
         var qnames = new Array();
         var qhelp = new Array();
         var qcaption = new Array();
    
        
        function OtherSelection(QuestionType)
        {
        if (QuestionType == '') { QuestionType=document.getElementById('question_type').value;}
        if (QuestionType == 'M' || QuestionType == 'P' || QuestionType == 'L' || QuestionType == '!')
        {
            document.getElementById('OtherSelection').style.display = '';
            document.getElementById('Validation').style.display = 'none';
            document.getElementById('MandatorySelection').style.display='';
        }
        else if (QuestionType == 'W' || QuestionType == 'Z')
        {
            document.getElementById('OtherSelection').style.display = '';
            document.getElementById('Validation').style.display = 'none';
            document.getElementById('MandatorySelection').style.display='';
        }
        else if (QuestionType == '|')
        {
            document.getElementById('OtherSelection').style.display = 'none';
            document.getElementById('Validation').style.display = 'none';
            document.getElementById('MandatorySelection').style.display='none';
        }
        else if (QuestionType == 'F' || QuestionType == 'H' || QuestionType == ':' || QuestionType == ';')
        {
            document.getElementById('OtherSelection').style.display = 'none';
            document.getElementById('Validation').style.display = 'none';
            document.getElementById('MandatorySelection').style.display='';
        }
        else if (QuestionType == '1')
        {
            document.getElementById('OtherSelection').style.display = 'none';
            document.getElementById('Validation').style.display = 'none';
            document.getElementById('MandatorySelection').style.display='';
        }
        else if (QuestionType == 'S' || QuestionType == 'T' || QuestionType == 'U' || QuestionType == 'N' || QuestionType=='' || QuestionType=='K')
        {
            document.getElementById('Validation').style.display = '';
            document.getElementById('OtherSelection').style.display ='none';
            if (document.getElementById('ON'))  { document.getElementById('ON').checked = true;}
            document.getElementById('MandatorySelection').style.display='';
        }
        else if (QuestionType == 'X')
        {
            document.getElementById('Validation').style.display = 'none';
            document.getElementById('OtherSelection').style.display ='none';
            document.getElementById('MandatorySelection').style.display='none';
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