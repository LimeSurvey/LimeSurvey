<!-- modal-gallery is the modal dialog used for the image gallery -->
<div id="modal-gallery" class="modal modal-gallery hide fade" tabindex="-1">
    <div class="modal-header">
        <a class="btn-close" data-bs-dismiss="modal"></a>
        <h3 class="modal-title"></h3>
    </div>
    <div class="modal-body"><div class="modal-image"></div></div>
    <div class="modal-footer">
        <button 
            role="button" 
            class="btn btn-primary modal-next">
            Next 
            <i class="ri-arrow-right-fill icon-white"></i>
        </button>

        <button 
            class="btn btn-info modal-prev"
            type="button">
            <i class="ri-arrow-left-fill icon-white"></i>
             Previous
        </button>

        <button 
            class="btn btn-primary modal-play modal-slideshow"
            type="button" 
            data-slideshow="5000">
            <i class="icon-play icon-white"></i>
             Slideshow
        </button>
        <a class="btn modal-download" target="_blank"><i class="icon-download"></i> Download</a>
    </div>
</div>

