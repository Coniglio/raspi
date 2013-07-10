<?php
    /**
     * 空調制御コントローラー設定画面を扱うモジュール
     * @author 株式会社バイオス 山下　哲生<yamashita@bios-net.co.jp>
     */
    require_once('./config/controller.php');
    
    /**
      * 空調制御コントローラー設定画面クラス
      * @author 山下 哲生 <yamashita@bios-net.co.jp>
      * @create 2012/11/01
      *
      */
    class ControllerConfig extends Controller {
        private $controllerConfigXML;
        private $deleteId; // 削除ID
        private $configArray; // 設定の配列
        
        /**
         * コンストラクタ
         */
        public function __construct($configArray, $deleteId) {
            parent::__construct();
            $this->configArray = $configArray;
            $this->deleteId = $deleteId;
            require_once('./config/xml/controllerConfigXML.php');
            $this->controllerConfigXML = new ControllerConfigXML('../config/controllerConfig.xml');
        }

        /**
         * リクエストを処理します。
         */
        public function execute() {
            $this->logger->debug($this->configArray);
            // 空調制御コントローラー設定を取得
            if (empty($this->configArray) && empty($this->deleteId)) {
                $this->getControllerConfig();
                return;
            }

            // 空調制御コントローラー設定を削除
            if (! empty($this->configArray) && ! empty($this->deleteId)) {
                $this->deleteControllerConfig();
                return;
            }
            
            // 空調制御コントローラー設定を登録
            if (! empty($this->configArray) && empty($this->deleteId)) {
                $this->entryControllerConfig();
                return;
            }

            $this->logger->error('該当なし');
        }
        
        /**
         * 空調制御コントローラー設定を取得します。
         */
        private function getControllerConfig() {
            $this->logger->debug('空調制御コントローラー設定取得');
            $array = $this->controllerConfigXML->read();
            if (count($array) === 0) {
              $array[] = array(
                'controllerId' => '',
                'xbee' => ''
              );
            }
            echo json_encode($array);
        }

          /**
         * 空調制御コントローラー設定を削除します。
         */
        private function deleteControllerConfig() {
            $this->logger->debug('削除する空調制御コントローラー: ' . $this->deleteId);
            $this->logger->debug($this->configArray);
            $this->controllerConfigXML->delete($this->configArray, $this->deleteId);
        }

        /**
         * 空調制御コントローラー設定を登録します。
         */
        private function entryControllerConfig() {
            // 全角数字を半角に変換
            $this->convertHalfWidthNumber();

            $this->logger->debug('登録する空調制御コントローラー設定: ');
            $this->logger->debug($this->configArray);
			$this->controllerConfigXML->update($this->configArray);
        }

        /**
         * 全角数字の場合は半角数字に変換します。
         */
        private function convertHalfWidthNumber() {
            $num = count($this->configArray);
            for ($i = 0; $i < $num; $i++) {
                $xbee = $this->configArray[$i]['xbee'];
                $this->configArray[$i]['xbee'] = mb_convert_kana($xbee, 'a', 'utf-8');
            }
        }
    }
?>