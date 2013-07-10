<?php
    /**
     * 空調制御状態を扱うモジュール
     * @author 株式会社バイオス 山下　哲生<yamashita@bios-net.co.jp>
     */
    require_once('./config/controller.php');
    
    /**
      * 空調制御状態を扱うクラス
      * @author 山下 哲生 <yamashita@bios-net.co.jp>
      * @create 2012/11/06
      *
      */
    class ControllerState extends Controller {
        private $controllerStateXML;
        
        /**
         * コンストラクタ
         */
        public function __construct() {
            parent::__construct();
            require_once('./config/xml/controllerStateXML.php');
            $this->controllerStateXML = new ControllerStateXML('../config/controllerState.xml');
        }

        /**
         * リクエストを処理します。
         */
        public function execute() {
            // 空調制御状態を取得
            $controllerId = null;
            if (isset($_POST['controllerId'])) {
                $controllerId = $_POST['controllerId'];    
            }

            if (! empty($controllerId)) {
                $this->read($controllerId);
                return;
            }
        }
        
        /**
          * 空調制御状態を読み込み配列で返します。
          * 空調制御状態ファイルが存在しない場合は空の配列を返します。
          * @param string $controllerId コントローラー番号
          * @return array(
          *                 'controllerId' => コントローラー番号,
          *                 'sentCommand' => 最後に送信したコマンド番号,
          *                 'surveillanceCount' => 監視回数,
          *                 'latencyCount' => 待機回数
          *                 'sentCommandCount' => 空調制御コマンド送信後の監視回数
          *             )
          */
        public function read($controllerId) {
            $this->logger->debug('制御状態取得コントローラー番号' . $controllerId);
            $controllerState = $this->controllerStateXML->read($controllerId);
            $this->logger->debug($controllerState);
            echo json_encode($controllerState);
        }
    }
