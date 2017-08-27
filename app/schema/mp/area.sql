-- TABLE area

CREATE TABLE IF NOT EXISTS `area` (
    `area_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` VARCHAR(128) NOT NULL COMMENT '城区名称',
    `city_id` BIGINT(11) NOT NULL COMMENT '所属城市编号',
    `created` BIGINT(11) COMMENT 'mp.area.created',
    `updated` BIGINT(11) COMMENT 'mp.area.updated',
    PRIMARY KEY (`area_id`),
    KEY `ak_area_city_id` (`city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='城区表' AUTO_INCREMENT=1;