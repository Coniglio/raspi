<?php
    /**
     * 画面の処理を行う抽象クラスを扱うモジュール
     * @author 株式会社バイオス 山下　哲生<yamashita@bios-net.co.jp>
     */

    require_once('./log4php/Logger.php');
    Logger::configure('./log4php/log4php.properties');

	/**
	 * コントローラークラス
	 * @author 山下 哲生 <yamashita@bios-net.co.jp>
	 * @create 2012/11/22
	 *
	 */
	abstract class Controller {
		protected $logger; // ロガー

        /**
         * コンストラクタ
         */
	    public function __construct() {
	    	$this->logger = Logger::getLogger('logger');
	    }

        /**
         * リクエストを処理します。
         */
	    abstract public function execute();
	}
?>
