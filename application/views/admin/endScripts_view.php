<script type="text/javascript">
        <!--

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
        myform.submit();
        }

        //-->
</script>