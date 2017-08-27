-- TABLE wx_pay_record

CREATE TABLE IF NOT EXISTS `wx_pay_record` (
    `wx_pay_record_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `order_id` BIGINT(20) NOT NULL COMMENT '订单号',
    `wx_user_id` CHAR(64) NOT NULL COMMENT '微信用户ID',
    `username` VARCHAR(128) NOT NULL COMMENT '用户姓名',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `outTradeNo` VARCHAR(128) COMMENT '微信商户订单号',
    `transactionId` VARCHAR(128) COMMENT '微信支付单号',
    `pay_iterm` VARCHAR(128) NOT NULL COMMENT '付款项目',
    `pay_start_date` DATETIME COMMENT '订单开始时间',
    `pay_end_date` DATETIME COMMENT '订单完成时间',
    `pay_value` DECIMAL(15,2) NOT NULL DEFAULT 1 COMMENT '支付金额',
    `pay_finished` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否完成支付',
    `mark` TEXT COMMENT '备注信息',
    `pay_method` CHAR(32) NOT NULL DEFAULT 'cash_pay' COMMENT '付款方式',
    PRIMARY KEY (`wx_pay_record_id`),
    UNIQUE KEY `uk_wx_pay_record_order_id` (`order_id`),
    KEY `ak_wx_pay_record_username` (`username`),
    KEY `ak_wx_pay_record_outTradeNo` (`outTradeNo`),
    KEY `ak_wx_pay_record_transactionId` (`transactionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='支付记录' AUTO_INCREMENT=1;