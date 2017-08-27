/**
 * @Author 张冬生
 * 用于 web 点菜
 */

var wbtWebAPI = window.wbtWebAPI || (function (document, $) {

    var _serviceURL = 'http://' + window.location.host,// + '../web/web/',
        that = {};

    that.setServiceURL = function (url) {
        _serviceURL = url;
    };

    that.call = function (service, resourceID, params, success) {

        if (arguments.length == 2) {
            var type = typeof resourceID;
            if (type == 'function') {
                success = resourceID;
                resourceID = null;
            }
            else if (type == 'object') {
                params = resourceID;
                resourceID = null;
            }
        }
        else if (arguments.length == 3) {
            if (typeof params == 'function') {
                success = params;
                params = null;
            }
            if (typeof resourceID == 'object') {
                params = resourceID;
                resourceID = null;
            }
        }

        $.ajax({
            type: 'POST',
            url: _serviceURL + (resourceID ? (service + '/' + resourceID) : service),
            data: params,
            datatype: 'JSON',
            async: false,
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                showError(errorThrown);
            },
            success: function (data) {
                if (data.errno == 0) {
                    success(data);
                } else if (data.error) {
                    showError(data.error);
                }
            }
        });
    };

    return that;

}(document, window.jQuery));

window.wbtWebAPI = wbtWebAPI;

var $pageName;
function showError(message) {
    if (!message) message = '出错啦。。。';

    var errE = get('page_name');
    $pageName = errE.innerHTML;
    errE.innerHTML = message;

    errE.setAttribute('class', 'page_name error');

    setTimeout('revert()', 3000);
}

function revert() {
    var $errE = get('page_name');
    $errE.innerHTML = $pageName;
    $errE.setAttribute('class', 'page_name');

}