<?php

    /**
     * 各温湿度センサー設定の日付を比較します。
     */
    function compareTempLogger($sensor1, $sensor2) {
        $date1 = $sensor1['date'];
        $date2 = $sensor2['date'];

        if (strtotime($date1) == strtotime($date2)) {
            return 0;
        }

        return strtotime($date1) < strtotime($date2) ? 1 : -1;
    }

    /**
     * 温湿度センサー設定画面を扱うモジュール
     */
    require_once('./config/controller.php');
    
    /**
      * 温湿度センサー設定画面クラス
      *
      */
    class TempLoggerConfig extends Controller {
        private $tempLoggerConfigXML;
        private $deleteId; // 削除ID
        private $configArray; // 設定の配列

        /**
         * コンストラクタ
         */
        public function __construct($configArray, $deleteId) {
            parent::__construct();
            $this->configArray = $configArray;
            $this->deleteId = $deleteId;
            require_once('./config/xml/tempLoggerConfigXML.php');
            $this->tempLoggerConfigXML = new TempLoggerConfigXML('../config/tempLoggerConfig/');
        }

        /**
         * リクエストを処理します。
         * 戻り値はjson形式で返します。
         */
        public function execute() {
          // 温湿度センサー設定を取得
          if (empty($this->configArray) && empty($this->deleteId)) {
              $this->getAllTempLoggerConfig();
              return;
          }
          
          // 温湿度センサー名をリンク画面にHTML出力しているため、スクリプトインジェクション対策
          $num = count($this->configArray);
          for ($i = 0; $i < $num; $i++) {
              $this->configArray[$i]['sensorName'] = htmlspecialchars($this->configArray[$i]['sensorName']);
          }
          
          // 温湿度センサー設定の削除
          if (! empty($this->configArray) && ! empty($this->deleteId)) {
              $this->deleteTempLoggerConfig();
              return;
          }
          
          // 温湿度センサー設定の登録
          if (! empty($this->configArray) && empty($this->deleteId)) {
              $this->entryTempLoggerConfig();
              return;
          }
          
          $this->logger->error('該当なし');
        }

        /**
         * 温室度センサー設定を取得します。
         */
        private function getAllTempLoggerConfig() {
            $date = null;
            if (isset($_POST['date'])) {
                $date = $_POST['date'];
            }

            $this->logger->debug('温湿度センサー設定を取得');

            // 各センサーの温湿度センサー設定を取得
            $sensorConfig = $this->tempLoggerConfigXML->allRead();
            if (count($sensorConfig) === 0) {
              $sensorConfig = array(
                array(
                  'sensorId' => '',
                  'sensorName' => '',
                  'date' => ''
                )
              );
            }

            // 温湿度センサー設定取得日が指定されている場合
            if (! empty($date)) {
                $this->logger->debug('温湿度センサー設定取得日:' . $date);
                $array = $this->getNearTempLoggerConfig($sensorConfig, $date);
            } else {
                // 各センサーの最新の温湿度センサー設定を取得
                $array = $this->getNewestTempLoggerConfig($sensorConfig);
            }

            // json形式で返す。
            echo json_encode($array);
        }

        /**
         * 温室度センサー設定を削除します。
         */
        private function deleteTempLoggerConfig() {
            $this->logger->debug('削除する温湿度センサー: ' . $this->deleteId);
            $this->tempLoggerConfigXML->delete($this->deleteId);
        }

        /**
         * 温室度センサー設定を登録します。
         */
        private function entryTempLoggerConfig() {
            $this->logger->debug('登録する温湿度センサー設定: ');
            $this->logger->debug($this->configArray);
            $this->tempLoggerConfigXML->entry($this->configArray);
        }

        // 指定日に最も近い温湿度センサー設定を取得します。
        private function getNearTempLoggerConfig($sensorConfig, $date) {
            $config = array();
            $this->logger->debug($sensorConfig);
            // 指定日に最も近い日の温湿度センサー設定を取得
            foreach ($sensorConfig as $sensor) {
                // 日付をキーにして降順にソート
                usort($sensor, 'compareTempLogger');
                $isMatch = false;
                foreach ($sensor as $value) {
                  if (strtotime($value['date']) <= strtotime($date)) {
                      $config[] = $value;
                      $isMatch = true;
                      break;
                  }
                }
                // 一致する温湿度センサー設定が見つからない場合は最も古い設定を使う
                if (! $isMatch) $config[] = $sensor[count($sensor) - 1];
            }

            return $config;
        }

        // 最新の温室度センサー設定を取得します。
        private function getNewestTempLoggerConfig($array) {
            if ($array[0][0]['sensorId'] == '') return $array;

            $config = array();

            // 温湿度センサー設定を日付の降順でソート
            foreach ($array as $sensor) {
                usort($sensor, 'compareTempLogger');
                // 最新のデータのみを取得
                $config[] = $sensor[0];
            }

            return $config;
        }
    }
?>
