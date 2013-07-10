#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
コントローラー状態を扱うモジュール(XML形式)
"""

import os
from xml.etree.ElementTree import*

class ControllerStateXML():
    """
    コントローラー状態を扱うクラス(XML形式)
    """
    def __init__(self):
        self.file_path = '../config/controllerState.xml'
        
    def write(self, controller_state):
        """
        コントローラー状態を書き込みます。
        ファイルが存在しない場合は作成します。
        controller_state = {
            'id' : コントローラー番号,
            'sent_command' : 最後に送信したコマンド,
            'surveillance_count' : 監視回数,
            'latency_count' : 待機回数
        }
        """
        # ファイルの存在可否
        if not os.path.isfile(self.file_path):
            f = open(self.file_path, 'w')
            f.write('')
            f.close()
            
        tree = parse(self.file_path)
        elem = tree.getroot()
        isExist = False
        for e in elem.iter('controller'):
            if e.get('id') == controller_state['id']:
                isExist = True
                e.find('sentCommand').text = controller_state['sent_command']
                e.find('surveillanceCount').text = controller_state['surveillance_count']
                e.find('latencyCount').text = controller_state['latency_count']
                
        # 設定が存在しない場合
        if not isExist:
            controller = SubElement(elem, 'controller', {'id' : controller_state['id']})
            sentCommand = SubElement(controller, 'sentCommand')
            sentCommand.text = controller_state['sent_command']
            surveillanceCount = SubElement(controller, 'surveillanceCount')
            surveillanceCount.text = controller_state['surveillance_count']
            latencyCount = SubElement(controller, 'latencyCount')
            latencyCount.text = controller_state['latency_count']
        
        tree.write(self.file_path, 'UTF-8', xml_declaration=True)

    def all_read(self):
        """
        コントローラー状態を読み込み配列として返します。
        ファイルが存在しない場合はNoneを返します。
        [{'id : コントローラー番号,
          'sent_command' : 最後に送信したコマンド番号,
          'surveillance_count' : 監視回数,
          'latency_count' : 待機回数
        }]
        """
        # ファイルの存在可否
        if not os.path.isfile(self.file_path):
            return None

        tree = parse('../config/controllerState.xml')
        elem = tree.getroot()
        controller_state_list = []
        for e in elem.iter('controller'):
            controller_state_list.append(
                {'id' : e.get('id'),
                 'sent_command' : e.find('sentCommand').text,
                 'surveillance_count' : e.find('surveillanceCount').text,
                 'latency_count' : e.find('latencyCount').text
                })
        return controller_state_list
        
    def read(self, controller_id):
        """
        指定されたコントローラー番号の空調制御状態を辞書で返します。
        ファイルが存在しない場合は作成して空の空調制御状態を返します。
        一致するコントローラーが存在しない場合は空の辞書を返します。
        {'id' : コントローラー番号,
         'sent_command' : 最後に送信したコマンド番号,
         'surveillance_count' : 監視回数,
         'latency_count' : 待機回数
        }
        """
        # ファイルの存在可否
        if not os.path.isfile(self.file_path):
            self.write({'id' : controller_id, 'sent_command' : '0', 'surveillance_count' : '0', 'latency_count' : '0'})
        
        tree = parse('../config/controllerState.xml')
        elem = tree.getroot()
        controller_state_dic = {}
        for e in elem.iter('controller'):
            if controller_id == e.get('id'):
                controller_state_dic['id'] = e.get('id')
                controller_state_dic['sent_command'] = e.find('sentCommand').text
                controller_state_dic['surveillance_count'] = e.find('surveillanceCount').text
                controller_state_dic['latency_count'] = e.find('latencyCount').text
        return controller_state_dic
