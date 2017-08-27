-- TABLE cart_detail

CREATE TABLE IF NOT EXISTS `cart_detail` (
    `cart_detail_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `cart_id` CHAR(32) NOT NULL COMMENT '购物车ID',
    `product_id` INT(10) NOT NULL COMMENT '产品ID',
    `count` FLOAT NOT NULL DEFAULT 1 COMMENT '数量',
    PRIMARY KEY (`cart_detail_id`),
    UNIQUE KEY `uk_cart_detail_cart_product_id` (`cart_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购物车详情' AUTO_INCREMENT=1;