export const PLACEHOLDER_HTML = '<div class="upload-placeholder"><span class="ri-file-line" aria-hidden="true"></span></div>';
// Not "placeholder": that collides with Bootstrap's .placeholder loading style
export const PLACEHOLDER_CELL_CLASS = 'no-preview';

/**
 * Replaces uploaded image previews the browser cannot decode (e.g. HEIC outside Safari)
 * with the generic file placeholder.
 * @param {jQuery} $images
 */
export function applyImagePreviewFallback($images) {
    $images.each(function () {
        const img = this;
        const replaceWithPlaceholder = function () {
            const $img = $(img);
            $img.closest('.upload.image').removeClass('image').addClass(PLACEHOLDER_CELL_CLASS);
            $img.replaceWith(PLACEHOLDER_HTML);
        };
        // The image may already have failed before the handler was attached
        if (img.complete && img.naturalWidth === 0) {
            replaceWithPlaceholder();
            return;
        }
        $(img).one('error', replaceWithPlaceholder);
    });
}
