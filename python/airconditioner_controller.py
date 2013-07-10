#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
温湿度のロギング、温湿度監視、空調制御コマンドの送信を行うモジュール。
"""

import time
import re
import datetime

from xbee import XBee, ZigBee
cordinator = None

from config.system_config_xml import SystemConfigXML
system_config = SystemConfigXML()
system_config_dict = None

from sensor.temperature_hygro import TemperatureHygro
temp_hygro = TemperatureHygro()

from config.controller_config_xml import ControllerConfigXML
controller_config = ControllerConfigXML()

import logging
import logging.config
logging.config.fileConfig('log.conf')

import Queue
command_queue = Queue.Queue()

from mail.fatal_error_mail import FatalErrorMail

def send_control_command(queue):
    """
    XBeeにデータ送信を行います。
    """
    global cordinator, controller_config
    # コントローラーIDをもとに対象コントローラーの設定を取得
    if not queue.has_key('controller_id'):
        logging.error(u'空調制御コントローラーIDが存在しません。')
        return
    
    controller_list = controller_config.read(queue['controller_id'])
    if controller_list == None:
        logging.error(u'空調制御コントローラー設定ファイルが存在しません。')
        return
        
    if controller_list == {}:
        logging.warn(u'空調制御コントローラー設定が存在しません。')
        return
        
    # 空調制御コマンド送信先XBeeのアドレス取得
    xal = re.split('(..)', controller_list['xbee'])[1::2]
    xbee_addr = chr(int(xal[0], 16)) + chr(int(xal[1], 16)) + chr(int(xal[2], 16)) + chr(int(xal[3], 16)) + chr(int(xal[4], 16)) + chr(int(xal[5], 16)) + chr(int(xal[6], 16)) + chr(int(xal[7], 16))
                
    # 空調制御コマンド送信処理
    logging.debug(u'空調制御コマンド送信: コントローラー番号 ' + queue['controller_id'] + u': 送信コマンド: ' + queue['command'])
    import datetime
    timeout = datetime.datetime.now() + datetime.timedelta(seconds=60)
    while True:
        # 制限時間内にコマンド送信処理が完了しない場合はタイムアウト
        if timeout < datetime.datetime.now():
            logging.warn(u'空調制御コントローラーNo' + controller_list['id'] + u'のコマンド送信処理タイムアウト')
            subject = datetime.datetime.today().strftime('%Y/%m/%d %H:%M:%S 影山様邸 空調制御コントローラーNo' + controller_list['id'] + 'の空調制御コマンド送信処理タイムアウト')
            body = u'空調制御コントローラー故障の可能性があります。\n早めの確認をお願いします。'
            FatalErrorMail().send_mail(system_config_dict, subject, body)
            break
        
        # 空調制御コントローラーとなるXBeeエンドデバイスへコマンド送信
        cordinator.send('tx', dest_addr_long=xbee_addr, dest_addr='\xFF\xFE', data=queue['command'], frame_id='\x10')
        
        # 送信結果が存在しない場合
        result = cordinator.wait_read_frame()
        if not result:
            logging.warn(u'温空調制御コマンド送信結果が存在しません。')
            time.sleep(0.5)
            continue
            
        # 送信結果の確認
        if result.has_key('deliver_status'):
            deliver_status = result['deliver_status']
            char_code =  int(ord(deliver_status))
            logging.info(u'コマンド送信結果:' + str(char_code))
            
            # XBeeエンドデバイスがコマンドを受信した場合
            if char_code == 0:
                #for i in range(1):
                #    cordinator.send('tx', dest_addr_long=xbee_addr, dest_addr='\xFF\xFE', data=queue['command'], frame_id='\x00')
                #    logging.info(u'コマンド送信:' + str(i + 1) + u'回目')
                #    time.sleep(0.3)
                logging.info(u'空調制御コマンドは正常に送信されました。')
                break
            # 空調制御コマンド送信中に温湿度データをロギングした場合
            elif result.has_key('rf_data'):
                temp_hygro.logging_temperature_hygro(result)
                
        time.sleep(0.5)

def check_xbee_voltage():
    """
    XBee電圧値を確認します。
    """
    global cordinator, system_config_dict
    controller_list = controller_config.all_read()
    if controller_list == None:
        logging.error(u'空調制御コントローラー設定ファイルが存在しません。')
        return
    if controller_list == {}:
        logging.warn(u'空調制御コントローラー設定が存在しません。')
        return
    
    for conf in controller_list:
        xal = re.split('(..)', conf['xbee'])[1::2]
        xbee_addr = chr(int(xal[0], 16)) + chr(int(xal[1], 16)) + chr(int(xal[2], 16)) + chr(int(xal[3], 16)) + chr(int(xal[4], 16)) + chr(int(xal[5], 16)) + chr(int(xal[6], 16)) + chr(int(xal[7], 16))

        timeout = datetime.datetime.now() + datetime.timedelta(seconds=120)
        get_supply_num = 0
        while True:
            get_supply_num += 1
            cordinator.send('remote_at', dest_addr_long=xbee_addr, dest_addr='\xFF\xFE', command='%v', frame_id='A')
            logging.debug(u'空調制御コントローラーNo.' + conf['id'] + u'番電圧取得回数:' + str(get_supply_num) + u'回目')
            
            if timeout < datetime.datetime.now():
                logging.warn(u'空調制御コントローラーNo' + conf['id'] + u'の電圧取得処理タイムアウト')
                subject = datetime.datetime.today().strftime('%Y/%m/%d %H:%M:%S 影山様邸 空調制御コントローラーNo' + conf['id'] + 'の電圧取得処理タイムアウト')
                body = u'空調制御コントローラーのバッテリー残量なし若しくはコントローラー故障の可能性があります。\n早めの確認をお願いします。'
                FatalErrorMail().send_mail(system_config_dict, subject, body)
                break
            result = cordinator.wait_read_frame()
            if not result:
                logging.warn(u'温湿度データもしくはXBee電圧取得コマンド送信結果が存在しません。')
                time.sleep(0.5)
                continue

            if result.has_key('parameter'):
                mv = int(hex(ord(result['parameter'][0])).split('0x')[1] + hex(ord(result['parameter'][1])).split('0x')[1], 16)
                logging.warn(u'コントローラーNo.' + conf['id'] + ' = ' + str(mv) + u'mV, 閾値 = ' + system_config_dict['xbee'] + 'V')

                # 取得した電圧値と閾値を比較し、閾値を下回ればメール通知
                if float(system_config_dict['xbee']) * 1000 > float(mv):
                    subject = datetime.datetime.today().strftime('%Y/%m/%d %H:%M:%S 影山様邸 空調制御コントローラー バッテリー残量低下')
                    body = u'空調制御コントローラー' + conf['id'] + u'番のバッテリー残量が低下しています。\n早めのバッテリー交換をお願いします。'
                    FatalErrorMail().send_mail(system_config_dict, subject, body)
                    logging.warn(u'XBee電圧値が低下しています。')
                    
                break
                
            if result.has_key('rf_data'):
                # 温湿度データをロギング
                temp_hygro.logging_temperature_hygro(result)

            time.sleep(0.3)

def main():
    """
    温湿度データのロギング・監視を行い空調を制御します。
    """
    com = None
    
    import sys
    try:
        # システム設定の取得
        global system_config_dict
        system_config_dict = system_config.read()
        if system_config_dict == None:
            system_config_dict = {
                'smtp' : 'smtp.gmoserver.jp',
                'port' : 587,
                'user' : 'smarthouse@bios-net.co.jp',
                'passwd' : 'WKssGLDB',
                'from' : 'smarthouse@bios-net.co.jp',
                'to' : 'yamashita@bios-net.co.jp',
                'xbee': '2.0'
            }
            
            subject = datetime.datetime.today().strftime('%Y/%m/%d %H:%M:%S 影山様邸 空調自動制御システム異常')
            body = u'空調自動制御システムが停止しました。\n\nメッセージ\nシステム設定ファイルが存在しません。'
            FatalErrorMail().send_mail(system_config_dict, subject, body)
            
            logging.error(u'システム設定ファイルが存在しません。')
            sys.exit(u'システム設定ファイルが存在しません。')
        
        # コーディネータと接続
        import serial
        com = serial.Serial(port=system_config_dict['cordinator'], baudrate=9600, timeout=1, bytesize=8, parity='N')
        global cordinator
        cordinator = ZigBee(com)
        
        # 温湿度データの監視
        from surveillance.temperature_surveillance import TemperatureSurveillance
        temp_surveillance = TemperatureSurveillance(command_queue)
        temp_surveillance.start()
        
        # ソケット通信による空調制御コマンド受信
        from socket.socket_command import ScoketCommand
        socket_command = ScoketCommand(command_queue)
        socket_command.start()
        
        # XBee電圧チェック時間
        check_xbee_voltage_time = datetime.datetime.now()
        
        while True:
            # シリアル通信でデータ受信時、温湿度データをロギング
            if com.inWaiting() > 0:
                recv_data = cordinator.wait_read_frame()
                temp_hygro.logging_temperature_hygro(recv_data)
                    
            # 空調制御コマンドキューにコマンドが存在すれば送信
            if not command_queue.empty():
                queue = command_queue.get()
                send_control_command(queue)
            
            # 1日1回空調制御コントローラーXBeeの電圧確認
            now = datetime.datetime.now()
            if check_xbee_voltage_time <= now:
                check_xbee_voltage()
                
                # 日付を加算
                one_day = datetime.timedelta(days=1)
                check_xbee_voltage_time = now + one_day
                
            time.sleep(0.1)
    except KeyboardInterrupt:
        logging.warn(u'キーボード操作により処理を停止します。')
    except:
        logging.error(sys.exc_info()[:2])
        import traceback
        logging.error(traceback.extract_tb(sys.exc_info()[2]))

        subject = datetime.datetime.today().strftime('%Y/%m/%d %H:%M:%S 影山様邸 空調自動制御システム異常')
        body = u'空調自動制御システムが停止しました。\n\nメッセージ\n' + str(traceback.extract_tb(sys.exc_info()[2]))
        FatalErrorMail().send_mail(system_config_dict, subject, body)
    finally:
        if cordinator != None:
            cordinator.halt()
        if com  != None:
            com.close()

if __name__ == '__main__':
	main()
