/**
 * Installed question themes grid functionality
 */

$(document).on("change", "#questionthemes-pageSize", function () {
    $.fn.yiiGridView.update("questionthemes-grid", {
        data: {
            pageSize: $(this).val()
        }
    });
});

// Use event delegation for toggle question themes so it works across pagination
$(document).on("change", ".toggle_question_theme", function () {
    let $url = $(this).attr("data-url");
    let data = new FormData();
    let xhttp = new XMLHttpRequest();
    data.append(LS.data.csrfTokenName, LS.data.csrfToken);
    xhttp.open("POST", $url, true);
    xhttp.send(data);
});

// Hook into yiiGridView after updates
$("#questionthemes-grid").on("yiiGridView:afterUpdate", function() {
    if (window.LS && LS.floatingActions && typeof LS.floatingActions.updateBar === "function") {
        setTimeout(function() {
            LS.floatingActions.updateBar("questionthemes-grid", "questionId");
        }, 100);
    }
});
