-- TABLE company_admin

CREATE TABLE IF NOT EXISTS `company_admin` (
    `company_admin_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `username` VARCHAR(128) NOT NULL COMMENT 'user.username',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `mp_name` VARCHAR(1024) COMMENT 'mp_user.mp_name',
    `power` VARCHAR(1024) COMMENT '权限',
    `comment` TEXT COMMENT '备注',
    PRIMARY KEY (`company_admin_id`),
    KEY `ak_company_admin_username` (`username`),
    KEY `ak_company_admin_mp_user_id` (`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众帐号公司管理员，多对多关系' AUTO_INCREMENT=1;