</div>
<script type="text/javascript">
        <!--
        for(i=0; i<document.forms.length; i++)
        {
        var el = document.createElement('input');
        el.type = 'hidden';
        el.name = 'checksessionbypost';
        el.value = '<?php echo $checksessionpost; ?>';
        document.forms[i].appendChild(el);
        }
        
        function addHiddenElement(theform,thename,thevalue)
        {
        var myel = document.createElement('input');
        myel.type = 'hidden';
        myel.name = thename;
        theform.appendChild(myel);
        myel.value = thevalue;
        return myel;
        }
        
        function sendPost(myaction,checkcode,arrayparam,arrayval)
        {
        var myform = document.createElement('form');
        document.body.appendChild(myform);
        myform.action =myaction;
        myform.method = 'POST';
        for (i=0;i<arrayparam.length;i++)
        {
        addHiddenElement(myform,arrayparam[i],arrayval[i])
        }
        addHiddenElement(myform,'checksessionbypost',checkcode)
        myform.submit();
        }
        
        //-->
</script>