(function () {
    // Configuration & Script Info
    const script = document.currentScript;
    const surveyId = script.dataset.surveyId;
    const lang = script.dataset.lang || "en";
    const containerId = script.dataset.containerId || "1";
    const rootUrl = script.dataset.rootUrl || window.location.origin;
    const referer =
        window.location.protocol + "//" + window.location.host + "/";
    const requestUrl = `${rootUrl}/index.php/rest/v1/survey-template/${surveyId}?lang=${lang}`;
    let pageNumber = 0;

    // Prepare the container in the DOM
    document.documentElement.className = "js"; // "js" class shows that JavaScript is enabled
    const container = document.getElementById("limesurvey-container");
    container.innerHTML = `
      <div id="beginScripts"></div>
      <div id="limesurvey-${containerId}"></div>
      <div id="bottomScripts"></div>
    `;

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

    // Send a POST request to load or submit the form
    async function fetchSurveyContent(params) {
        pageNumber++;

        const response = await fetch(requestUrl, {
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
        const { template, hiddenInputs, head, beginScripts, bottomScripts } =
            JSON.parse(responseText);

        const surveyRoot = document.getElementById(`limesurvey-${containerId}`);
        surveyRoot.innerHTML = template;

        // Update form for submission via fetch
        const form = surveyRoot.querySelector("#limesurvey, #form-token");
        form.action = requestUrl;

        form.querySelectorAll("[name]").forEach((el) => {
            el.name = "LSEMBED-" + el.name;
        });

        form.innerHTML += hiddenInputs;

        // Remove elements not needed in embedded mode
        form.querySelectorAll(".clearall-saveall-wrapper").forEach((el) =>
            el.remove()
        );

        if (form.id === "form-token") {
            for (let toggle of form.querySelectorAll("#ls-toggle-token-show")) {
                const tokenItems = toggle.parentNode.parentNode.querySelectorAll("#token");
                if (tokenItems.length) {
                    let tokenItem = tokenItems[0];
                    toggle.addEventListener("click", function(evt) {
                        tokenItem.type = ((tokenItem.type === "password") ? "text" : "password");
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

    // Initial Load
    fetchSurveyContent({
        popuppreview: false,
        js: false,
        container_id: containerId,
    });
})();
