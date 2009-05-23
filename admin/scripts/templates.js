// $Id$
// based on TTabs from http://interface.eyecon.ro/

$(document).ready(function(){
  $("textarea").bind('keydown',
    function(e){
      pressedKey = e.charCode || e.keyCode || -1;
      if (pressedKey == 9) {
        if (window.event) {
          window.event.cancelBubble = true;
          window.event.returnValue = false;
        } else {
          e.preventDefault();
          e.stopPropagation();
        }

        // save current scroll position for later restoration
        var oldScrollTop=this.scrollTop;

        if (this.createTextRange) {
          document.selection.createRange().text="\t";
          this.onblur = function() { this.focus(); this.onblur = null; };
        } else if (this.setSelectionRange) {
          start = this.selectionStart;
          end = this.selectionEnd;
          this.value = this.value.substring(0, start) + "\t" + this.value.substr(end);
          this.setSelectionRange(start + 1, start + 1);
          this.focus();
        }

        this.scrollTop=oldScrollTop;

        return false;
      }
    }
  );
});
