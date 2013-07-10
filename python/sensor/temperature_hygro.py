#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
温湿度データを扱うモジュール
"""

import datetime

import logging
import logging.config
logging.config.fileConfig('log.conf')

class TemperatureHygro():
    """
    温湿度データを扱うクラス
    """
    
    def __init__(self):
        self.temp_dir = '../log/TempHygro/'
    
    def get_time_loc(self, tm):
        """
        時刻から温湿度データの保存位置を返します。
        """
        hh = int(tm[0:2])
        mm = int(tm[3:5])
        return ((hh*60) + mm)

    def _get_time_str(self, bin):
        """
        温湿度データファイルに書き込む時刻を返します。
        """
        hh = '%02d' % (bin / 60)
        mm = ':%02d' % (bin % 60)
        return (hh + mm)
        
    def _make(self, file_path, sensor_id):
        """
        温湿度データファイルを作成します。
        """    
        with open(file_path, 'w') as f: 
            for seq in range(0, 1440):
                #         "HH:MM"             ,-99.9,-99.9
                f.write(self._get_time_str(seq) + '            \n')
        
    def write(self, file_path, temp):
        """
        温湿度データを書き込みます。
        """   
        now_tim = datetime.datetime.now().strftime('%H:%M')
        loc = self.get_time_loc(now_tim)
        with open(file_path, 'r+') as f:
            f.seek(loc * (len('00:00,-99.9,-99.9') + 1))
            f.write(now_tim + ',' + temp)
    
    def logging_temperature_hygro(self, xbee_data):
        """
        温湿度データのロギングをします。
        """
        if not xbee_data:
            logging.warn(u'XBee受信データが存在しません。')
            return
            
        if not xbee_data.has_key('rf_data'):
            logging.warn(u'温湿度データが存在しません。')
            return
            
        temp = xbee_data['rf_data'].rstrip()
        
        # 温湿度データの書式チェック
        import re
        if None == re.match("[0-9]{2}[,]{1}[\s]?[\s]?[-]?[0-9]?[0-9]?[.]{1}[0-9]{1}[,]{1}[\s]?[\s]?[-]?[0-9]?[0-9]?[.]{1}[0-9]{1}", temp):
            logging.warn(u'破棄するデータ: ' + temp)
            return
        
        # 温湿度データの書き込み
        file_path = self.make_path(temp[1])
        self.write(file_path, temp[3:])
        
        logging.debug(u'ロギングデータ:' + temp)

    def read(self, file_path):
        """
        温湿度データを読み込みます。
        """   
        loc = self.get_time_loc(datetime.datetime.now().strftime('%H:%M'))
        with open(file_path, 'r') as f:
            f.seek(loc * (len('00:00,-99.9,-99.9') + 1))
            temp_list = f.readline().split(',')
        return temp_list       
        
    def make_path(self, sensor_id):
        """
        温湿度データのパスを作成します。
        """
        import os
        if not os.path.isdir(self.temp_dir):
            os.mkdir(self.temp_dir)
            logging.warn(self.temp_dir + u'を作成します。')

        date = datetime.datetime.today()

        dir_name = self.temp_dir + date.strftime("%Y/")
        if not os.path.isdir(dir_name):
            os.mkdir(dir_name)
            logging.warn(dir_name + u'を作成します。')
            
        dir_name = dir_name + "sensor" + sensor_id + "/"
        if not os.path.isdir(dir_name):
            os.mkdir(dir_name)
            logging.warn(dir_name + u'を作成します。')

        file_path = dir_name + sensor_id + date.strftime('-%m%d.csv')
        if not os.path.isfile(file_path):
            self._make(file_path, sensor_id)
            logging.warn(file_path + u'を作成します。')
            
        return file_path
