<div id="notify-container" style="display:none;">
    <div id="default-notify"  class="ui-state-highlight ui-corner-all">
        <p>
            <a class="ui-notify-close" href="#"><span class="ui-icon ui-icon-close" style="float:right">&nbsp;</span></a>
            <span style="float:left; margin:2px 5px 0 0;" class="ui-icon ui-icon-info">&nbsp;</span>
            #{message}
        </p>
    </div>
    <div id="error-notify"  class="ui-state-highlight ui-corner-all ui-state-error error">
        <p> 
            <a class="ui-notify-close" href="#"><span class="ui-icon ui-icon-close" style="float:right">&nbsp;</span></a>
            <span style="float:left; margin:2px 5px 0 0;" class="ui-icon ui-icon-alert">&nbsp;</span>
             #{message}
        </p>
    </div>
    <!-- ui-state-success doesn't exist -->
    <div id="success-notify"  class="ui-state-highlight ui-corner-all ui-state-success success">
        <p>
            <a class="ui-notify-close" href="#"><span class="ui-icon ui-icon-close" style="float:right">&nbsp;</span></a>
            <span style="float:left; margin:2px 5px 0 0;" class="ui-icon ui-icon-alert">&nbsp;</span>
             #{message}
        </p>
    </div>
</div>
