<?php
    /**
     * 空調制御コマンド画面を扱うモジュール
     * @author 株式会社バイオス 山下　哲生<yamashita@bios-net.co.jp>
     */

    require_once('./config/controller.php');
    
    /**
      * 空調制御コマンド画面クラス
      * @author 山下 哲生 <yamashita@bios-net.co.jp>
      * @create 2012/11/16
      *
      */
    class ControlCommand extends Controller {
        private $controlCommandXML;
        private $deleteId; // 削除ID
        private $configArray; // 設定の配列
        
        /**
         * コンストラクタ
         * @param array $configArray 空調制御コマンド
         * @param string $deleteId 削除する空調制御コマンドの番号
         */
        public function __construct($configArray, $deleteId) {
            parent::__construct();
            $this->configArray = $configArray;
            $this->deleteId = $deleteId;
            require_once('./config/xml/controlCommandXML.php');
            $this->controlCommandXML = new ControlCommandXML('../config/controlCommand/');
        }

        /**
         * リクエストを処理します。
         */
        public function execute() {
            $entryId = null;
            if (isset($_POST['entryId'])) {
                $entryId = $_POST['entryId'];
            }
            
            // 空調制御コマンドを取得
            if (empty($this->configArray) && empty($entryId) && empty($this->deleteId)) {
                $this->getControlCommand();
                return;
            }
            
            // 空調制御コマンドを削除
            if (! empty($this->configArray) && empty($entryId) && ! empty($this->deleteId)) {
                $this->deleteControlCommand();
                return;
            }
            
            // 空調制御コマンドを登録
            if (! empty($this->configArray) && ! empty($entryId) && empty($this->deleteId)) {
                $this->entryControlCommand($entryId);
                return;
            }
            
            $this->logger->error('該当なし');
        }
        
        /**
         * 空調制御コマンドを取得します。
         */
        private function getControlCommand() {
            $commands = $this->controlCommandXML->read();
            $this->logger->debug('空調制御コマンドを取得');
            echo json_encode($commands);
        }

        /**
         * 空調制御コマンドを削除します。
         */
        private function deleteControlCommand() {
            $this->logger->debug('削除コマンド: ' . $this->deleteId);
            $this->logger->debug($this->configArray);
            $this->controlCommandXML->delete($this->configArray, $this->deleteId);
        }

        /**
         * 空調制御コマンドを登録します。
         * @param string $entryId 登録する空調制御コマンドの番号
         */
        private function entryControlCommand($entryId) {
            $this->logger->debug('登録コマンド: ' . $entryId);
            $this->logger->debug($this->configArray);
            
            // 空調制御コマンドの登録
            $this->controlCommandXML->update($this->configArray);
        }
    }
?>