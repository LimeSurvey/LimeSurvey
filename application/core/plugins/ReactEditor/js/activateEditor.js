$(document).ready(function () {
    let savedViaSwitch = false;

    // Reads slides from data-slides JSON on #activate_editor.
    const EditorSlider = (function () {
        let slides = [];
        let current = 0;
        let $modal = null;

        function renderSlide(index, animate = true) {
            const slide = slides[index];
            const $title = $modal.find(".editor-slider-title");
            const $image = $modal.find(".editor-slider-image");
            const $desc = $modal.find(".editor-slider-description");
            const $dots = $modal.find(".editor-slider-dot");
            const FADE_MS = 250;

            const updateContent = () => {
                $title.html(slide.title);
                $image.attr("src", slide.image).attr("alt", slide.title);
                $desc.html(slide.description);

                $dots
                    .removeClass("editor-slider-dot--active")
                    .attr("aria-selected", "false");
                $dots
                    .eq(index)
                    .addClass("editor-slider-dot--active")
                    .attr("aria-selected", "true");

                $modal
                    .find(".editor-slider-arrow--prev")
                    .toggleClass("invisible", index === 0);
                $modal
                    .find(".editor-slider-arrow--next")
                    .toggleClass("invisible", index === slides.length - 1);
            };

            if (animate) {
                $title
                    .add($desc)
                    .css({
                        opacity: 0,
                        transition: "opacity " + FADE_MS + "ms ease",
                    });

                setTimeout(function () {
                    updateContent();

                    $title.add($desc).css({ opacity: 1 });
                }, FADE_MS);
            } else {
                updateContent();
            }
        }

        function buildDots() {
            const $dotsContainer = $modal.find(".editor-slider-dots");
            $dotsContainer.empty();
            slides.forEach(function (_, i) {
                const $dot = $("<button>")
                    .attr({
                        type: "button",
                        role: "tab",
                        "aria-selected": i === 0 ? "true" : "false",
                    })
                    .addClass(
                        "editor-slider-dot" +
                            (i === 0 ? " editor-slider-dot--active" : ""),
                    );
                $dot.on("click", function () {
                    goTo(i);
                });
                $dotsContainer.append($dot);
            });
        }

        function goTo(index) {
            current = Math.max(0, Math.min(index, slides.length - 1));
            renderSlide(current);
        }

        function init(modalEl) {
            $modal = $(modalEl);
            current = 0;

            try {
                slides = JSON.parse($modal.attr("data-slides") || "[]");
            } catch (e) {
                slides = [];
            }
            if (!slides.length) {
                return;
            }

            buildDots();
            renderSlide(0, false);

            $modal
                .find(".editor-slider-arrow--prev")
                .off("click")
                .on("click", function () {
                    goTo(current - 1);
                });
            $modal
                .find(".editor-slider-arrow--next")
                .off("click")
                .on("click", function () {
                    goTo(current + 1);
                });
        }

        return { init: init };
    })();

    // Initialise slider and toggle auto-open vs manual sections
    $(document).on("show.bs.modal", "#activate_editor", function () {
        EditorSlider.init(this);
        const isAutoOpen = $(this).data("auto-open") === true;
        $(this).find(".editor-auto-open-section").toggleClass("d-none", !isAutoOpen);
        $(this).find(".editor-manual-section").toggleClass("d-none", isAutoOpen);
    });

    /**
     * Save the user decision if new editor is turned on or off
     */
    $(document).on("change", "#editor-switch-btn", function () {
        let newValue = $(this).find(".btn-check:checked").val();

        let url = $("#saveUrl").val();
        let data = { optin: newValue };
        savedViaSwitch = true;

        $.post(url, data)
            .done(function () {
                let successMessage =
                    newValue === "1"
                        ? $("#successMsgFeatureOptin").val()
                        : $("#successMsgFeatureOptout").val();
                $("#activate_editor").modal("hide");
                LS.ajaxAlerts(successMessage, "alert-success", {
                    showCloseButton: true,
                });
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
            })
            .fail(function () {
                $("#activate_editor").modal("hide");
                LS.ajaxAlerts($("#errorOnSave").val(), "alert-danger", {
                    showCloseButton: true,
                });
            });
    });

    /**
     * "Switch to new editor" button in auto-open mode — saves optin=1
     */
    $(document).on("click", "#switch-new-editor-btn", function () {
        savedViaSwitch = true;
        $.post($("#saveUrl").val(), { optin: 1 })
            .done(function () {
                $("#activate_editor").modal("hide");
                LS.ajaxAlerts($("#successMsgFeatureOptin").val(), "alert-success", {
                    showCloseButton: true,
                });
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
            })
            .fail(function () {
                $("#activate_editor").modal("hide");
                LS.ajaxAlerts($("#errorOnSave").val(), "alert-danger", {
                    showCloseButton: true,
                });
            });
    });

    /**
     * Handle modal close (via close button or clicking outside)
     *     We save the current selected value to make sure we have an entry in the db,
     *     because the modal opens automatically once for users without a saved entry
     */
    $(document).on("hide.bs.modal", "#activate_editor", function () {
        const isAutoOpen = $(this).data("auto-open") === true;
        if (!savedViaSwitch) {
            if (isAutoOpen) {
                // Dismissed auto-open modal without choosing — save classic (0)
                $.post($("#saveUrl").val(), { optin: 0 }).fail(function () {
                    LS.ajaxAlerts($("#errorOnSave").val(), "alert-danger", {
                        showCloseButton: true,
                    });
                });
            } else {
                let currentValue = $("#editor-switch-btn")
                    .find(".btn-check:checked")
                    .val();
                $.post($("#saveUrl").val(), { optin: currentValue }).fail(
                    function () {
                        LS.ajaxAlerts($("#errorOnSave").val(), "alert-danger", {
                            showCloseButton: true,
                        });
                    },
                );
            }
        }
        // Remove auto-open flag so next open (manual) shows the toggle
        $(this).removeAttr("data-auto-open").removeData("auto-open");
        // Reset flag for next time
        savedViaSwitch = false;
    });
});
