#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
コントローラー設定を扱うモジュール(XML形式)
"""

import os
from xml.etree.ElementTree import*

class ControllerConfigXML():
    """
    コントローラー設定を扱うクラス(XML形式)
    """
    def __init__(self):
        self.file_path = '../config/controllerConfig.xml'
        
    def read(self, controller_id):
        """
        指定されたコントローラー番号に一致するコントローラー設定を辞書で返します。
        {'id' : コントローラー番号, 'xbee' : XBeeシリアル番号}
        """
        # ファイルの存在可否
        if not os.path.isfile(self.file_path):
            return None 
        
        tree = parse(self.file_path)
        elem = tree.getroot()
        controller_config = {}
        for e in elem.iter('controller'):
            if e.get('id') == controller_id:
                controller_config['id'] = e.get('id')
                controller_config['xbee'] = e.find('xbee').text
        return controller_config

    def all_read(self):
        """
        コントローラー設定を読み込み配列として返します。
        [{'id : コントローラー番号, 'xbee' : XBeeシリアル番号}]
        """
        # ファイルの存在可否
        if not os.path.isfile(self.file_path):
            return None 
        
        tree = parse(self.file_path)
        elem = tree.getroot()
        controller_config = []
        controller_config = [{'id' : e.get('id'), 'xbee' : e.find('xbee').text} for e in elem.iter('controller')]
        return controller_config
