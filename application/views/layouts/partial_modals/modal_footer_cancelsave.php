<?php
?>

<div class="modal-footer modal-footer-buttons">
    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
        <?php
        eT("Cancel"); ?>
    </a>
    <a role="button" tabindex="0" class="btn btn-primary btn-ok">
        <?php eT("Save"); ?>
    </a>
</div>
<script>
    (function(){
        // When modal is shown, pressing Enter (unless in a textarea or contentEditable) triggers the primary .btn-ok (for anchors too)
        document.addEventListener('shown.bs.modal', function(e){
            var modal = e.target;
            if (!modal) return;
            var handler = function(evt){
                if (evt.key !== 'Enter') return;
                var target = evt.target || document.activeElement;
                var tag = (target && target.tagName) ? target.tagName.toLowerCase() : '';
                if (tag === 'textarea') return;
                if (target && target.isContentEditable) return;
                if (evt.ctrlKey || evt.altKey || evt.metaKey) return;
                var ok = modal.querySelector('.modal-footer .btn-ok');
                if (ok) {
                    evt.preventDefault();
                    // If it's an anchor without href, ensure click handlers are invoked
                    ok.click();
                }
            };
            modal.addEventListener('keydown', handler);
            modal.addEventListener('hidden.bs.modal', function remove(){
                modal.removeEventListener('keydown', handler);
                modal.removeEventListener('hidden.bs.modal', remove);
            });
        });
    })();
</script>
