<?php
    /**
     * 温湿度センサーリンク画面を扱うモジュール
     */
    require_once('./config/controller.php');
    
    /**
      * 温湿度センサーリンク画面クラス
      *
      */
    class LinkInfo extends Controller {
        private $linkInfoXML;
        private $deleteId; // 削除ID
        private $configArray; // 設定の配列
        
        /**
         * コンストラクタ
         */
        public function __construct($configArray, $deleteId) {
            parent::__construct();
            $this->configArray = $configArray;
            $this->deleteId = $deleteId;
            require_once('./config/xml/linkInfoXML.php');
            $this->linkInfoXML = new LinkInfoXML('../config/linkInfo.xml');
        }

        /**
         * リクエストを処理します。
         */
        public function execute() {
            $sensorId = null;
            if (isset($_POST['sensorId'])) {
                $sensorId = (int)$_POST['sensorId'];
            }

            $fromCopy = null;
            if (isset($_POST['fromCopy'])) {
                $fromCopy = $_POST['fromCopy'];
            }

            $toCopy = null;
            if (isset($_POST['toCopy'])) {
                $toCopy = $_POST['toCopy'];
            }

            // 温湿度センサーリンク情報を取得
            if (! empty($sensorId) && empty($this->configArray) && empty($this->deleteId)) {
                $this->getLinkInfo($sensorId);
                return;
            }
            
            // 温湿度センサーリンク情報を削除
            if (! empty($this->configArray) && ! empty($this->deleteId)) {
                $this->deleteLinkInfo();
                return;
            }

            // 温湿度センサーリンク情報を登録
            if (! empty($this->configArray) && empty($this->deleteId)) {
                $this->entryLinkInfo();
                return;
            }

            // 温湿度センサーリンク情報をコピー
            if (! empty($fromCopy) && ! empty($toCopy)) {
                $this->copyLinkInfo($fromCopy, $toCopy);
                return;
            }

            $this->logger->error('該当なし');
        }

        /**
         * 温室度センサーリンク情報を取得します。
         */
        private function getLinkInfo($sensorId) {
            $this->logger->debug('温湿度センサー' . $sensorId . '番のリンク情報を取得');
            $array = $this->linkInfoXML->read($sensorId);
            echo json_encode($array);
        }

        /**
         * 温室度センサーリンク情報を削除します。
         */
        private function deleteLinkInfo() {
            $this->logger->debug('削除する温湿度センサーリンク情報: ' . $this->deleteId);
            $this->linkInfoXML->delete((integer)$this->deleteId);
        }

        /**
         * 温室度センサー設定を登録します。
         */
        private function entryLinkInfo() {
            $this->logger->debug('登録する温湿度センサーリンク情報: ' . implode(',', $this->configArray));
            $this->linkInfoXML->entry($this->configArray);
        }

        private function copyLinkInfo($fromCopy, $toCopy) {
            $this->logger->debug('リンク設定コピー元: ' . $fromCopy, ', リンク設定コピー先:' . $toCopy);
            $configArray = array();
            $fromConfig = $this->linkInfoXML->read($fromCopy);

            // 設定をコピー
            $configArray['sensorId'] = $toCopy;
            $configArray['controllerId'] = $fromConfig['controllerId'];
            $configArray['surveillanceCycle'] = $fromConfig['surveillanceCycle'];
            $configArray['maxTempLimitThreshold'] = $fromConfig['maxTempLimitThreshold'];
            $configArray['minTempLimitThreshold'] = $fromConfig['minTempLimitThreshold'];
            $configArray['maxHygroLimitThreshold'] = $fromConfig['maxHygroLimitThreshold'];
            $configArray['minHygroLimitThreshold'] = $fromConfig['minHygroLimitThreshold'];
            $configArray['latency'] = $fromConfig['latency'];
            $configArray['maxTempCautionThreshold'] = $fromConfig['maxTempCautionThreshold'];
            $configArray['minTempCautionThreshold'] = $fromConfig['minTempCautionThreshold'];
            $configArray['maxHygroCautionThreshold'] = $fromConfig['maxHygroCautionThreshold'];
            $configArray['minHygroCautionThreshold'] = $fromConfig['minHygroCautionThreshold'];
            $configArray['isSend'] = $fromConfig['isSend'];

            $this->linkInfoXML->entry($configArray);
        }
    }
?>
