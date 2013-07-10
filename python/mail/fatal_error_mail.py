#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
死活監視メールを扱うモジュール
"""

import smtplib
import base64
from email.MIMEText import MIMEText
from email import Encoders
from email.MIMEBase import MIMEBase
from email.MIMEMultipart import MIMEMultipart
from email.Utils import formatdate
from email.Header import Header
from base_mail import BaseMail

class FatalErrorMail(BaseMail):
    """
    死活監視メールを扱うクラス
    """

    def __init__(self):
        BaseMail.__init__(self)
        
    def send_mail(self, system_config, subject, body):
        """
        死活監視メールを送信します。
        """
        msg = self.create(subject, body, system_config['from'], system_config['to'], 'utf-8')

        self.send(msg, system_config)
