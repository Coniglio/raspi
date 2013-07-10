#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
警戒値の判定を行うモジュール
"""

from threshold_judge import ThresholdJudge

class CautionValueJudge(ThresholdJudge):
    """
    警戒値の判定を行うクラス
    """
    
    def __init__(self):
        ThresholdJudge.__init__(self)
        
    def judge(self, temp, hygro, max_temp, min_temp, max_hygro, min_hygro):
        if max_temp != 999.0:
            if self.is_over_threshold(temp, max_temp):
                return True
        
        if min_temp != 999.0:
            if self.is_under_threshold(temp, min_temp):
                return True
        
        if max_hygro != 999.0:
            if self.is_over_threshold(hygro, max_hygro):
                return True
                
        if min_hygro != 999.0:
            if self.is_under_threshold(hygro, min_hygro):
                return True
    
