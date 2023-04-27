function action_dropdown() {
    "use strict";
    let dropdownElementList = [].slice.call(
        document.querySelectorAll(".dropdown-menu")
    );
    dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            boundary: document.querySelector("body"),
            popperConfig: function (defaultBsPopperConfig) {
                return {
                    defaultBsPopperConfig,
                    strategy: "fixed",
                };
            },
        });
    });

    // when open dropdown, z-index should be set to `ls-sticky-column` element to avoid dropdown overlapping
    // &:has(.dropdown-menu.show) is working for chrome but not in other browsers like firefox, so we add has-dropdown class and set z-index there
    $(".ls-sticky-column .ls-dropdown-toggle").on("click", function () {
        let $stickyColumn = $(this).closest(".ls-sticky-column");
        let $dropdownMenu = $stickyColumn.find(".dropdown-menu");

        if ($dropdownMenu.hasClass("show")) {
            $stickyColumn.addClass("has-dropdown");
        } else {
            $stickyColumn.removeClass("has-dropdown");
        }
    });

    // when click outside dropdown, `has-dropdown` class should be removed
    let menuParentElementList = [].slice.call(
        document.querySelectorAll(".dropdown")
    );
    menuParentElementList.map((element) => {
        element.addEventListener("hide.bs.dropdown", function () {
            let $stickyColumn = $(this).closest(".ls-sticky-column");
            $stickyColumn.removeClass("has-dropdown");
        });
    });
}
action_dropdown();