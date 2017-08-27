-- TABLE product_comment

CREATE TABLE IF NOT EXISTS `product_comment` (
    `product_comment_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `wx_user_id` CHAR(64) NOT NULL COMMENT 'wx_user.wx_user_id',
    `community_id` INT(10) NOT NULL COMMENT '‘community.community_id’',
    `order_id` CHAR(32) NOT NULL COMMENT '订单号',
    `nick` CHAR(64) COMMENT '称呼',
    `head_pic` VARCHAR(1024) COMMENT '头像',
    `product_id` INT(10) NOT NULL COMMENT '产品ID',
    `product_title` VARCHAR(128) NOT NULL COMMENT '产品名称',
    `order_finish_time` DATETIME COMMENT '购买时间',
    `comment_time` DATETIME COMMENT '评论时间',
    `comment_level` TINYINT(1) COMMENT '评论星数',
    `comment` TEXT COMMENT '评论内容',
    PRIMARY KEY (`product_comment_id`),
    KEY `ak_product_comment_product_id` (`product_id`,`wx_user_id`),
    KEY `ak_product_comment_time` (`comment_time`),
    KEY `ak_product_comment_community_id` (`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品评论' AUTO_INCREMENT=1;