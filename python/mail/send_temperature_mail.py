#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
温湿度メールを扱うモジュール
"""

def main():
    import sys
    sys.path.append('../config')

    from temperature_mail import TemperatureMail
    temp_mail = TemperatureMail()

    # システム設定を取得
    system_config = temp_mail.read_config()

    # 件名、本文を生成
    import datetime
    date = datetime.datetime.today() - datetime.timedelta(days=1)
    subject = date.strftime('%Y/%m/%d 影山様邸 温湿度データ')
    body = subject + 'です。\n\n'

    # 温湿度センサー設定を取得
    from templogger_config_xml import TempLoggerConfigXML
    templogger_config = TempLoggerConfigXML()
    templogger_config_list = templogger_config.read()

    attachment_list = []
    for sensor in templogger_config_list:
        sensor_id = sensor['id']
        attach_file_name = sensor['name'].encode('utf-8') + '.csv'
        file_path = date.strftime('../log/TempHygro/%Y/sensor' + sensor_id + '/' + sensor_id + '-%m%d.csv')
        body += sensor['name'].encode('utf-8') + '\n'
        body += temp_mail.get_aggregation(file_path)
        attachment_list.append([attach_file_name, file_path])

    msg = temp_mail.create_message(subject, body, system_config['from'], system_config['to'], 'utf-8', attachment_list)

    temp_mail.send(msg, system_config)

if __name__ == '__main__':
    main()
