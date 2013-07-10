#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
システム設定を扱うクラス(XML形式)
"""

class SystemConfigXML():
    """
    システム設定を扱うクラス(XML形式)
    """
    def __init__(self):
        self.file_path = '../../config/systemConfig.xml'

    def read(self):
        """
        システムを読み込み辞書形式で返します。
        システム設定ファイルが存在しない場合はNoneを返します。
        return システム設定
        {'from' : メール送信元,
        'to' : メール送信先,
        'smtp' : SMTPサーバホスト名,
        'port' : SMTPサーバポート番号,
        'user' : メール送信元ユーザ名,
        'passwd' : メール送信元パスワード,
        'cordinator' : コーディネータCOMポート,
        'irReceive' : 赤外線受信機COMポート,
        'xbee' : XBee電圧閾値
        }
        """
        # ファイルの存在可否
        import os
        if not os.path.isfile(self.file_path):
            return None

        from xml.etree.ElementTree import *
        tree = parse(self.file_path)
        elem = tree.getroot()
        mail = elem.find('mail')
        weather = elem.find('weather')
        system_config = {}
        system_config['from'] = mail.find('from').text
        system_config['to'] = mail.find('to').text
        system_config['smtp'] = mail.find('smtp').text
        system_config['port'] = mail.find('port').text
        system_config['user'] = mail.find('user').text
        system_config['passwd'] = mail.find('passwd').text
        system_config['cordinator'] = elem.find('cordinator').text
        system_config['irReceive'] = elem.find('irReceive').text
        system_config['xbee'] = elem.find('xbee').text

        return system_config

