-- TABLE total_user

CREATE TABLE IF NOT EXISTS `total_user` (
    `total_user_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `total_user_num` INT(10) NOT NULL COMMENT '有效公众号用户总数',
    `active_user_num` INT(10) NOT NULL COMMENT '活跃用户数',
    `insert_time` DATETIME NOT NULL COMMENT '统计时间',
    `insert_hour` INT(10) NOT NULL COMMENT '统计时间段',
    PRIMARY KEY (`total_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户总数活跃用户数统计表每小时统计' AUTO_INCREMENT=1;