-- TABLE urgent_notice_read_record

CREATE TABLE IF NOT EXISTS `urgent_notice_read_record` (
    `urgent_notice_read_record_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `channel_article_id` INT(10) NOT NULL COMMENT 'channel_article.channel_article_id',
    `wx_user_id` CHAR(64) NOT NULL COMMENT '微信用户OpenID',
    PRIMARY KEY (`urgent_notice_read_record_id`),
    UNIQUE KEY `uk_urgent_notice_read_record_ukey` (`channel_article_id`,`wx_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='紧急通知读取记录' AUTO_INCREMENT=1;