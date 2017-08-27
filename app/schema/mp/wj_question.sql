-- TABLE wj_question

CREATE TABLE IF NOT EXISTS `wj_question` (
    `wj_question_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `wj_questionnaire_id` INT(10) NOT NULL COMMENT 'wj_questionnaire.wj_questionnaire_id',
    `content` TEXT NOT NULL COMMENT '问题内容',
    `comment` TEXT COMMENT '问题描述',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序',
    `placeholder` VARCHAR(128) COMMENT '输入框提示语',
    `question_type` CHAR(32) NOT NULL DEFAULT 'choice_single' COMMENT '问题类型',
    PRIMARY KEY (`wj_question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题目' AUTO_INCREMENT=1;