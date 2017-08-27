-- TABLE bill

CREATE TABLE IF NOT EXISTS `bill` (
    `bill_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `bill_day` INT(10) NOT NULL COMMENT '账单日期',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `house_no` CHAR(64) COMMENT '房间编号',
    `name` CHAR(32) COMMENT '业主姓名',
    `phone` CHAR(64) COMMENT '业主联系电话',
    `house_address` VARCHAR(255) NOT NULL COMMENT '地址',
    `house_area` DECIMAL(15,2) COMMENT '面积',
    `total_payment` DECIMAL(15,2) NOT NULL COMMENT '累计应缴合计',
    `read_time` DATETIME COMMENT '阅读时间',
    `pay_finished` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否完成支付',
    `bill_pay_method` CHAR(32) DEFAULT 'other' COMMENT '付款方式',
    PRIMARY KEY (`bill_id`),
    UNIQUE KEY `uk_bill_ukey` (`house_address`,`community_id`,`bill_day`),
    KEY `ak_bill_day` (`bill_day`),
    KEY `ak_bill_read_time` (`read_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收费通知单明细' AUTO_INCREMENT=1;