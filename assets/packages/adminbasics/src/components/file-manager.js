const replacingIconData = [
    {
        originIcon: "a[href='kcact:upload'] span",
        newIcon: "ri-upload-fill",
    },
    {
        originIcon: "a[href='kcact:refresh'] span",
        newIcon: "ri-refresh-line",
    },
    {
        originIcon: "a[href='kcact:settings'] span",
        newIcon: "ri-settings-3-line",
    },
    {
        originIcon: "a[href='kcact:maximize'] span",
        newIcon: "ri-fullscreen-line",
    },
    {
        originIcon: "a[href='kcact:about'] span",
        newIcon: "ri-information-line",
    },
    {
        originIcon: "a[href='kcdir:/files'] span.folder",
        newIcon: "ri-folder-line",
    },
    {
        originIcon: "a[href='kcdir:/images'] span.folder",
        newIcon: "ri-folder-line",
    },
    {
        originIcon: "a[href='kcdir:/flash'] span.folder",
        newIcon: "ri-folder-line",
    },
];

const menuReplacingIconData = [
    {
        originIcon:
            "a[href='kcact:refresh'][class='ui-menu-item-wrapper'] span",
        newIcon: "ri-refresh-line",
    },
    {
        originIcon: "a[href='kcact:download'] span",
        newIcon: "ri-download-line",
    },
    {
        originIcon: "a[href='kcact:mkdir'] span",
        newIcon: "ri-folder-add-line",
    },
    {
        originIcon: "a[href='kcact:mvdir'] span",
        newIcon: "ri-eraser-line",
    },
    {
        originIcon: "a[href='kcact:rmdir'] span",
        newIcon: "ri-delete-bin-line",
    },

];

/**
 * Replace old icons in the file manager toolbar and window.
 *
 * @param iframeSource - The iframe source where icons will be replaced.
 * @param originIcon - The origin icon that were already existed in the iframe.
 * @param newIcon - The new remix icon that will replace old icon.
 * @returns 
 */

const handleReplaceIcons = ({ iframeSource, originIcon, newIcon }) => {
    const replacingElement =
        iframeSource.contentWindow.document.querySelector(originIcon);
    if (replacingElement) {
        replacingElement.insertAdjacentHTML(
            "beforebegin",
            `<i class=${newIcon}></i>`
        );
    }
};

/**
 * Register styles in the iframe header
 *
 * @param header - The iframe header where styles to load are registered
 * @param linkUrl - style links that will be loaded after iframe loaded
 * @returns 
 */
const handleAppendCssLink = ({ header, linkUrl }) => {
    const cssLink = document.createElement("link");
    cssLink.rel = "stylesheet";
    cssLink.type = "text/css";
    cssLink.href = linkUrl;

    header.appendChild(cssLink);
};

/**
 * Replace icons that is shown at first load in the iframe left menu and the icons that is shown when right click on that item
 *
 * @param header - The iframe header where styles to load are registered
 * @param linkUrl - style links that will be loaded after iframe loaded
 * @returns 
 */
const handleReplaceFolderIcons = (fileManagerIframe, menuReplacingIconData) => {
    const replacingElements =
        fileManagerIframe.contentWindow.document.querySelectorAll(
            "span[class='folder regular']"
        );

    replacingElements.forEach((element) => {
        const previousSiblingIcon =
            element.previousElementSibling?.classList.contains(
                "ri-folder-line"
            );
        const nextSiblingIcon =
            element.nextElementSibling?.classList.contains("ri-folder-line");
        if (!previousSiblingIcon && !nextSiblingIcon) {
            element.insertAdjacentHTML(
                "beforebegin",
                "<i class='ri-folder-line'></i>"
            );
        }

        element.addEventListener("contextmenu", (event) => {
            event.preventDefault(); // prevent the default context menu from appearing
            menuReplacingIconData.map((data) =>
                handleReplaceIcons({
                    ...data,
                    iframeSource: fileManagerIframe,
                })
            );
        });
    });

    // when right click of menu folder
    const folderCurrentEl =
        fileManagerIframe.contentWindow.document.getElementsByClassName(
            "folder current"
        )[0];

    if (folderCurrentEl) {
        folderCurrentEl.addEventListener("contextmenu", (event) => {
            event.preventDefault(); // prevent the default context menu from appearing
            menuReplacingIconData.map((data) =>
                handleReplaceIcons({
                    ...data,
                    iframeSource: fileManagerIframe,
                })
            );
        });
    }
};

export default function fileManagerStyle() {
    const fileManagerIframe = document.getElementById("browseiframe");
    if (fileManagerIframe) {
        fileManagerIframe.addEventListener("load", function () {
            // after iframe is loaded
            fileManagerIframe.contentWindow.document.body.classList.add(
                "file-manager-body"
            );
            // replace icons in the toolbar and main window
            replacingIconData.map((data) =>
                handleReplaceIcons({
                    ...data,
                    iframeSource: fileManagerIframe,
                })
            );

            // replace icons in the left menu folder icons, and icons that is shown when right click each of them.
            handleReplaceFolderIcons(fileManagerIframe, menuReplacingIconData);

            // Target Node that is observed of changes
            // this is used when new subfolder is created
            const targetNode =
                fileManagerIframe.contentWindow.document.getElementById(
                    "folders"
                );

            if (targetNode) {
                // Create a new MutationObserver
                const observer = new MutationObserver((mutationsList) => {
                    for (let mutation of mutationsList) {
                        if (
                            mutation.type === "childList" &&
                            mutation.addedNodes.length > 0
                        ) {
                            for (let node of mutation.addedNodes) {
                                if (
                                    node.nodeName === "DIV" &&
                                    node.classList.contains("folders")
                                ) {
                                    // if new subfolder is created, then replace icons
                                    handleReplaceFolderIcons(
                                        fileManagerIframe,
                                        menuReplacingIconData
                                    );
                                }
                            }
                        }
                    }
                });

                // Start observing the target node for mutations
                observer.observe(targetNode, {
                    childList: true,
                    subtree: true,
                });
            }

            // Load sea_green css again after iframe is fully loaded
            handleAppendCssLink({
                header: fileManagerIframe.contentWindow.document.head,
                linkUrl: "/themes/admin/Sea_Green/css/sea_green.css",
            });
            handleAppendCssLink({
                header: fileManagerIframe.contentWindow.document.head,
                linkUrl:
                    "/assets/fonts/font-src/remix/remixicon.css",
            });
        });
    }
}
