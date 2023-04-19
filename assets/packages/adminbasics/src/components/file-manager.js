export default function fileManagerStyle() {
  const fileManagerIframe = document.getElementById("browseiframe");
  if (fileManagerIframe) {
      fileManagerIframe.addEventListener("load", function () {
          fileManagerIframe.contentWindow.document.body.classList.add(
              "file-manager-body"
          );

          const uploadElement =
              fileManagerIframe.contentWindow.document.querySelector(
                  "a[href='kcact:upload'] span"
              );
          if (uploadElement) {
              uploadElement.insertAdjacentHTML(
                  "beforebegin",
                  "<i class='ri-upload-fill'></i>"
              );
          }

          const refreshElement =
              fileManagerIframe.contentWindow.document.querySelector(
                  "a[href='kcact:refresh'] span"
              );
          if (refreshElement) {
              refreshElement.insertAdjacentHTML(
                  "beforebegin",
                  "<i class='ri-refresh-line'></i>"
              );
          }

          const settingsElement =
              fileManagerIframe.contentWindow.document.querySelector(
                  "a[href='kcact:settings'] span"
              );
          if (settingsElement) {
              settingsElement.insertAdjacentHTML(
                  "beforebegin",
                  "<i class='ri-settings-3-line'></i>"
              );
          }

          const maximizeElement =
              fileManagerIframe.contentWindow.document.querySelector(
                  "a[href='kcact:maximize'] span"
              );
          if (maximizeElement) {
              maximizeElement.insertAdjacentHTML(
                  "beforebegin",
                  "<i class='ri-fullscreen-line'></i>"
              );
          }

          const aboutElement =
              fileManagerIframe.contentWindow.document.querySelector(
                  "a[href='kcact:about'] span"
              );
          if (aboutElement) {
              aboutElement.insertAdjacentHTML(
                  "beforebegin",
                  "<i class='ri-information-line'></i>"
              );
          }

          const folderElement =
              fileManagerIframe.contentWindow.document.querySelector(
                  "a[href='kcdir:/files'] span.folder"
              );
          if (folderElement) {
              folderElement.insertAdjacentHTML(
                  "beforebegin",
                  "<i class='ri-folder-line'></i>"
              );
          }

          // Load sea_green css again after iframe is fully loaded
          const head = fileManagerIframe.contentWindow.document.head;
          const seaGreenCss = document.createElement("link");
          seaGreenCss.rel = "stylesheet";
          seaGreenCss.type = "text/css";
          seaGreenCss.href = "/themes/admin/Sea_Green/css/sea_green.css";

          const remixIconCss = document.createElement("link");
          remixIconCss.rel = "stylesheet";
          remixIconCss.type = "text/css";
          remixIconCss.href =
              "http://ls-ce/assets/fonts/font-src/remix/remixicon.css";

          head.appendChild(seaGreenCss);
          head.appendChild(remixIconCss);
      });
  }
}