'use strict';
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('update-alert').addEventListener('closed.bs.alert', function () {
        let url = this.dataset.urlNotificationState;
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && (xhr.status >= 200 && xhr.status < 300)) {
                // success, do nothing
            } else {
                // error, do nothing
            }
        };
        xhr.open('GET', url, true);
        xhr.send();
    });
});