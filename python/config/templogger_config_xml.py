#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
温湿度センサー設定を扱うモジュール(XML形式)
"""

import os
from xml.etree.ElementTree import*

class TempLoggerConfigXML():
    """
    温湿度センサー設定を扱うクラス(XML形式)
    """
    def __init__(self):
        self.file_path = os.path.dirname(__file__) + '/../../config/tempLoggerConfig/'
        self.file_name = '-tempLoggerConfig.xml'

    def read(self):
        """
        温湿度センサー設定を読み込み配列として返します。
        [{'id : 温湿度センサー番号, 'name' : 温湿度センサー名}]
        """
        temp_logger_list = []
        files = os.listdir(self.file_path)
        import datetime
        for file in files:
            # ファイルの存在可否
            if os.path.isfile(self.file_path + file):
                tree = parse(self.file_path + file)
                elem = tree.getroot()
                temp_logger = [{'id' : e.get('id'), 'name' : e.find('sensorName').text, 'date' : e.find('date').text} for e in elem.iter('sensor')]
                
                # 日付の降順にソート
                sorted_list = sorted(temp_logger, key=lambda sensor : sensor.get('date'), reverse=True)
                temp_logger_list.append(sorted_list[0])
        return temp_logger_list
