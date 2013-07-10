/**
 * @fileOverview システム設定を扱うモジュールです。
 *
 * @author 株式会社バイオス　山下　哲生<yamashita@bios-net.co.jp>
 * @version 1.0.0
 */

var config = [
    {'type' : 'email', 'name' : 'from'},
    {'type' : 'text', 'name' : 'to'},
    {'type' : 'text', 'name' : 'smtp'},
    {'type' : 'text', 'name' : 'port'},
    {'type' : 'text', 'name' : 'user'},
    {'type' : 'password', 'name' : 'passwd'},
    {'type' : 'text', 'name' : 'cordinator'},
    {'type' : 'text', 'name' : 'irReceive'},
    {'type' : 'select', 'name' : 'weather'},
    {'type' : 'select', 'name' : 'xbee'},
];

$(function() {
    // システム設定を取得
    var systemConfig = getSystemConfig();

    // システム設定が未設定及びシステム設定が存在しない場合は画面に表示しない
    if (systemConfig == null || systemConfig['from'] == undefined) {
        return;
    }

    // 温湿度センサー設定を取得
    var tempLoggerConfig = getTempLoggerConfig();
    if (tempLoggerConfig == null) {
        return;
    }

    // システム設定を表示
    showSystemTable(systemConfig, tempLoggerConfig);
    
    // システム設定を登録
    $('#save').bind('click', function() {
        saveConfig('SystemConfig', '../php/requestReceptionist.php', getSystemTable(), location.reload);
    });
    
    // システム設定を削除
    $('.del').bind('click', function() {
        deleteConfig('SystemConfig', '../php/requestReceptionist.php', getSystemTable(), config[parseInt($(this).children().next().val(), 10)]['name'], location.reload);
    });
});

/**
 * システム設定を画面に表示します。
 * @param systemConfig システム設定
 */
function showSystemTable(systemConfig, tempLoggerConfig) {
    var count = $('#systemTable').find('tr').size() - 2; // 温湿度センサー、XBee閾値の分を除去
    for (var i = 0; i < count; i++) {
        $('#systemTable').find('tr').eq(i).find('td').children("input[type='" + config[i]['type'] + "']").val(systemConfig[config[i]['name']]);
    }

    // 気象情報としてグラフに表示する温湿度センサーの番号
    var tempLoggerNum = tempLoggerConfig.length;
    for (var i = 0; i < tempLoggerNum; i++) {
        if (systemConfig['weather'] == tempLoggerConfig[i].sensorId) {
            $('#systemTable').find('tr').eq(8).find('td').eq(0).find('select').append("<option selected value='" + tempLoggerConfig[i].sensorId + "'>" + tempLoggerConfig[i].sensorName + "</option>");
        } else {
            $('#systemTable').find('tr').eq(8).find('td').eq(0).find('select').append("<option value='" + tempLoggerConfig[i].sensorId + "'>" + tempLoggerConfig[i].sensorName + "</option>");
        }
    }

    // XBee閾値を選択
    var options = $('#systemTable').find('tr').eq(9).find('td').eq(0).find('select').find('option');
    var optionsNum = options.size();
    for (var i = 0; i < optionsNum; i++) {
        var option = $(options).eq(i);
        if (systemConfig['xbee'] === $(option).val()) $(option).attr('selected', 'selected');
    }
}

/**
 * システム情報を画面から配列で取得します。
 * @return システム設定
 */
function getSystemTable() {
    var systemConfig = {};
    var configNum = $('#systemTable').find('tr').size();
    var getKey = function(index) {return config[index]['name'];};
    for (var i = 0; i < configNum; i++) {
        systemConfig[config[i]['name']] = $('#systemTable').find('tr').eq(i).find('td').children().val();
    }
    return systemConfig;
}

/**
 * 非同期通信でシステム設定を取得します。
 * @return Array システム設定[
 *              'from' : メール送信元 ,
 *              'to' : メール送信先,
 *              'smtp' : SMTPサーバホスト名,
 *              'port' : SMTPサーバポート番号,
 *              'user' : 送信元ユーザ名,
 *              'passwd' : 送信元ユーザパスワード,
 *              'cordinator' : コーディネータCOMポート番号,
 *              'irReceive' : 赤外線受信機COMポート番号,
 *              'weather' : 気象情報として温湿度グラフに描画する温湿度センサーの番号,
 *              'xbee' : XBee供給電圧閾値
 *             ]
 */
function getSystemConfig() {
    var systemConfig = [];
	  $.ajax({
        type: 'POST',
        url: '../php/requestReceptionist.php',
        dataType: 'json',
        async: false,
        data: {'target' : 'SystemConfig'},
    }).done(function(json) {
        if (json == null || json.from == null) {
          return systemConfig;
        }
        systemConfig['from'] = json.from;
        systemConfig['to'] = json.to;
        systemConfig['smtp'] = json.smtp;
        systemConfig['port'] = json.port;
        systemConfig['user'] = json.user;
        systemConfig['passwd'] = json.passwd;
        systemConfig['cordinator'] = json.cordinator;
        systemConfig['irReceive'] = json.irReceive;
        systemConfig['weather'] = json.weather;
        systemConfig['xbee'] = json.xbee;
    }).fail(function (data) {
        alert('システム設定の取得に失敗しました。');
        return null;
    });
    return systemConfig;
}