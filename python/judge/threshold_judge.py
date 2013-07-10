#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
閾値の判定を行うモジュール
"""

class ThresholdJudge():
    """
    閾値の判定を行うクラス
    """
    
    def __init__(self):
        pass

    def is_over_threshold(self, val, threshold):
        """
        閾値よりも大きいか否か
        """
        return val > threshold
        
    def is_under_threshold(self, val, threshold):
        """
        閾値よりも小さいか否か
        """
        return val < threshold
