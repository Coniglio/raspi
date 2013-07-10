/**
 * @fileOverview 温湿度センサー設定扱うモジュールです。
 *
 * @author 株式会社バイオス　山下　哲生<yamashita@bios-net.co.jp>
 * @version 1.0.0
 */

$(function() {
    setSensor();
    appendRow($('#sensorTable tbody').children().length, '');
	
    // 温湿度センサー設定を登録
	$('#save').bind('click', function() {
        saveConfig('TempLoggerConfig', '../php/requestReceptionist.php', getSensorTable(), location.reload);
    });
	
    // 温湿度センサー設定を削除
	$('.deleteBtn').live('click', function() {
        deleteConfig('TempLoggerConfig', '../php/requestReceptionist.php', getSensorTable(), $(this).children().next().val(), location.reload);
    });
	 
     // 入力行を1行増やす
	 $("input[type='text']:last").live('click', function() {
		appendRow($('#sensorTable tbody').children().length, '');
	 });
});

/**
 * 温湿度センサー設定を画面にセットします。
 */
function setSensor() {
    sensor = getTempLoggerConfig();
    if (sensor == null || sensor[0] == undefined) {
        return;
    }
    var sensorNum = sensor.length;
    var prevSensorId = 0; // 温湿度センサー表示位置調整に使う。
    for (var i = 0; i < sensorNum; i++) {
        var sensorId = parseInt(sensor[i]['sensorId'], 10);
        if ((i + 1) === sensorId) {
            appendRow(sensor[i]['sensorId'], sensor[i]['sensorName']);
        } else {
            // 温湿度センサー番号が順に存在しない場合は、空の入力欄を追加
            var sensorDiff = sensorId - prevSensorId - 1; // 現在のセンサーはループ外で追加するため-1する
            for (var j = 0; j < sensorDiff; j++) {
                appendRow($('#sensorTable tbody').children().length, '');
            }
            appendRow(sensor[i]['sensorId'], sensor[i]['sensorName']);
        }
        prevSensorId = parseInt(sensor[i]['sensorId'], 10);
    }
}

/**
 * 入力した温湿度センサー設定を配列で取得します。
 * @return Array 温湿度センサー設定
 */
function getSensorTable() {
    var sensorArray = new Array();
    $.each($('tr:not(:eq(0)):not(:last)'), function() {
        var sensorName = $(this).find('td').eq(1).find('input').val();
        if (sensorName != '') {
            sensorArray.push({'sensorId' : $(this).find('td').eq(0).text(), 'sensorName' : sensorName});
        }
    });
    return sensorArray;
}

/**
 * 入力行を1行増やします。
 * @param sensorId 温湿度センサー番号
 * @param sensorName 温湿度センサー名
 */
function appendRow(sensorId, sensorName) {
	$('tbody').append('<tr><td>' + sensorId
		+ "</td><td><input type='text' value='" + sensorName + "'/></td><td class='deleteBtn'><input type='button' value='削除'>"
		+ "<input type='hidden' value='" +sensorId + "'></td></tr>");
}