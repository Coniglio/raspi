<?php
    require_once('./log4php/Logger.php');
    Logger::configure('./log4php/log4php.properties');

    /**
      * XML形式の空調制御コントローラーコマンドクラス
      *
      */
    class ControllerCommandXML {
        private $filePath;
        private $logger;
        
        public function __construct($filePath) {
            $this->filePath = $filePath;
            $this->logger = Logger::getLogger('logger');
        }
        
        /**
          * XML形式の空調制御コントローラーのコマンドを読み込み配列で返します。
          * 空調制御コマンドファイルが存在しない場合は空の配列を返します。
          * コントローラー番号が一致する空調制御コマンドが存在しない場合は空の配列を返します。
          * @param integer $id = コントローラー番号
          * @return array(
          *                 array(
          *                     'controllerId' => 空調制御コントローラー番号,
          *                     'controllerCommandId' => 空調制御コントローラーコマンド番号,
          *                     'commandId' => 空調制御コマンド番号
          *                 )
          *             )
          */
        public function read($id) {
            $xmlPath = $this->filePath . 'controllerCommand' . $id . '.xml';
            $this->logger->debug($xmlPath);
            if (! file_exists($xmlPath)) {
                return array(
                  array(
                    'controllerId' => '',
                    'controllerCommandId' => '',
                    'commandId' => '',
                  )
                );
            }

            $array = array();
            $xml = simplexml_load_file($xmlPath);
            foreach($xml -> controllerCommand as $controllerCommand) {
                if ($id == (int)$xml['id']) {
                    $array[] = array(
                      'controllerId' => (string)$xml['id'],
                      'controllerCommandId' => (string)$controllerCommand['id'],
                      'commandId' => (string)$controllerCommand->command['id']
                    );
                }
            }
            return $array;
        }
        
        /**
          * 配列の空調制御コマンドをXMLファイルに書き込みます。
          * 空調制御コマンドファイルが存在しない場合は作成します。
          * @param array $configArray array(
          *                             array(
          *                               'controllerId' => 空調制御コントローラー番号,
          *                               'controllerCommandId' => 空調制御コントローラーコマンド番号,
          *                               'commandId' => 空調制御コマンド番号
          *                             )
          *                           )
          *
          */
        public function entry($configArray) {
            $xml = simplexml_load_file($this->filePath);
            $configLength = count($configArray);
            $dom = new DomDocument('1.0');
            $dom->encoding = 'UTF-8';
            $dom->formatOutput = true;
            $root = $dom->appendChild($dom->createElement('controllerCommands'));
            $isNewControllerCommand = true;
            
            foreach($xml -> controller as $controller) {
                $c = $root->appendChild($dom->createElement('controller'));
                $c->setAttribute('id', (string)$controller['id']);

                if ($configArray[0]['controllerId'] == (string)$controller['id']) {
                    $configNum = count($configArray);
                    for ($i = 0; $i < $configNum; $i++) {
                        $cm = $c->appendChild($dom->createElement('controllerCommand'));
                        $cm->setAttribute('id', $configArray[$i]['controllerCommandId']);
                        $m = $cm->appendChild($dom->createElement('command'));
                        $m->setAttribute('id', $configArray[$i]['commandId']);
                    }
                } else {
                    foreach($controller->controllerCommand as $controllerCommand) {
                        $cm = $c->appendChild($dom->createElement('controllerCommand'));
                        $cm->setAttribute('id', (string)$controllerCommand['id']);
                        $m = $cm->appendChild($dom->createElement('command'));
                        $m->setAttribute('id', (string)$controllerCommand->command['id']);
                    }
                }
                if ((string)$controller['id'] === $array[0]['controllerId']) $isNewControllerCommand = false;
            }
            if ($isNewControllerCommand) {
                $c = $root->appendChild($dom->createElement('controller'));
                $c->setAttribute('id', $configArray[0]['controllerId']);
                $configNum = count($configArray);
                for ($i = 0; $i < $configNum; $i++) {
                    $cm = $c->appendChild($dom->createElement('controllerCommand'));
                    $cm->setAttribute('id', $configArray[$i]['controllerCommandId']);
                    $m = $cm->appendChild($dom->createElement('command'));
                    $m->setAttribute('id', $configArray[$i]['commandId']);
                }
            }
            file_put_contents($this->filePath, $dom->saveXML(), LOCK_EX);
        }

        /**
          * 指定した空調制御コントローラーコマンドをXMLファイルから削除します。
          * ファイルが存在すれば削除します。
          * @param array $array array(
          *                             array(
          *                               'controllerId' => 空調制御コントローラー番号,
          *                               'controllerCommandId' => 空調制御コントローラーコマンド番号,
          *                               'commandId' => 空調制御コマンド番号
          *                             )
          *                           )
          * @param string $deleteCommandId コマンド番号
          */
        public function delete($array, $deleteCommandId) {
            $xml = simplexml_load_file($this->filePath);
            $configLength = count($array);
            $dom = new DomDocument('1.0');
            $dom->encoding = 'UTF-8';
            $dom->formatOutput = true;
            $root = $dom->appendChild($dom->createElement('controllerCommands'));
            foreach($xml -> controller as $controller) {
                $c = $root->appendChild($dom->createElement('controller'));
                $c->setAttribute('id', (string)$controller['id']);
                
                if ($array[0]['controllerId'] === (string)$controller['id']) {
                    for ($i = 0; $i < $configLength; $i++) {
                        if ($deleteCommandId === $array[$i]['controllerCommandId']) continue;
                        $cm = $c->appendChild($dom->createElement('controllerCommand'));
                        $cm->setAttribute('id', $array[$i]['controllerCommandId']);
                        $m = $cm->appendChild($dom->createElement('command'));
                        $m->setAttribute('id', $array[$i]['commandId']);
                    }
                } else {
                    foreach($controller->controllerCommand as $controllerCommand) {
                        $cm = $c->appendChild($dom->createElement('controllerCommand'));
                        $cm->setAttribute('id', (string)$controllerCommand['id']);
                        $m = $cm->appendChild($dom->createElement('command'));
                        $m->setAttribute('id', (string)$controllerCommand->command['id']);
                    }
                }
            }
            file_put_contents($this->filePath, $dom->saveXML(), LOCK_EX);
        }
    }
