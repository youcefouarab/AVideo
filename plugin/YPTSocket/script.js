var socketConnectRequested = 0;
var totalDevicesOnline = 0;
var yptSocketResponse;

var socketResourceId;
var socketConnectTimeout;
var users_id_online = [];

var socketConnectRetryTimeout = 15000;
function socketConnect() {
    if (socketConnectRequested) {
        //console.log('socketConnect: already requested');
        return false;
    }
    clearTimeout(socketConnectTimeout);
    if (!isOnline()) {
        //console.log('socketConnect: Not Online');
        socketConnectRequested = 0;
        socketConnectTimeout = setTimeout(function () {
            socketConnect();
        }, 1000);
        return false;
    }

    socketConnectRequested = 1;
    var url = addGetParam(webSocketURL, 'page_title', $('<textarea />').html($(document).find("title").text()).text());
    ////console.log('Trying to reconnect on socket... ');
    if (!isValidURL(url)) {
        socketConnectRequested = 0;
        //console.log("socketConnect: Invalid URL ", url);
        socketConnectTimeout = setTimeout(function () {
            socketConnect();
        }, 30000);
        return false;
    }
    conn = new WebSocket(url);
    setSocketIconStatus('loading');
    try {
        conn.onopen = function (e) {
            socketConnectRequested = 0;
            socketConnectRetryTimeout = 2000;
            clearTimeout(socketConnectTimeout);
            console.log("socketConnect: Socket onopen");
            onSocketOpen();
            return false;
        };
    } catch (e) {
        console.log("socketConnect: Error onopen", e);
    }
    conn.onmessage = function (e) {
        var json = JSON.parse(e.data);
        consolelog("Socket onmessage conn.onmessage", json);
        socketResourceId = json.resourceId;
        yptSocketResponse = json;
        parseSocketResponse();
        if (json.type == webSocketTypes.ON_VIDEO_MSG) {
            console.log("Socket onmessage ON_VIDEO_MSG", json);
            $('.videoUsersOnline, .videoUsersOnline_' + json.videos_id).text(json.total);
        }
        if (json.type == webSocketTypes.ON_LIVE_MSG && typeof json.is_live !== 'undefined') {
            console.log("Socket onmessage ON_LIVE_MSG", json);
            var selector = '#liveViewStatusID_' + json.live_key.key + '_' + json.live_key.live_servers_id;
            if (json.is_live) {
                onlineLabelOnline(selector);
            } else {
                onlineLabelOffline(selector);
            }
        }
        if (json.type == webSocketTypes.NEW_CONNECTION) {
            console.log("Socket onmessage NEW_CONNECTION", json);
            if (typeof onUserSocketConnect === 'function') {
                onUserSocketConnect(json);
            }
        } else if (json.type == webSocketTypes.NEW_DISCONNECTION) {
            console.log("Socket onmessage NEW_DISCONNECTION", json);
            if (typeof onUserSocketDisconnect === 'function') {
                onUserSocketDisconnect(json);
            }
        } else {
            var myfunc;
            if (json.callback) {
                //console.log("Socket onmessage json.callback ", json.resourceId, json.callback);
                var code = "if(typeof " + json.callback + " == 'function'){myfunc = " + json.callback + ";}else{myfunc = defaultCallback;}";
                ////console.log(code);
                eval(code);
            } else {
                //console.log("onmessage: callback not found", json);
                myfunc = defaultCallback;
            }
            myfunc(json.msg);
        }
    };
    conn.onclose = function (e) {
        socketConnectRequested = 0;
        console.log('Socket is closed. Reconnect will be attempted in ' + socketConnectRetryTimeout + ' seconds.', e.reason);
        socketConnectTimeout = setTimeout(function () {
            socketConnect();
        }, socketConnectRetryTimeout);
        onSocketClose();
    };
    conn.onerror = function (err) {
        socketConnectRequested = 0;
        console.error('Socket encountered error: ', err, 'Closing socket');
        conn.close();
    };
}

function onSocketOpen() {
    setSocketIconStatus('connected');
}

function onSocketClose() {
    setSocketIconStatus('disconnected');
}

function setSocketIconStatus(status) {
    var selector = '.socket_info';
    if (status == 'connected') {
        $(selector).removeClass('socket_loading');
        $(selector).removeClass('socket_disconnected');
        $(selector).addClass('socket_connected');
    } else if (status == 'disconnected') {
        $(selector).removeClass('socket_loading');
        $(selector).addClass('socket_disconnected');
        $(selector).removeClass('socket_connected');
    } else {
        $(selector).addClass('socket_loading');
        $(selector).removeClass('socket_disconnected');
        $(selector).removeClass('socket_connected');
    }
}

function sendSocketMessageToAll(msg, callback) {
    sendSocketMessageToUser(msg, callback, "");
}

function sendSocketMessageToNone(msg, callback) {
    sendSocketMessageToUser(msg, callback, -1);
}

function sendSocketMessageToUser(msg, callback, to_users_id) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({ msg: msg, webSocketToken: webSocketToken, callback: callback, to_users_id: to_users_id }));
    } else {
        //console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessageToUser(msg, to_users_id, callback);
        }, 1000);
    }
}

function sendSocketMessageToUser(msg, callback, to_users_id) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({ msg: msg, webSocketToken: webSocketToken, callback: callback, to_users_id: to_users_id }));
    } else {
        //console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessageToUser(msg, to_users_id, callback);
        }, 1000);
    }
}

function sendSocketMessageToResourceId(msg, callback, resourceId) {
    if (conn.readyState === 1) {
        conn.send(JSON.stringify({ msg: msg, webSocketToken: webSocketToken, callback: callback, resourceId: resourceId }));
    } else {
        //console.log('Socket not ready send message in 1 second');
        setTimeout(function () {
            sendSocketMessageToUser(msg, to_users_id, callback);
        }, 1000);
    }
}

function isSocketActive() {
    return isOnline() && typeof conn != 'undefined' && conn.readyState === 1;
}

function defaultCallback(json) {
    ////console.log('defaultCallback', json);
}

var socketAutoUpdateOnHTMLTimout;
var globalAutoUpdateOnHTML = [];
function socketAutoUpdateOnHTML(autoUpdateOnHTML) {
    globalAutoUpdateOnHTML = [];
    for (var prop in autoUpdateOnHTML) {
        if (autoUpdateOnHTML[prop] === false) {
            continue;
        }
        if (typeof autoUpdateOnHTML[prop] !== 'string' && typeof autoUpdateOnHTML[prop] !== 'number') {
            continue;
        }
        ////console.log('socketAutoUpdateOnHTML 1', prop, globalAutoUpdateOnHTML[prop], autoUpdateOnHTML[prop]);
        globalAutoUpdateOnHTML[prop] = autoUpdateOnHTML[prop];
    }

    ////console.log('socketAutoUpdateOnHTML 1', autoUpdateOnHTML, globalAutoUpdateOnHTML);
}


async function AutoUpdateOnHTMLTimer() {
    var localAutoUpdateOnHTML = [];
    clearTimeout(socketAutoUpdateOnHTMLTimout);
    ////console.log('AutoUpdateOnHTMLTimer 1', empty(globalAutoUpdateOnHTML), globalAutoUpdateOnHTML);
    if (!empty(globalAutoUpdateOnHTML)) {
        $('.total_on').text(0);
        $('.total_on').parent().removeClass('text-success');
        ////console.log("AutoUpdateOnHTMLTimer 2", $('.total_on'), globalAutoUpdateOnHTML);

        localAutoUpdateOnHTML = globalAutoUpdateOnHTML;
        globalAutoUpdateOnHTML = [];
        //console.log('AutoUpdateOnHTMLTimer localAutoUpdateOnHTML 1', globalAutoUpdateOnHTML, localAutoUpdateOnHTML);
        for (var prop in localAutoUpdateOnHTML) {
            if (localAutoUpdateOnHTML[prop] === false) {
                continue;
            }
            var val = localAutoUpdateOnHTML[prop];
            if (typeof val == 'string' || typeof val == 'number') {
                ////console.log('AutoUpdateOnHTMLTimer 3', prop, val, $('.' + prop).text());
                $('.' + prop).text(val);
                //console.log('AutoUpdateOnHTMLTimer 4', prop, val, $('.' + prop).text());
                if (parseInt(val) > 0) {
                    $('.' + prop).parent().addClass('text-success');
                }
            }
        }
    } else {
        globalAutoUpdateOnHTML = [];
    }
    localAutoUpdateOnHTML = [];

    socketAutoUpdateOnHTMLTimout = setTimeout(function () {
        AutoUpdateOnHTMLTimer();
    }, 2000);
}


function parseSocketResponse() {
    json = yptSocketResponse;
    yptSocketResponse = false;
    if (typeof json === 'undefined' || json === false) {
        return false;
    }
    console.log("parseSocketResponse", json);
    //console.trace();
    if (json.isAdmin && webSocketServerVersion > json.webSocketServerVersion) {
        if (typeof avideoToastWarning == 'function') {
            avideoToastWarning("Please restart your socket server. You are running (v" + json.webSocketServerVersion + ") and your client is expecting (v" + webSocketServerVersion + ")");
        }
    }
    if (json && typeof json.users_id_online !== 'undefined') {
        users_id_online = json.users_id_online;
    }
    if (json && typeof json.autoUpdateOnHTML !== 'undefined') {
        socketAutoUpdateOnHTML(json.autoUpdateOnHTML);
    }

    if (json && typeof json.msg.autoEvalCodeOnHTML !== 'undefined') {
        ////console.log("autoEvalCodeOnHTML", json.msg.autoEvalCodeOnHTML);
        eval(json.msg.autoEvalCodeOnHTML);
    }

    $('#socketUsersURI').empty();
    if (json && $('#socket_info_container').length) {
        if (typeof json.users_uri !== 'undefined') {
            for (var prop in json.users_uri) {
                if (json.users_uri[prop] === false) {
                    continue;
                }
                for (var prop2 in json.users_uri[prop]) {
                    if (json.users_uri[prop][prop2] === false || typeof json.users_uri[prop][prop2] !== 'object') {
                        continue;
                    }
                    for (var prop3 in json.users_uri[prop][prop2]) {
                        if (json.users_uri[prop][prop2][prop3] === false || typeof json.users_uri[prop][prop2][prop3] !== 'object') {
                            continue;
                        }

                        var socketUserDivID = 'socketUser' + json.users_uri[prop][prop2][prop3].users_id;

                        if (!$('#' + socketUserDivID).length) {
                            var html = '<div class="socketUserDiv" id="' + socketUserDivID + '" >';
                            html += '<div class="socketUserName" onclick="socketUserNameToggle(\'#' + socketUserDivID + '\');">';
                            html += '<i class="fas fa-caret-down"></i><i class="fas fa-caret-up"></i>';
                            if (json.users_uri[prop][prop2].length < 50) {
                                // html += '<img src="' + webSiteRootURL + 'user/' + json.users_uri[prop][prop2][prop3].users_id + '/foto.png" class="img img-circle img-responsive">';
                            }
                            html += json.users_uri[prop][prop2][prop3].user_name + '</div>';
                            html += '<div class="socketUserPages"></div></div>';
                            $('#socketUsersURI').append(html);
                        }

                        var text = '';
                        if (json.ResourceID == json.users_uri[prop][prop2][prop3].resourceId) {
                            text += '<stcong>(YOU)</strong>';
                        }
                        ////console.log(json.users_uri[prop][prop2][prop3], json.users_uri[prop][prop2][prop3].client);
                        text = ' ' + json.users_uri[prop][prop2][prop3].page_title;
                        text += '<br><small>(' + json.users_uri[prop][prop2][prop3].client.browser + ' - ' + json.users_uri[prop][prop2][prop3].client.os + ') '
                            + json.users_uri[prop][prop2][prop3].ip + '</small>';
                        if (json.users_uri[prop][prop2][prop3].location) {
                            text += '<br><i class="flagstrap-icon flagstrap-' + json.users_uri[prop][prop2][prop3].location.country_code + '" style="margin-right: 10px;"></i>';
                            text += ' ' + json.users_uri[prop][prop2][prop3].location.country_name;
                        }
                        html = '<a href="' + json.users_uri[prop][prop2][prop3].selfURI + '" target="_blank" class="btn btn-xs btn-default btn-block"><i class="far fa-compass"></i> ' + text + '</a>';
                        $('#' + socketUserDivID + ' .socketUserPages').append(html);
                        var isVisible = Cookies.get('#' + socketUserDivID);
                        if (isVisible && isVisible !== 'false') {
                            $('#' + socketUserDivID).addClass('visible')
                        }
                    }
                }


            }
        }
        if (typeof json.users_id_online !== 'undefined') {
            for (const key in json.users_id_online) {
                if (Object.hasOwnProperty.call(json.users_id_online, key)) {
                    const element = json.users_id_online[key];

                    var socketUserDivID = 'socketUser' + element.users_id;
                    if (!$('#' + socketUserDivID).length) {
                        var html = '<div class="socketUserDiv" id="' + socketUserDivID + '" >';
                        html += '<div class="socketUserName" onclick="socketUserNameToggle(\'#' + socketUserDivID + '\');">';
                        html += '<i class="fas fa-caret-down"></i><i class="fas fa-caret-up"></i>';
                        // html += '<img src="' + webSiteRootURL + 'user/' + element.users_id + '/foto.png" class="img img-circle img-responsive">';
                        html += element.identification + '</div>';
                        html += '<div class="socketUserPages"></div></div>';
                        $('#socketUsersURI').append(html);
                    }

                    var text = '';
                    if (json.ResourceID == element.resourceId) {
                        text += '<stcong>(YOU)</strong>';
                    }
                    text = ' ' + element.page_title;
                    html = '<a href="' + element.selfURI + '" target="_blank" class="btn btn-xs btn-default btn-block"><i class="far fa-compass"></i> ' + text + '</a>';
                    $('#' + socketUserDivID + ' .socketUserPages').append(html);
                    var isVisible = Cookies.get('#' + socketUserDivID);
                    if (isVisible && isVisible !== 'false') {
                        $('#' + socketUserDivID).addClass('visible')
                    }
                }
            }
        }
    }


}


function socketNewConnection(json) {
    setUserOnlineStatus(json.msg.users_id);
}

function socketDisconnection(json) {
    setUserOnlineStatus(json.msg.users_id);
}

function setInitialOnlineStatus() {
    if (!isReadyToCheckIfIsOnline()) {
        setTimeout(function () {
            setInitialOnlineStatus();
        }, 1000);
        return false;
    }

    for (var users_id in users_id_online) {
        setUserOnlineStatus(users_id);
    }
    return true;
}

function setUserOnlineStatus(users_id) {
    if (isUserOnline(users_id)) {
        $('.users_id_' + users_id).removeClass('offline');
        $('.users_id_' + users_id).addClass('online');
    } else {
        $('.users_id_' + users_id).removeClass('online');
        $('.users_id_' + users_id).addClass('offline');
    }
}
var getWebSocket;
$(function () {
    startSocket();
    AutoUpdateOnHTMLTimer();
});
var _startSocketTimeout;
async function startSocket() {
    clearTimeout(_startSocketTimeout);
    if (!isOnline() || typeof webSiteRootURL == 'undefined') {
        //console.log('startSocket: Not Online');
        _startSocketTimeout = setTimeout(function () {
            startSocket();
        }, 10000);
        return false;
    }
    ////console.log('Getting webSocketToken ...');
    getWebSocket = webSiteRootURL + 'plugin/YPTSocket/getWebSocket.json.php';
    getWebSocket = addGetParam(getWebSocket, 'webSocketSelfURI', webSocketSelfURI);
    getWebSocket = addGetParam(getWebSocket, 'webSocketVideos_id', webSocketVideos_id);
    getWebSocket = addGetParam(getWebSocket, 'webSocketLiveKey', webSocketLiveKey);
    $.ajax({
        url: getWebSocket,
        success: function (response) {
            if (response.error) {
                //console.log('Getting webSocketToken ERROR ' + response.msg);
                if (typeof avideoToastError == 'function') {
                    avideoToastError(response.msg);
                }
            } else {
                ////console.log('Getting webSocketToken SUCCESS ', response);
                webSocketToken = response.webSocketToken;
                webSocketURL = response.webSocketURL;
                socketConnect();
            }
        }
    });
    if (inIframe()) {
        $('#socket_info_container').hide();
    }
    setInitialOnlineStatus();
}