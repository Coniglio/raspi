<?php
    /**
     * システム設定画面を扱うモジュール
     * @author 株式会社バイオス 山下　哲生<yamashita@bios-net.co.jp>
     */
    require_once('./config/controller.php');
    
    /**
      * システム設定画面クラス
      * @author 山下 哲生 <yamashita@bios-net.co.jp>
      * @create 2012/11/01
      *
      */
    class SystemConfig extends Controller {
        private $systemConfigXML;
        private $deleteId; // 削除ID
        private $configArray; // 設定の配列
        
        /**
         * コンストラクタ
         */
        public function __construct($configArray, $deleteId) {
            parent::__construct();
            $this->configArray = $configArray;
            $this->deleteId = $deleteId;
            require_once('./config/xml/systemConfigXML.php');
            $this->systemConfigXML = new SystemConfigXML('../config/systemConfig.xml');
        }

        /**
         * リクエストを処理します。
         */
        public function execute() {
            //$this->logger->debug($this->configArray);
            // システム設定を取得
            if (empty($this->configArray) && empty($this->deleteId)) {
                $this->getSystemConfig();
                return;
            }
            
            // システム設定を削除
            if (! empty($this->configArray) && ! empty($this->deleteId)) {
                $this->deleteSystemConfig();
                return;
            }
            
            // システム設定を登録
            if (! empty($this->configArray) && empty($this->deleteId)) {
                $this->entrySystemConfig();
                return;
            }
            
            $this->logger->error('該当なし');
        }

        /**
         * システム設定を取得します。
         */
        private function getSystemConfig() {
            $this->logger->debug('システム設定を取得');
            $systemConfig = $this->systemConfigXML->read();

            echo json_encode($systemConfig);
        }

        /**
         * システム設定を削除します。
         */
        private function deleteSystemConfig() {
            $this->logger->debug('削除するシステム設定: ' . $this->deleteId);
            $this->configArray[$this->deleteId] = '';
            $this->systemConfigXML->update($this->configArray);
        }

        /**
         * システム設定を登録します。
         */
        private function entrySystemConfig() {
            // 入力チェック(全角数字を半角に変換)
            $this->checkHalfWidthNumber();

            $this->logger->debug('登録するシステム設定: ' . implode(',', $this->configArray));
            $this->systemConfigXML->update($this->configArray);
        }

        /**
         * 半角数字であるかチェックします。
         * 全角数字の場合は半角数字に変換します。
         */
        private function checkHalfWidthNumber() {
            // メール送信元
            $from = $this->configArray['from'];
            if (! preg_match("/^[0-9]+$/", $from)) {
                $this->configArray['from'] = mb_convert_kana($from, 'n', 'utf-8');
            }

            // メール送信元
            $to = $this->configArray['to'];
            if (! preg_match("/^[0-9]+$/", $to)) {
                $this->configArray['to'] = mb_convert_kana($to, 'n', 'utf-8');
            }

            // SMTP
            $smtp = $this->configArray['smtp'];
            if (! preg_match("/^[0-9]+$/", $smtp)) {
                $this->configArray['smtp'] = mb_convert_kana($smtp, 'n', 'utf-8');
            }

            // ユーザ
            $user = $this->configArray['user'];
            if (! preg_match("/^[0-9]+$/", $user)) {
                $this->configArray['user'] = mb_convert_kana($user, 'n', 'utf-8');
            }

            // パスワード
            $passwd = $this->configArray['passwd'];
            if (! preg_match("/^[0-9]+$/", $passwd)) {
                $this->configArray['passwd'] = mb_convert_kana($passwd, 'n', 'utf-8');
            }

            // SMTPポート
            $port = $this->configArray['port'];
            if (! preg_match("/^[0-9]+$/", $port)) {
                $this->configArray['port'] = mb_convert_kana($port, 'n', 'utf-8');
            }

            // コーディネータCOMポート
            $cordinator = $this->configArray['cordinator'];
            if (! preg_match("/^[0-9]+$/", $cordinator)) {
                $this->configArray['cordinator'] = mb_convert_kana($cordinator, 'n', 'utf-8');
            }

            // 赤外線受信機COMポート
            $irReceive = $this->configArray['irReceive'];
            if (! preg_match("/^[0-9]+$/", $irReceive)) {
                $this->configArray['irReceive'] = mb_convert_kana($irReceive, 'n', 'utf-8');
            }
        }
    }
?>