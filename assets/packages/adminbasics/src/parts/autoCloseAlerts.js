/**
 * Functionality to auto close alerts after some time
 */

const autoClose = function () {
    const
        autoCloseAlert = (container, timeout) => {
            if (timeout === undefined) {
                    timeout = 3000;
                }
                if (container.length && timeout > 0) {
                    var timeoutRef = setTimeout(function () {
                        container.alert('close');
                    }, timeout);
                    container.on('closed.bs.alert', function () {
                        clearTimeout(timeoutRef);
                    });
                }
            };

    return {
        autoCloseAlert,
    }
}

//########################################################################

const autoCloseAlerts = new autoClose();

export default autoCloseAlerts;
