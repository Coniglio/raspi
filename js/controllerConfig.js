/**
 * @fileOverview 空調制御コントローラー設定を扱うモジュールです。
 *
 * @author 株式会社バイオス　山下　哲生<yamashita@bios-net.co.jp>
 * @version 1.0.0
 */

 /**
 * 空調制御コントローラー設定をセットします。
 */
function setController() {
    var controllerConfig = getController();
    if (controllerConfig == null) {
        return;
    }
    var controllerNum = controllerConfig.length;
    var prevControllerId = 0; // コントローラー表示位置調整に使う。
    for (var i = 0; i < controllerNum; i++) {
        var controllerId = parseInt(controllerConfig[i].controllerId, 10);
        if ((i + 1) === controllerId) {
            appendRow(controllerConfig[i].xbee);
        } else {
            // コントローラーが順に存在しない場合は、空の入力欄を追加
            var controllerDiff = controllerId - prevControllerId - 1;
            for (var j = 0; j < controllerDiff; j++) {
                appendRow('', '');
            }
            appendRow(controllerConfig[i].xbee);
        }
        
        prevControllerId = parseInt(controllerConfig[i].controllerId, 10);
    }
}

$(function() {
    setController();
    appendRow('', '');
	 
    // コントローラー設定を登録
	$('#save').bind('click', function() {
        saveConfig('ControllerConfig', '../php/requestReceptionist.php', getControllerTable(), location.reload);
    });
	
    // コントローラー設定を削除
	$('.deleteBtn').live('click', function() {
        deleteConfig('ControllerConfig', '../php/requestReceptionist.php', getControllerTable(), $(this).children().next().val(), location.reload);
    });
	 
    // 入力行を1行増やす
	$("input[class='xbee']:last").live('click', function() {
        appendRow('');
	});
});

/**
 * 入力したコントローラー設定を配列で取得します。
 * @return Array 空調制御コントローラー設定
 */
function getControllerTable() {
    var controllerArray = new Array;
    $.each($("tr:not(:eq(0)):not(:last)"), function() {
        var td = $(this).find('td');
        var id = td.eq(0).text();
        var xbee = td.eq(1).find('input').val();
        if (xbee != '') {
            controllerArray.push({'controllerId' : id, 'xbee' : xbee});
        }
    });
    return controllerArray;
}

/**
 * 入力行を1行増やします。
 */
function appendRow(xbee, com) {
	$('tbody').append('<tr><td>' + ($('#controllerTable tbody').children().length) + '</td>'
        + "<td><input type='text' class='xbee' value='" + xbee + "'/></td>"
        + "<td class='deleteBtn'><input type='button' value='削除'>"
		+ "<input type='hidden' value='" + $('#controllerTable tbody').children().length + "'></td></tr>");
}