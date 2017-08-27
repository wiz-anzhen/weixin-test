-- TABLE app_user

CREATE TABLE IF NOT EXISTS `app_user` (
    `create_time` DATETIME NOT NULL COMMENT '创建时间',
    `vip_no` BIGINT(20) NOT NULL DEFAULT 0 COMMENT '会员号',
    `phone` CHAR(32) COMMENT '注册用户登录手机号',
    `password` CHAR(32) NOT NULL COMMENT '注册用户登录密码',
    `is_admin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否是管理员',
    `nick` CHAR(64) COMMENT '昵称',
    `name` VARCHAR(128) COMMENT '姓名',
    `last_access_ymd` INT(10) NOT NULL DEFAULT 0 COMMENT '上次访问日期',
    `comment` VARCHAR(128) COMMENT '备注',
    `idiograph` VARCHAR(255) COMMENT '个人签名',
    `province` VARCHAR(128) COMMENT '所在省份',
    `city` CHAR(64) COMMENT '所在城市',
    `community_name` VARCHAR(255) COMMENT '社区名字',
    `address` VARCHAR(255) COMMENT '地址',
    `birth` INT(10) COMMENT '生日',
    `gender` CHAR(6) COMMENT '性别',
    `head_pic` VARCHAR(1024) COMMENT '头像',
    `message_date` CHAR(16) DEFAULT 00000000 COMMENT '消息日期',
    `card_id` VARCHAR(255) COMMENT '卡号',
    `email` VARCHAR(255) COMMENT '邮箱地址',
    `current_community_id` INT(10) NOT NULL DEFAULT 0 COMMENT 'community.community_id',
    `latitudeUser` VARCHAR(255) COMMENT '纬度',
    `longitudeUser` VARCHAR(255) COMMENT '经度',
    `is_receive_message` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否接收消息',
    `baidu_user_id` CHAR(32) COMMENT '百度userid',
    `baidu_channel_id` CHAR(32) COMMENT '百度channelid',
    `last_access` DATETIME COMMENT '上次访问时间',
    `is_quit` TINYINT(1) DEFAULT 0 COMMENT '是否退出',
    PRIMARY KEY (`create_time`),
    UNIQUE KEY `uk_app_user_vip_no` (`vip_no`),
    KEY `ak_app_user_phone` (`phone`),
    KEY `ak_app_user_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='普通App用户';