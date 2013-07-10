<?php
    /**
     * 空調制御ココントローラー画面を扱うモジュール
     */

    require_once('./config/controller.php');
    
    /**
      * 空調制御コントローラーコマンド画面クラス
      *
      */
    class ControllerCommand extends Controller {
        private $controllerCommandXML;
        private $controlCommandXML;
        private $linkInfoXML;
        private $configArray; // 設定の配列

        /**
         * コンストラクタ
         */
        public function __construct($configArray, $deleteId) {
            parent::__construct();
            $this->configArray = $configArray;
            $this->deleteId = $deleteId;
            require_once('./config/xml/controllerCommandXML.php');
            $this->controllerCommandXML = new ControllerCommandXML('../config/controllerCommand/');
            require_once('./config/xml/linkInfoXML.php');
            $this->linkInfoXML = new LinkInfoXML('../config/linkInfo.xml');
            require_once('./config/xml/controlCommandXML.php');
            $this->controlCommandXML = new ControlCommandXML('../config/controlCommand/');
        }

        /**
         * リクエストを処理します。
         */
        public function execute() {
            $sensorId = null;
            if (isset($_POST['sensorId'])) {
                $sensorId = (int)$_POST['sensorId'];
            }

            // 空調制御コマンドを取得
            if (! empty($sensorId)) {
                $this->getControllerCommand($sensorId);
                return;
            }
            
            $this->logger->error('該当なし');
        }

        /**
         * 空調制御コマンドを取得します。
         */
        private function getControllerCommand($sensorId) {
            $this->logger->debug('空調制御コマンドを取得するコントローラーとリンクする温湿度センサー: ' . $sensorId);
            
            // 温湿度センサーリンク情報を取得
            $linkInfo = $this->linkInfoXML->read($sensorId);
            
            $controllerId = $linkInfo['controllerId'];
            $this->logger->debug('空調制御コマンドを取得するコントローラー: ' . $controllerId);
            
            // 空調制御コマンドを取得
            $controllerCommand = $this->controllerCommandXML->read($controllerId);
            $this->logger->debug($controllerCommand);
            
            if (count($controllerCommand) === 0) {
              $controllerCommand = array(
                array(
                  'controllerId' => $controllerId,
                  'controllerCommandId' => '',
                  'commandId' => ''
                )
              );
            }

            echo json_encode($controllerCommand);
        }
    }
?>
