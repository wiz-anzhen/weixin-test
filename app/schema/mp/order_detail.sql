-- TABLE order_detail

CREATE TABLE IF NOT EXISTS `order_detail` (
    `order_detail_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `order_id` CHAR(32) NOT NULL COMMENT '订单号',
    `product_id` INT(10) NOT NULL COMMENT '产品ID',
    `img_url` VARCHAR(1024) COMMENT '图片地址',
    `price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '价格',
    `title` VARCHAR(128) NOT NULL COMMENT '产品名称',
    `description` VARCHAR(128) COMMENT '产品描述',
    `count` FLOAT NOT NULL DEFAULT 1 COMMENT '数量',
    `refund` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否退货',
    `product_unit` CHAR(32) DEFAULT 'kilo' COMMENT '单位',
    PRIMARY KEY (`order_detail_id`),
    UNIQUE KEY `uk_order_detail_order_product_id` (`order_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单详情' AUTO_INCREMENT=1;