-- TABLE procurement_order_change_detail

CREATE TABLE IF NOT EXISTS `procurement_order_change_detail` (
    `procurement_order_change_detail_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `order_id` CHAR(32) NOT NULL COMMENT '订单号',
    `product_id` INT(10) NOT NULL COMMENT '产品ID',
    `img_url` VARCHAR(1024) COMMENT '图片地址',
    `price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '价格',
    `title` VARCHAR(128) NOT NULL COMMENT '产品名称',
    `description` VARCHAR(128) COMMENT '产品描述',
    `count` FLOAT COMMENT '数量',
    `chef_count` FLOAT DEFAULT 0 COMMENT '订货员改变次数',
    `part_id` INT(10) NOT NULL COMMENT '档口ID',
    `product_unit` CHAR(32) DEFAULT 'kilo' COMMENT '单位',
    `status` CHAR(32) NOT NULL DEFAULT 'none' COMMENT '交易状态',
    PRIMARY KEY (`procurement_order_change_detail_id`),
    KEY `ak_procurement_order_change_detail_order_id` (`order_id`),
    KEY `ak_procurement_order_change_detail_status` (`status`),
    KEY `ak_procurement_order_change_detail_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单详情变化' AUTO_INCREMENT=1;