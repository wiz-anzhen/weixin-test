-- TABLE community

CREATE TABLE IF NOT EXISTS `community` (
    `community_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `_updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'UpdatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user_id',
    `name` CHAR(64) NOT NULL COMMENT '社区名称',
    `phone` VARCHAR(128) COMMENT '热线电话',
    `admin_email` TEXT COMMENT '主送管理员email',
    `admin_cc_email` TEXT COMMENT '抄送管理员email',
    `comment` VARCHAR(128) COMMENT '备注',
    `is_virtual` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否是虚拟社区',
    `is_app` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否应用到APP',
    `valid` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否有效',
    `bill_comment` TEXT COMMENT '收费通知单提示',
    `bill_name` VARCHAR(128) COMMENT '收费通知单名称',
    `address` VARCHAR(128) COMMENT '详细地址',
    `province` VARCHAR(128) COMMENT '所在省份',
    `city` VARCHAR(128) COMMENT '所在城市',
    `area` VARCHAR(128) COMMENT '所在区/县',
    `longitude` CHAR(64) COMMENT '精度',
    `latitude` CHAR(64) COMMENT '纬度',
    `community_type` CHAR(32) DEFAULT 'none' COMMENT '社区属性',
    PRIMARY KEY (`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='社区' AUTO_INCREMENT=1;