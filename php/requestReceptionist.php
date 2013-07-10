<?php
    /**
     * クライアントからのリクエストを受け付けるモジュール
     * 処理の振り分けを行うDispatcherクラスを呼び出す。
     */

    setlocale( LC_ALL, 'ja_JP.UTF-8' );
    date_default_timezone_set('Asia/Tokyo');

    require_once('./dispatcher.php');
    $dispatcher = new Dispatcher();
    $dispatcher->dispatch();
?>
