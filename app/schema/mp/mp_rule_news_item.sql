-- TABLE mp_rule_news_item

CREATE TABLE IF NOT EXISTS `mp_rule_news_item` (
    `mp_rule_news_item_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL DEFAULT 0 COMMENT 'mp_user.mp_user_id',
    `title` TEXT NOT NULL COMMENT '标题',
    `description` TEXT COMMENT '描述',
    `pic_url` VARCHAR(1024) COMMENT '图片url',
    `url` VARCHAR(1024) COMMENT '图文消息url',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序id',
    `top_dir_no` INT(10) COMMENT '一级目录编号',
    PRIMARY KEY (`mp_rule_news_item_id`),
    KEY `ak_mp_rule_news_item_mp_user_id` (`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='保存图文消息元信息' AUTO_INCREMENT=1;