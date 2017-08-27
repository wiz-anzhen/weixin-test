-- TABLE cart

CREATE TABLE IF NOT EXISTS `cart` (
    `cart_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `store_id` INT(10) NOT NULL COMMENT '商城ID',
    `wx_user_id` CHAR(64) NOT NULL COMMENT '微信用户ID',
    PRIMARY KEY (`cart_id`),
    UNIQUE KEY `uk_cart_wxUserId_storeId` (`mp_user_id`,`store_id`,`wx_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购物车' AUTO_INCREMENT=1;