#!/bin/bash

##########################################################################
# 一般情况下不要修改此文件
# 需要修改时，请注意先单独更新服务器上的本文件，避免执行git pull时被更新
#########################################################################

# 改变当前路径到 release.sh 所在文件夹
cd "$(dirname "$0")";

# 打印当前路径
echo "current dir:"
pwd
echo ""

# 执行更新代码前的脚本 
echo "start run before_gitpull.sh"
sh -x before_gitpull.sh
if [ $? -ne 0 ]
then
    echo "sh before_gitpull.sh failed. exit 1"
    exit 1
else
    echo "before_gitpull.sh run over."
    echo ""
fi

# 从github更新代码，需要设置git，使得执行git pull时无需输入密码
# 如果分支发生变化，需要在服务器上手动切换分支
echo "start run git pull"
git pull
if [ $? -ne 0 ]
then
    echo "git pull  failed. exit 1"
    exit 1
else
    echo "git pull run over."
    echo ""
fi

# 执行更新代码后的脚本
echo "start run after_gitpull.sh"
sh -x after_gitpull.sh
if [ $? -ne 0 ]
then
    echo "sh after_gitpull.sh failed. exit 1"
    exit 1
else
    echo "after_gitpull.sh run over."
    echo "release success."
fi

