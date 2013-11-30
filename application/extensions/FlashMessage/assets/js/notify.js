$(document).ready(function(){
    $notifycontainer=$("#notify-container").notify({
      speed: 500,
      custom: true,
      expires: 5000
    });
    $.each(LS.messages,function(key, oMessage){
        if(typeof oMessage.message ==="string")
        {
            if(typeof oMessage.type !="string")
            {
                oMessage.template="default-notify";
            }
            else
            {
                oMessage.template=oMessage.type+"-notify";
            }
        }
        if(typeof oMessage.detail !=="string")
        {
            oMessage.detail="";
        }
        $notifycontainer.notify("create", oMessage.template, { message:oMessage.message,detail:oMessage.detail});
    });
});
