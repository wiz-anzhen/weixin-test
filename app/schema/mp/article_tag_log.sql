-- TABLE article_tag_log

CREATE TABLE IF NOT EXISTS `article_tag_log` (
    `article_tag_log_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `tag` CHAR(64) NOT NULL COMMENT '标签',
    `wx_user_id` CHAR(64) COMMENT '用户微信open_id',
    PRIMARY KEY (`article_tag_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签访问记录' AUTO_INCREMENT=1;