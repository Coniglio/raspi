#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
警告メールを扱うモジュール
"""

import smtplib
import datetime
import base64
from email.MIMEText import MIMEText
from email import Encoders
from email.MIMEBase import MIMEBase
from email.MIMEMultipart import MIMEMultipart
from email.Utils import formatdate
from email.Header import Header
from base_mail import BaseMail

class TemperatureAlertMail(BaseMail):
    """
    警告メールを扱うクラス
    """

    def __init__(self):
        BaseMail.__init__(self)

    def read_config(self):
        """
        システム設定を読み込みます。
        """
        from system_config_xml import SystemConfigXML
        system_config = SystemConfigXML().read()
        if system_config == {}:
            pass
            
        return system_config
        
    def send_alert_mail(self, temp_list, sensor_id, sensor_name, kind):
        """
        警告メールを送信します。
        """
        system_config = self.read_config()
        date = datetime.datetime.today()
        subject = date.strftime('%Y/%m/%d 影山様邸 温湿度異常')
        body = kind + u'を超えたため、\nスマートフォンからの制御をお願いいたします。\n\n' + sensor_name + u'\n------------------------\n温度:' + str(temp_list[1]) + u'℃\n湿度:' + temp_list[2].rstrip() + u'%\nhttp://110.5.3.178:8080/html/tempLogger.html?sensorId=' + sensor_id + '&date=' + date.strftime('%Y/%m/%d')
        msg = self.create(subject, body, system_config['from'], system_config['to'], 'utf-8')
        self.send(msg, system_config)
