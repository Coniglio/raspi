<?php    
    /**
      * XML形式のシステム設定クラス
      * @author 山下 哲生 <yamashita@bios-net.co.jp>
      * @create 2012/11/01
      *
      */
    class SystemConfigXML {
        private $filePath;
        
        public function __construct($filePath) {
            $this->filePath = $filePath;
        }
        
        /**
          * XML形式のシステム設定を読み込み配列で返します。
          * システム設定ファイルが存在しない場合は空の配列を返します。
          * @return array array(
          *                 'from' => メール送信元,
          *                 'to' => XBeeメール送信先番号,
          *                 'smtp' => SMTPサーバホスト名,
          *                 'port' => SMTPサーバポート番号,
          *                 'user' => メール送信元ユーザ,
          *                 'passwd' => メール送信元ユーザのパスワード,
          *                 'cordinator' => XBeeコーディネータのCOMポート,
          *                 'irReceive' => 赤外線受信機のCOMポート,
          *                 'weather' => 気象情報としてグラフに描画する温湿度センサーの番号 
          *                 'xbee' => XBee供給電圧閾値         
          *               )
          */
        public function read() {
            if (! file_exists($this->filePath)) {
                return array(
                    'from' => '',
                    'to' => '',
                    'smtp' => '',
                    'port' => '',
                    'user' => '',
                    'passwd' => '',
                    'cordinator' => '',
                    'irReceive' => '',
                    'weather' => '',
                    'xbee' => ''
                );
            }
            
            $xml = simplexml_load_file($this->filePath);
            $mail = $xml->mail;
            $weather = $xml->weather;
            return array(
                'from' => (string)$mail->from,
                'to' => (string)$mail->to,
                'smtp' => (string)$mail->smtp,
                'port' => (string)$mail->port,
                'user' => (string)$mail->user,
                'passwd' => (string)$mail->passwd,
                'cordinator' => (string)$xml->cordinator,
                'irReceive' => (string)$xml->irReceive,
                'weather' => (string)$xml->weather,
                'xbee' => (string)$xml->xbee
            );
        }
        
        /**
          * 配列のシステム設定をXMLファイルに書き込みます。
          * システム設定ファイルが存在しない場合は作成します。
          * @param array $configArray array(
          *                             'from' => メール送信元,
          *                             'to' => メール送信先,
          *                             'smtp' => メール送信元SMTPサーバホスト,
          *                             'port' => メール送信元SMTPサーバポート番号
          *                             'user' => メール送信元ユーザ名,
          *                             'passwd' => メール送信元ユーザパスワード,
          *                             'cordinator' => XBeeコーディネータCOMポート,
          *                             'irReceive' => 赤外線受信機COMポート,
          *                             'weather' => 気象情報として温湿度グラフに描画する温湿度センサーの番号
          *                             'xbee' => XBee供給電圧閾値 
          *                           )
          *
          */
        public function update($configArray) {
            $dom = new DomDocument('1.0');
            $dom->encoding = 'UTF-8';
            $dom->formatOutput = true;
            $xml = $dom->appendChild($dom->createElement('systemConfig'));
            
            $mail = $xml->appendChild($dom->createElement('mail'));
            $from = $mail->appendChild($dom->createElement('from'));
            $from->appendChild($dom->createTextNode($configArray['from']));
            $to = $mail->appendChild($dom->createElement('to'));
            $to->appendChild($dom->createTextNode($configArray['to']));
            $smtp = $mail->appendChild($dom->createElement('smtp'));
            $smtp->appendChild($dom->createTextNode($configArray['smtp']));
            $port = $mail->appendChild($dom->createElement('port'));
            $port->appendChild($dom->createTextNode($configArray['port']));
            $user = $mail->appendChild($dom->createElement('user'));
            $user->appendChild($dom->createTextNode($configArray['user']));
            $passwd = $mail->appendChild($dom->createElement('passwd'));
            $passwd->appendChild($dom->createTextNode($configArray['passwd']));
            $cordinator = $xml->appendChild($dom->createElement('cordinator'));
            $cordinator->appendChild($dom->createTextNode($configArray['cordinator']));
            $irReceive = $xml->appendChild($dom->createElement('irReceive'));
            $irReceive->appendChild($dom->createTextNode($configArray['irReceive']));
            $wather = $xml->appendChild($dom->createElement('weather'));
            $wather->appendChild($dom->createTextNode($configArray['weather']));
            $xbee = $xml->appendChild($dom->createElement('xbee'));
            $xbee->appendChild($dom->createTextNode($configArray['xbee']));
            
            file_put_contents($this->filePath, $dom->saveXML(), LOCK_EX);
        }
    }

