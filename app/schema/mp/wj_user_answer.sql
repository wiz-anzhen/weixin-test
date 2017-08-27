-- TABLE wj_user_answer

CREATE TABLE IF NOT EXISTS `wj_user_answer` (
    `wj_user_answer_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `wx_user_id` CHAR(64) COMMENT 'wx_user.wx_user_id',
    `name` CHAR(64) COMMENT '答题人姓名',
    `gender` CHAR(64) COMMENT '性别',
    `tel` CHAR(64) COMMENT '手机',
    `birth` CHAR(64) COMMENT '出生日期',
    `email` CHAR(64) COMMENT '电子邮件',
    `wj_questionnaire_id` INT(10) COMMENT 'wj_questionnaire.wj_questionnaire_id',
    `answer` TEXT COMMENT '问答题直接保存，选择题逗号分隔',
    PRIMARY KEY (`wj_user_answer_id`),
    KEY `ak_wj_user_answer_community_id` (`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户答卷记录表' AUTO_INCREMENT=1;