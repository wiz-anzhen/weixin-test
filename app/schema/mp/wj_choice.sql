-- TABLE wj_choice

CREATE TABLE IF NOT EXISTS `wj_choice` (
    `wj_choice_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `wj_questionnaire_id` INT(10) NOT NULL COMMENT 'wj_questionnaire.wj_questionnaire_id',
    `wj_question_id` INT(10) NOT NULL COMMENT 'wj_question.wj_question_id',
    `content` TEXT NOT NULL COMMENT '选项内容',
    `comment` TEXT COMMENT '选项描述',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序',
    `select_times` INT(10) NOT NULL DEFAULT 0 COMMENT '被选次数',
    PRIMARY KEY (`wj_choice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='选项，只有选择题才有选项' AUTO_INCREMENT=1;