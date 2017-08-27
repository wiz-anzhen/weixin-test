-- TABLE owner_lessee

CREATE TABLE IF NOT EXISTS `owner_lessee` (
    `owner_lessee_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `lessee_wx_user_id` CHAR(64) NOT NULL COMMENT 'lesseeOpenID',
    `owner_wx_user_id` CHAR(64) NOT NULL COMMENT 'ownerOpenID',
    PRIMARY KEY (`owner_lessee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='业主帮助用户认证记录' AUTO_INCREMENT=1;