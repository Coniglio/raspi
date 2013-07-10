<?php
    /**
      * 温湿度データクラス
      *
      */
    class TemperatureLog {
        private $filePath; // 温湿度データファイルパス
        
        public function __construct($filePath) {
            $this->filePath = $filePath;
        }
        
        /**
          * CSV形式の温湿度データを読み込み配列で返します。
          * 温湿度データファイルが存在しない場合は空の配列を返します。
          * @return array(
          *     'time' => 時刻,
          *     'theromo' => 温度配列,
          *     'hygro' => 湿度配列
          * )
          */
        public function read() {
            if (! file_exists($this->filePath)) {
                return array(
                    'time' => array(''),
                    'thermo' => array(''),
                    'hygro' => array('')
                );
            }
            
            $i = 0;
            $fp = fopen($this->filePath, 'r');
            if (flock($fp, LOCK_SH)) {
                while ($data = fgetcsv($fp)) {
                    // CSVには日付を予め書き込んでおり、
                    // 温度が空であれば温湿度データ無しとして次の参照する。
                    if (empty($data[1])) {
                        continue;
                    }
                    
                    $time[$i] = $data[0];
                    $thermo[$i] = $data[1];
                    $hygro[$i] = $data[2];
                    $i++;
                }
                flock($fp, LOCK_UN);
            } else {
                fclose($fp);
                return array(
                    'time' => array(''),
                    'thermo' => array(''),
                    'hygro' => array('')
                );
            }
            fclose($fp);
            
            return array(
                'time' => $time,
                'thermo' => $thermo,
                'hygro' => $hygro
            );
        }
    }
?>
