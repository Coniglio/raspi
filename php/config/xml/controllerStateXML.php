<?php
    /**
      * XML形式の空調制御状態を扱うクラス
      * @author 山下 哲生 <yamashita@bios-net.co.jp>
      * @create 2012/11/06
      *
      */

    require_once('./log4php/Logger.php');
    Logger::configure('./log4php/log4php.properties');

    class ControllerStateXML {
        private $logger;

        public function __construct($filePath) {
            $this->filePath = $filePath;
            $this->logger = Logger::getLogger('logger');
        }
        
        /**
          * XML形式の空調制御状態を読み込み配列で返します。
          * 空調制御状態ファイルが存在しない場合は空の配列を返します。
          * @param string $id コントローラー番号
          * @return array array(
          *                 'controllerId' => コントローラー番号,
          *                 'sentCommand' => 送信したコマンド,
          *                 'surveillanceCount' => 監視回数,
          *                 'latencyCount' => 待機回数
          *                 'sentCommandCount' => コマンド送信後の監視回数
          *               )
          */
        public function read($id) {
            if (! file_exists($this->filePath)) {
                return array(
                        'controllerId' => '',
                        'sentCommand' => '',
                        'surveillanceCount' => '',
                        'latencyCount' => '',
                        'sentCommandCount' => ''
                );
            }
            
            $array = array();
            $xml = simplexml_load_file($this->filePath);
            foreach($xml -> controller as $controller) {
                if ($id == (string)$controller['id']) {
                    $array['controllerId'] = (string)$controller['id'];
                    $array['sentCommand'] = (string)$controller->sentCommand;
                    $array['surveillanceCount'] = (string)$controller->surveillanceCount;
                    $array['latencyCount'] = (string)$controller->sentCommandCount;
                    break;
                }
            }
            

            return $array;
        }
    }
