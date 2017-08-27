#!/bin/bash

# 清空cache文件
rm -rf /kingcores/www/spm.weibotui.com/cache/*
if [ $? -ne 0 ]
then
    echo "failed to rm cache files. exit 1"
    exit 1
fi

