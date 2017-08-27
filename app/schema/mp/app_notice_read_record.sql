-- TABLE app_notice_read_record

CREATE TABLE IF NOT EXISTS `app_notice_read_record` (
    `app_notice_read_record_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `app_article_id` INT(10) NOT NULL COMMENT 'article_id',
    `app_phone` CHAR(64) NOT NULL COMMENT 'app注册手机号',
    `notice_read_time` INT(10) NOT NULL COMMENT '年月日，示例20141229',
    PRIMARY KEY (`app_notice_read_record_id`),
    UNIQUE KEY `uk_app_notice_read_record_ukey` (`app_article_id`,`app_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='app消息读取记录' AUTO_INCREMENT=1;