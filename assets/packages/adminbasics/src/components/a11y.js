document.addEventListener("DOMContentLoaded", function () {
    $.fn.modal.Constructor.Default.keyboard = false;
    // setTimeout(function () {
    //     $(".select2-selection--single").each(function () {
    //         let labelId = $(this)
    //             .closest("div")
    //             .find(".select2-hidden-accessible")
    //             .attr("id");
    //         $('label[for="' + labelId + '"]').attr("id", "label" + labelId);
    //         $(this).attr(
    //             "aria-labelledby",
    //             "label" + labelId + " " + $(this).attr("aria-labelledby")
    //         );
    //     });
    // }, 1000);

    $("div.modal.fade").each(function () {
        $(this).attr("aria-label", $(this).find(".modal-title").text());
    });

    $("ul.nav.nav-tabs").each(function () {
        $(this).attr("role", "tablist");
        $(this).find("> li").attr("role", "none");
        $(this).find("> li > a").attr("role", "tab");
        $(this)
            .find("> li > a.active")
            .attr({ tabindex: "0", "aria-selected": "true" });
        $(this)
            .find("> li > a:not(.active)")
            .attr({ tabindex: "-1", "aria-selected": "false" });
    });

    $(document).on("click", "ul.nav.nav-tabs > li > a", function (e) {
        $(this)
            .closest("ul")
            .find("li > a")
            .attr({ tabindex: "-1", "aria-selected": "false" });
        $(this).attr({ tabindex: "0", "aria-selected": "true" });
    });
    $(document).on("keydown", "ul.nav.nav-tabs > li > a", function (e) {
        if (e.key === "ArrowLeft") {
            $(this).closest("li").prev().find("a").focus();
        } else if (e.key === "ArrowRight") {
            $(this).closest("li").next().find("a").focus();
        } else if (e.key === "Enter") {
            $(this).click();
        }
    });

    setTimeout(() => {
        $("ul.nav.nav-tabs> li > a").removeAttr("aria-controls");
    }, 2000);

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            event.preventDefault();
            document.body.classList.add("disable-pointer-events");
        }
    });
    document.addEventListener("mousemove", function (event) {
        document.body.classList.remove("disable-pointer-events");
    });

    $('[data-bs-toggle="tooltip"]').tooltip();

    $('[data-bs-toggle="tooltip"]').on("shown.bs.tooltip", function () {
        $(document).on("keydown.dismiss.tooltip", function (e) {
            if (e.which === 27) {
                // 27 is the key code for the Escape key
                $.fn.modal.Constructor.Default.keyboard = false;
                $('[data-bs-toggle="tooltip"]').tooltip("hide");
                $(document).off("keydown.dismiss.tooltip");
            }
        });
    });

    $('[data-bs-toggle="tooltip"]').on("hidden.bs.tooltip", function () {
        $.fn.modal.Constructor.Default.keyboard = false;
        $(document).off("keydown.dismiss.tooltip");
    });

    console.log("a11y loaded");
});

document.addEventListener("shown.bs.tab", function (event) {
    $("ul.nav.nav-tabs").each(function () {
        $(this).attr("role", "tablist");
        $(this).find("> li").attr("role", "none");
        $(this).find("> li > a").attr("role", "tab");
        $(this)
            .find("> li > a.active")
            .attr({ tabindex: "0", "aria-selected": "true" });
        $(this)
            .find("> li > a:not(.active)")
            .attr({ tabindex: "-1", "aria-selected": "false" });
    });
    console.log(event.target); // newly activated tab
    console.log(event.relatedTarget); // previous active tab
});

var lastFocus;
document.addEventListener("show.bs.modal", function (e) {
    lastFocus = $(":focus");
});
document.addEventListener("hidden.bs.modal", function (e) {
    if (lastFocus) lastFocus.focus();
});

document.addEventListener("shown.bs.modal", function (event) {
    const modal = event.target;
    setTimeout(function () {
        $(modal).attr(
            "aria-label",
            $(modal).find(".modal-title").text().trim()
        );
    }, 1000);
    if ($(modal).find("#a11y-modal-end").length == 0) {
        $(
            '<div id="a11y-modal-end" class="sr-only" tabindex="0">Modal End</div>'
        ).appendTo(modal);
    }
    $("#a11y-modal-end").off("keydown");
    $("#a11y-modal-end").on("keydown", function (e) {
        if (e.key === "Tab") {
            if (!e.shiftKey) {
                modal.focus();
            }
        }
    });
    modal.focus();
});

document.addEventListener("keydown", function (event) {
    document.body.classList.remove("disable-pointer-events");
    if ($(this).closest(".custom-actions").length > 0) {
        console.log("dropdown");
    }
    console.log(
        "event.target.classList.contains(a11y-enter)",
        event.target.classList.contains("a11y-enter")
    );
    if (
        (event.key == "Enter" || event.key == " ") &&
        event.target.classList.contains("a11y-enter")
    ) {
        event.preventDefault();
        if ($(event.target).find(".a11y-enter-trigger")) {
            $(event.target).find(".a11y-enter-trigger")?.click();
        } else {
            $(event.target).click();
        }
    }
});

// Function to execute when a select2 is initiated
function onSelect2Initiated(node) {
    const ID = $(node)
        .closest(".select2.select2-container")
        .prev("select")
        .attr("id");
    const LABEL = $("#" + ID)
        .parent()
        .parent()
        .find("label.form-label");
    if ($('[for="' + ID + '"]').length <= 0) {
        if (LABEL.length > 0) {
            LABEL.attr({ id: "label" + ID, for: ID });
        }
    }
    $('[for="' + ID + '"]').attr("id", "label" + ID);
    $(node)
        .find("textarea")
        .attr(
            "aria-label",
            $('[for="' + ID + '"')
                .text()
                .trim()
        );
}

function onSelect2Initiated2(node) {
    const ID = $(node)
        .closest(".select2.select2-container")
        .prev("select")
        .attr("id");

    const LABEL = $("#" + ID)
        .parent()
        .parent()
        .find("label.form-label");
    if ($('[for="' + ID + '"]').length <= 0) {
        if (LABEL.length > 0) {
            LABEL.attr({ id: "label" + ID, for: ID });
        }
    }

    $('[for="' + ID + '"]').attr("id", "label" + ID);
    $(node).attr(
        "aria-labelledby",
        $('[for="' + ID + '"]').attr("id") + " select2-gsid-container"
    );
}

// Observer callback to detect select2 initialization
function mutationObserverCallback(mutationsList) {
    for (let mutation of mutationsList) {
        if (mutation.type === "childList") {
            mutation.addedNodes.forEach((node) => {
                if (
                    node.nodeType === Node.ELEMENT_NODE &&
                    $(node).hasClass("select2-selection--multiple")
                ) {
                    onSelect2Initiated(node);
                }
                if (
                    node.nodeType === Node.ELEMENT_NODE &&
                    $(node).hasClass("select2-selection--single")
                ) {
                    onSelect2Initiated2(node);
                }
            });
        }
    }
}

// Create an observer instance linked to the callback function
const observer = new MutationObserver(mutationObserverCallback);

// Start observing the document body for added child nodes
observer.observe(document.body, { childList: true, subtree: true });

// Optional: Disconnect observer when you're done observing (e.g., on page unload)
// observer.disconnect();
