#/bin/python

# -*- coding: utf-8 -*-

"""
温湿度メールを扱うモジュール
"""

from base_mail import BaseMail

class TemperatureMail(BaseMail):
    """
    温湿度メールを扱うクラス
    """

    def __init__(self):
        BaseMail.__init__(self)

    def send_mail(self, system_config, subject, body):
        """
        温湿度メールを送信します
        """
        msg = self.create(subject, body, system_config['from'], system_config['to'], 'utf-8')
        self.send(msg, system_config)

    def avg(self, sum, len):
        """
        温湿度の平均、最大、最小値を返します
        """
        return 0 if sum == 0.0 else sum / len

    def get_aggregation(self, file_path):
        """
        温湿度データの集合を返します
        """
        import csv
        if not os.path.isfile(file_path):
            return  '温湿度データが存在しません。\n'

        temp_list = []
        humi_list = []
        with open(file_path, 'r') as f:
            temp_list = [float(row[1]) for row in csv.reader(f) if not len(row) == 1]
        with open(file_path, 'r') as f:
            humi_list = [float(row[2]) for row in csv.reader(f) if not len(row) == 1]

        return '集計可能な温湿度データが存在しません。\n\n' if 0 == len(temp_list) else '　　 温度　　湿度\n------------------\n MAX ' + str(max(temp_list)) + '℃　' + str(max(humi_list)) + '%\n MIN ' + str(min(temp_list)) + '℃　' + str(min(humi_list)) + '%\n AVG ' + ('%1.1f' % (self.avg(sum(temp_list), len(temp_list)))) + '℃　' + ('%1.1f' % self.avg(sum(humi_list), len(humi_list))) + '%\n\n'

    def read_config(self):
        """
        システム設定を読み込みます
        """
        from system_config_xml import SystemConfigXML
        system_config = SystemConfigXML().read()
        if system_config == {}:
            pass

        return system_config

