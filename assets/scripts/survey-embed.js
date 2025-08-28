if (typeof lsFormIndex === "undefined") {
    var lsFormIndex = 0;
} else {
    lsFormIndex++;
}
(function () {
    // Configuration & Script Info
    let script = document.currentScript;
    if (!script) {
        script = window.__LS_SHADOW_ROOT__.querySelector(
            "script[data-is-preview]"
        );
    }
    const surveyId = script.dataset.surveyId;
    let lang = script.dataset.lang || "en";
    const containerId = script.dataset.containerId || "1";
    const rootUrl = script.dataset.rootUrl || window.location.origin;
    const isPreview = script.dataset.isPreview === "true";
    const embedType = script.dataset.embedType || null;

    const referer =
        window.location.protocol + "//" + window.location.host + "/";
    function getRequestUrl() {
        return `${rootUrl}/index.php/rest/v1/survey-template/${surveyId}?lang=${lang}&noregister=true`;
    }
    let pageNumber = 0;

    // Prepare the container in the DOM
    if (!isPreview) {
        document.documentElement.className = "js"; // "js" class shows that JavaScript is enabled
        const container = document.getElementById("limesurvey-container");
        container.innerHTML = `
          <div id="beginScripts"></div>
          <div id="limesurvey-${containerId}"></div>
          <div id="bottomScripts"></div>
          <style>
              .nav-link.ls-link-action.ls-link-loadall {
                  display: none;
              }
          </style>
        `;
    }

    // Inject scripts and styles in order
    function injectScripts(scripts, container) {
        function fixLinks(html) {
            return html.replace(/href="\//g, `href="${rootUrl}/`);
        }

        function loadScript(scriptEl) {
            return new Promise((resolve, reject) => {
                scriptEl.onload = () => resolve();
                scriptEl.onerror = () =>
                    reject(new Error("Script load failed: " + scriptEl.src));
                container.appendChild(scriptEl);
            });
        }

        function createElementFromHTML(html) {
            const wrapper = document.createElement("div");
            wrapper.innerHTML = html.trim();
            return wrapper.firstChild;
        }

        let chain = Promise.resolve();

        scripts.forEach((html) => {
            if (!html.includes("<script")) {
                // element is <link> or <style> not a script
                chain = chain.then(() => {
                    container.innerHTML += fixLinks(html);
                });
                return;
            }

            const original = createElementFromHTML(html);
            const script = document.createElement("script");

            if (original.src) {
                // external script : load it
                let src = original.src + `?v=${pageNumber}`;
                if (src.startsWith(referer)) {
                    src = src.replace(referer, `${rootUrl}/`);
                }
                script.src = src;
                chain = chain.then(() => loadScript(script));
            } else {
                // inline script : just add it to the DOM
                script.textContent = original.textContent;
                chain = chain.then(() => container.appendChild(script));
            }
        });

        return chain;
    }

    function removeError() {
        const oldError = document.querySelector(".error");
        if (oldError) {
            oldError.remove();
        }
    }
    function error(errorText) {
        removeError()

        const div = document.createElement("div");
        div.className = "alert alert-danger list-unstyled mt-3";
        div.textContent = errorText
        const p = document.createElement("div");
        p.className = "container-fluid error col-centered col-xl-8";
        p.appendChild(div);

        document.getElementById("limesurvey-container").prepend(p);
    }

    // Send a POST request to load or submit the form
    async function fetchSurveyContent(params) {
        pageNumber++;
        if (params.lang) {
            lang = params.lang;
        }

        const response = await fetch(getRequestUrl(), {
            method: "POST",
            body: new URLSearchParams(params),
            headers: {
                Accept: "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
                "Accept-Language": "en-US,en;q=0.9",
                "Cache-Control": "max-age=0",
                Connection: "keep-alive",
                Referer: referer,
                "Sec-Fetch-Dest": "document",
                "Sec-Fetch-Mode": "navigate",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-User": "?1",
                "Upgrade-Insecure-Requests": "1",
                "User-Agent":
                    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36",
                "sec-ch-ua": '"Not)A;Brand";v="8", "Chromium";v="138"',
                "sec-ch-ua-mobile": "?0",
                "sec-ch-ua-platform": '"Linux"',
            },
        });
        const responseText = await response.text();
        const responseTextParsed = JSON.parse(responseText)

        removeError()
        if (responseTextParsed.error) {
            error(responseTextParsed.error.message);
            return;
        }

        const { template, hiddenInputs, head, beginScripts, bottomScripts } = responseTextParsed;

        const surveyRoot = document.getElementById(`limesurvey-${containerId}`);
        surveyRoot.innerHTML = template;

        // Update form for submission via fetch
        const form = surveyRoot.querySelector("#limesurvey, #form-token");
        window["lssubmit" + lsFormIndex] = function (
            l,
            ft = false,
            token = ""
        ) {
            lang = l;
            const isTokenForm = (surveyRoot.querySelector("input[name=LSEMBED-token]") !== null);
            const formData = Array.from(form.querySelectorAll("[name]"))
                .filter(
                    (el) =>
                        [
                            "LSEMBED-YII_CSRF_TOKEN",
                            "LSEMBED-LEMpostKey",
                        ].indexOf(el.name) >= 0 ||
                        el.name.indexOf("LSSESSION-") === 0
                )
                .map((el) => `${el.name}=${el.value}`)
                .join("&");
            fetchSurveyContent(
                formData +
                    "&popuppreview=false" +
                    (ft ? "&filltoken=true" : "") +
                    (token ? `&LSEMBED-token=${token}` : "")
            ).then(function() {
                if (isTokenForm) {
                    const tokenLink = surveyRoot.querySelector(".ls-link-action.token");
                    if (tokenLink) {
                        tokenLink.click();
                    }
                }
            });
        };
        form.action = getRequestUrl();

        form.querySelectorAll("[name]").forEach((el) => {
            el.name = "LSEMBED-" + el.name;
        });

        form.innerHTML += hiddenInputs;

        // Remove elements not needed in embedded mode
        form.querySelectorAll(".clearall-saveall-wrapper").forEach((el) =>
            el.remove()
        );

        for (let languageLink of surveyRoot.querySelectorAll(
            ".ls-language-link"
        )) {
            languageLink.classList.remove("ls-language-link");
            languageLink.setAttribute(
                "onclick",
                `window["lssubmit" + ${lsFormIndex}]('${languageLink.getAttribute(
                    "data-limesurvey-lang"
                )}')`
            );
        }

        let showMenu = false;
        let navbarToggler = surveyRoot.querySelector("#navbar-toggler");
        if (navbarToggler) {
            navbarToggler.addEventListener("click", function () {
                setTimeout(() => {
                    showMenu = !showMenu;
                    surveyRoot
                        .querySelector("#main-dropdown")
                        .classList[showMenu ? "add" : "remove"]("show");
                }, 1);
            });
        }

        if (form.id === "form-token") {
            for (let toggle of form.querySelectorAll("#ls-toggle-token-show")) {
                const tokenItems =
                    toggle.parentNode.parentNode.querySelectorAll("#token");
                if (tokenItems.length) {
                    let tokenItem = tokenItems[0];
                    toggle.addEventListener("click", function (evt) {
                        tokenItem.type =
                            tokenItem.type === "password" ? "text" : "password";
                        for (let child of toggle.children) {
                            child.classList.toggle("d-none");
                        }
                    });
                }
            }
        }
        // Intercept form submission and resend via fetch
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            const formData = Array.from(form.querySelectorAll("[name]"))
                .map((el) => `${el.name}=${el.value}`)
                .join("&");
            fetchSurveyContent(formData + "&popuppreview=false");
        });

        let languageSelector = surveyRoot.querySelector(
            "#language-changer-select"
        );
        if (languageSelector) {
            let languageForm =
                languageSelector.parentNode.parentNode.parentNode.parentNode;
            languageForm.addEventListener("submit", (event) => {
                event.preventDefault();
                window["lssubmit" + lsFormIndex](languageSelector.value);
            });
        }

        function register() {
            window.open(
                `${rootUrl}/index.php/${surveyId}?lang=${lang}&filltoken=true`,
                "",
                "popup"
            );
            let token = prompt("token");
            if (token) {
                window["lssubmit" + lsFormIndex](lang, true, token);
            }
        }
        let tokenRedirects = [
            ...surveyRoot.querySelectorAll(".nav-link.ls-link-action"),
        ].filter((el) => el.href.indexOf("filltoken") >= 0);
        if (tokenRedirects.length) {
            let tokenRedirect = tokenRedirects[0];
            tokenRedirect.addEventListener("click", function (evt) {
                evt.preventDefault();
                window["lssubmit" + lsFormIndex](lang, true);
            });
        }

        const headScriptsList = head.split("SEPARATOR");
        const beginScriptList = beginScripts.split("SEPARATOR");
        const bottomScriptsList = bottomScripts.split("SEPARATOR");
        const documentHead = document.head;
        const beginContainer = document.getElementById("beginScripts");
        const bottomContainer = document.getElementById("bottomScripts");

        injectScripts(headScriptsList, documentHead)
            .then(() => injectScripts(beginScriptList, beginContainer))
            .then(() => injectScripts(bottomScriptsList, bottomContainer));
    }

    function removeError() {
        const oldError = document.querySelector(".error");
        if (oldError) {
            oldError.remove();
        }
    }
    function error(errorText) {
        removeError()

        const div = document.createElement("div");
        div.className = "alert alert-danger list-unstyled mt-3";
        div.textContent = errorText
        const p = document.createElement("div");
        p.className = "container-fluid error col-centered col-xl-8";
        p.appendChild(div);

        document.getElementById("limesurvey-container").prepend(p);
    }

    // Initial Load
    fetchSurveyContent({
        popuppreview: false,
        js: false,
        container_id: containerId,
    });
    if (!isPreview) {
        // Initial Load
        fetchSurveyContent({
            popuppreview: false,
            js: false,
            container_id: containerId,
        });
    }

    function initWidget() {
        const root = window.__LS_SHADOW_ROOT__ || document;
        var container = root.getElementById("limesurvey-container");
        var button = root.getElementById("limesurvey-embed-button");
        var icon = button.querySelector(".icon");
        const side = button.dataset.side || "right";
        var isOpen = false;

        button.addEventListener("click", function () {
            if (isOpen) {
                button.classList.remove("open");
                container.classList.remove("open");
                icon.innerHTML = side === "right" ? "<" : ">";
            } else {
                button.classList.add("open");
                container.classList.add("open");
                icon.innerHTML = "x";
            }
            isOpen = !isOpen;
        });
    }

    function initPopup() {
        const root = window.__LS_SHADOW_ROOT__ || document;
        const container = root.getElementById("limesurvey-parent-container");
        const button = root.getElementById("limesurvey-embed-button");
        const closeBtn = root.getElementById("ls-popup-close");
        let isOpen = false;

        function openPopup() {
            container.classList.add("open");
            isOpen = true;
        }

        function closePopup() {
            container.classList.remove("open");
            isOpen = false;
        }

        button.addEventListener("click", function () {
            isOpen ? closePopup() : openPopup();
        });

        closeBtn.addEventListener("click", function () {
            closePopup();
        });

        const triggerOn = button.getAttribute("data-trigger-on") || "auto";
        if (triggerOn === "auto") {
            openPopup();
        }
        if (triggerOn === "scroll") {
            window.addEventListener("scroll", function onScroll() {
                const rect = button.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom >= 0) {
                    openPopup();
                    window.removeEventListener("scroll", onScroll);
                }
            });
        }
    }

    function iniInteractions() {
        if (embedType === "Widget") {
            initWidget();
        } else if (embedType === "Popup") {
            initPopup();
        }
    }

    if (isPreview) {
        iniInteractions();
    } else {
        window.onload = () => iniInteractions();
    }
})();
