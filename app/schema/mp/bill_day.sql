-- TABLE bill_day

CREATE TABLE IF NOT EXISTS `bill_day` (
    `bill_day_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `bill_day` INT(10) NOT NULL COMMENT '账单日期',
    PRIMARY KEY (`bill_day_id`),
    UNIQUE KEY `uk_bill_day_ukey` (`community_id`,`bill_day`),
    KEY `ak_bill_day` (`bill_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='账单日期表' AUTO_INCREMENT=1;