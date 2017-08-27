-- TABLE procurement_order

CREATE TABLE IF NOT EXISTS `procurement_order` (
    `order_id` CHAR(32) NOT NULL COMMENT '订单号',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `wx_user_id` CHAR(64) NOT NULL COMMENT '微信用户ID',
    `comment` VARCHAR(255) COMMENT '备注',
    `customer_name` CHAR(64) COMMENT '下单者',
    `tel` CHAR(64) COMMENT '电话',
    `address` VARCHAR(255) COMMENT '地址',
    `total_price` DECIMAL(15,2) NOT NULL COMMENT '订单总价',
    `total_num` INT(10) NOT NULL DEFAULT 1 COMMENT '订单总数量',
    `create_time` DATETIME NOT NULL COMMENT '订单创建时间',
    `finish_time` DATETIME COMMENT '交易完成时间',
    `bound_community_id` INT(10) COMMENT '绑定供应商id',
    `bound_store_id` INT(10) COMMENT '绑定供应商商城id',
    `store_id` INT(10) COMMENT '商城id',
    `category_id` INT(10) COMMENT '报价单id',
    `refund_order_id` CHAR(32) COMMENT '退款退货原订单号',
    `refund_describe` TEXT COMMENT '退款退货原因描述',
    `refund_img_first` VARCHAR(1024) COMMENT '退款退货图片地址1',
    `refund_img_second` VARCHAR(1024) COMMENT '退款退货图片地址2',
    `refund_img_third` VARCHAR(1024) COMMENT '退款退货图片地址3',
    `order_self` CHAR(32) DEFAULT 'order_supply' COMMENT '是否是自订',
    `status` CHAR(32) NOT NULL DEFAULT 'none' COMMENT '交易状态',
    PRIMARY KEY (`order_id`),
    KEY `ak_procurement_order_community_id` (`community_id`),
    KEY `ak_procurement_order_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单';