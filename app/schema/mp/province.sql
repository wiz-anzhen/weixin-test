-- TABLE province

CREATE TABLE IF NOT EXISTS `province` (
    `province_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` CHAR(16) NOT NULL COMMENT '省份名称',
    `created` BIGINT(11) COMMENT 'mp.province.created',
    `updated` BIGINT(11) COMMENT 'mp.province.updated',
    PRIMARY KEY (`province_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='省份表' AUTO_INCREMENT=1;