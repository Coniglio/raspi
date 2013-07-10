/**
 * @fileOverview 温湿度ロギングシステム、空調自動制御システムの設定ファイルを扱うモジュールです。
 *
 * @author 株式会社バイオス　山下　哲生<yamashita@bios-net.co.jp>
 * @version 1.0.0
 */

/**
 * 非同期通信で空調制御コントローラー設定を取得します。
 * @return Array 空調制御コントローラー設定[[
 *              'controllerId' : 空調制御コントローラー番号,
 *              'xbee' : XBeeシリアル番号,
 *             ]]
 */
function getController() {
    var controller = null;
	  $.ajax({
        type: 'POST',
        async: false,
        url: '../php/requestReceptionist.php',
        dataType: 'json',
        data: {'target' : 'ControllerConfig'},
    }).done(function(json) {
        if (json == null || json[0].controllerId == '') {
            alert('空調制御設定が存在しません。');
			return null;
		}
        controller = json;
    }).fail(function (data) {
        alert('空調制御コントローラー設定の取得に失敗しました。');
        return null;
    });
    return controller;
}

/**
 * 非同期通信で温湿度センサー情報を取得します。
 * @param String path  温湿度センサー設定ファイルパス
 * @return Array 温湿度センサー設定[[
 *              'sensorId' : 温湿度センサー番号,
 *              'sensorName' : 温湿度センサー名
 *             ]]
 */
function getTempLoggerConfig(path) {
    var sensor = [];
    var filePath = '../php/requestReceptionist.php';
    if (path) filePath = path;
	  $.ajax({
       type: 'POST',
       url: filePath,
       dataType: 'json',
       async: false,
       data: {'target' : 'TempLoggerConfig'},
    }).done(function(json) {
        if (json == null || json[0].sensorId == '') {
          alert('温湿度センサー設定が存在しません。');
          return null;
        }
        sensor = json;
    }).fail(function (data) {
        alert('温湿度センサー設定の取得に失敗しました。');
        return null;
    });
    return sensor;
}

/**
 * 非同期通信で対象の設定値を削除します。
 * @param String target 削除処理の対象
 * @param String url 削除処理のURL
 * @param String configArray 書き込む設定
 * @param String deleteId 削除する設定の番号
 */
function deleteConfig(target, url, configArray, deleteId, callback) {
    $.ajax({
        type: 'POST',
        async: false,
        url: url,
        dataType: 'json',
        data: {'target' : target, 'configArray' : configArray, 'deleteId' : deleteId},
    }).done(function(json) {
        callback();
    }).fail(function (data) {
        alert("設定値の削除に失敗しました。\n数分ほど時間をあけてからやり直してください。" 
            + "\n何度も異常が発生した場合は、\n緊急連絡先へ連絡をお願いします。");
    });
}

/**
 * 非同期通信で対象の設定値を登録します。
 * @param String target 登録処理の対象
 * @param String url 登録処理のURL
 * @param String configArray 書き込む設定
 */
function saveConfig(target, url, configArray, callback) {
    $.ajax({
        type: 'POST',
        async: false,
        url: url,
        dataType: 'json',
        data: {'target' : target, 'configArray' : configArray},
    }).done(function(json) {
        callback();
    }).fail(function (XMLHttpRequest, status, errorThrown) {
        alert("設定値の登録に失敗しました。\n数分ほど時間をあけてからやり直してください。" 
          + "\n何度も異常が発生した場合は、\n緊急連絡先へ連絡をお願いします。");
	});
}
