-- TABLE order

CREATE TABLE IF NOT EXISTS `order` (
    `order_id` CHAR(32) NOT NULL COMMENT '订单号',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `wx_user_id` CHAR(64) NOT NULL COMMENT '微信用户ID',
    `comment` VARCHAR(255) COMMENT '备注',
    `customer_name` CHAR(64) COMMENT '客户姓名',
    `tel` CHAR(64) COMMENT '电话',
    `address` VARCHAR(255) COMMENT '地址',
    `total_price` DECIMAL(15,2) NOT NULL COMMENT '订单总价',
    `total_num` INT(10) NOT NULL DEFAULT 1 COMMENT '订单总数量',
    `create_time` DATETIME NOT NULL COMMENT '订单创建时间',
    `finish_time` DATETIME COMMENT '交易完成时间',
    `cs_id` INT(10) COMMENT '客服专员id',
    `cs_group_id` INT(10) COMMENT '客服专员分组id',
    `pay_finished` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '支付状态',
    `store_type` CHAR(32) COMMENT '商城类型',
    `status` CHAR(32) NOT NULL DEFAULT 'default_status' COMMENT '交易状态',
    `pay_method` CHAR(32) DEFAULT 'cash_pay' COMMENT '付款方式',
    `reason` CHAR(32) DEFAULT 'option_first' COMMENT '原因',
    PRIMARY KEY (`order_id`),
    KEY `ak_order_community_id` (`community_id`),
    KEY `ak_order_create_time` (`create_time`),
    KEY `ak_order_finish_time` (`finish_time`),
    KEY `ak_order_cs_id` (`cs_id`),
    KEY `ak_order_cs_group_id` (`cs_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单';