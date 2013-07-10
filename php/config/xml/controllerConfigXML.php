<?php
    require_once('./log4php/Logger.php');
    Logger::configure('./log4php/log4php.properties');

    /**
      * XML形式のコントローラー設定クラス
      * @author 山下 哲生 <yamashita@bios-net.co.jp>
      * @create 2012/11/01
      *
      */
    class ControllerConfigXML {
        private $filePath;
        private $logger;

        public function __construct($filePath) {
            $this->filePath = $filePath;
            $this->logger = Logger::getLogger('logger');
        }
        
        /**
          * XML形式のコントローラー設定を読み込み配列で返します。
          * コントローラー設定ファイルが存在しない場合は空の配列を返します。
          * @return array array(
          *                         array(
          *                             'controllerId' => コントローラー番号,
          *                             'xbee' => XBeeシリアル番号
          *                         )
          *                     )
          */
        public function read() {
            if (! file_exists($this->filePath)) {
                return array(
                  array(
                    'controllerId' => '',
                    'xbee' => ''
                  )
                );
            }
            
            $array = array();
            $xml = simplexml_load_file($this->filePath);
            foreach($xml -> controller as $controller) {
              $array[] = array(
                'controllerId' => (string)$controller['id'],
                'xbee' => (string)$controller->xbee
              );
            }
            
            return $array;
        }
        
        /**
          * 配列のコントローラー設定をXMLファイルに書き込みます。
          * コントローラー設定ファイルが存在しない場合は作成します。
          * @param array $configArray array(
          *                             array(
          *                               'controllerId' => 空調制御コントローラー番号,
          *                               'xbee' => XBeeシリアル番号
          *                             )
          *                           )
          *
          */
        public function update($configArray) {
            $configLength = count($configArray);
            $dom = new DomDocument('1.0');
            $dom->encoding = 'UTF-8';
            $dom->formatOutput = true;
            $xml = $dom->appendChild($dom->createElement('controllers'));
            for ($i = 0; $i < $configLength; $i++) {
                $controller = $xml->appendChild($dom->createElement('controller'));
                $controller->setAttribute('id', $configArray[$i]['controllerId']);
                $xbee = $controller->appendChild($dom->createElement('xbee'));
                $xbee->appendChild($dom->createTextNode($configArray[$i]['xbee']));
            }
            file_put_contents($this->filePath, $dom->saveXML(), LOCK_EX);
        }

        /**
          * 指定した空調制御コントローラー情報をXMLファイルから削除します。
          * ファイルが存在すれば削除します。
          * @param array $array コントローラー設定
          * @param string $id コントローラー番号
          */
        public function delete($array, $id) {
            $configLength = count($array);
            $dom = new DomDocument('1.0');
            $dom->encoding = 'UTF-8';
            $dom->formatOutput = true;
            $xml = $dom->appendChild($dom->createElement('controllers'));
            for ($i = 0; $i < $configLength; $i++) {
                if ($array[$i]['controllerId'] === $id) continue;
                $controller = $xml->appendChild($dom->createElement('controller'));
                $controller->setAttribute('id', $array[$i]['controllerId']);
                $xbee = $controller->appendChild($dom->createElement('xbee'));
                $xbee->appendChild($dom->createTextNode($array[$i]['xbee']));
            }
            file_put_contents($this->filePath, $dom->saveXML(), LOCK_EX);
        }

    }
