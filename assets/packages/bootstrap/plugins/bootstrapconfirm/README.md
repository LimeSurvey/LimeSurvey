#bsconfirm instead of jsconfirm

usage is really easy, instead of

```javascript
if(confirm(confirmText)) {
    //do something
}
```

Just do

```javascript
$.bsconfirm(confirmText, LSvar.lang, function onClickOK(){
    //do something
    }
); 
```

If you like you may even add a "on click cancel button" function

```javascript
$.bsconfirm(confirmText, LSvar.lang, function onClickOK(){
    //do something
    }, function onClickCancelButton(){
    //do something
    }
); 
```

To compile this please install babel and unglifyjs.

