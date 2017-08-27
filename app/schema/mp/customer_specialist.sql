-- TABLE customer_specialist

CREATE TABLE IF NOT EXISTS `customer_specialist` (
    `customer_specialist_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `staff_id` CHAR(32) NOT NULL COMMENT '工号',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `vip_no` BIGINT(20) DEFAULT 0 COMMENT '会员号',
    `wx_user_id` CHAR(64) COMMENT '微信用户OpenID',
    `customer_specialist_group_id` INT(10) NOT NULL COMMENT 'customer_specialist_group.customer_specialist_group_id',
    `name` CHAR(64) NOT NULL COMMENT '姓名',
    `phone` CHAR(64) NOT NULL COMMENT '电话号码',
    `comment` VARCHAR(128) COMMENT '备注',
    `holiday` TEXT COMMENT '休假日期',
    `valid` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否有效',
    PRIMARY KEY (`customer_specialist_id`),
    UNIQUE KEY `uk_customer_specialist_ukey` (`staff_id`,`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客户专员' AUTO_INCREMENT=1;