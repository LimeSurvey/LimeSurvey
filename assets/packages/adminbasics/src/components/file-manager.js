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

    // folder regular
];

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

const handleAppendCssLink = ({ header, linkUrl }) => {
    const cssLink = document.createElement("link");
    cssLink.rel = "stylesheet";
    cssLink.type = "text/css";
    cssLink.href = linkUrl;

    header.appendChild(cssLink);
};

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
            fileManagerIframe.contentWindow.document.body.classList.add(
                "file-manager-body"
            );
            replacingIconData.map((data) =>
                handleReplaceIcons({
                    ...data,
                    iframeSource: fileManagerIframe,
                })
            );

            handleReplaceFolderIcons(fileManagerIframe, menuReplacingIconData);

            // Select the element to observe
            const targetNode =
                fileManagerIframe.contentWindow.document.getElementById(
                    "folders"
                );

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
                                // Do something if a matching node was added
                                console.log(
                                    'New span element with class "folder regular" added:',
                                    node
                                );
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
            observer.observe(targetNode, { childList: true, subtree: true });

            // Load sea_green css again after iframe is fully loaded
            handleAppendCssLink({
                header: fileManagerIframe.contentWindow.document.head,
                linkUrl: "/themes/admin/Sea_Green/css/sea_green.css",
            });
            handleAppendCssLink({
                header: fileManagerIframe.contentWindow.document.head,
                linkUrl:
                    "http://ls-ce/assets/fonts/font-src/remix/remixicon.css",
            });
        });
    }
}
