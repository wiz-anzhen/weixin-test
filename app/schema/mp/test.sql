-- TABLE test

CREATE TABLE IF NOT EXISTS `test` (
    `test_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `power` CHAR(32) DEFAULT 'channel_r' COMMENT 'power',
    PRIMARY KEY (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='仅用来生成一些枚举类型的代码' AUTO_INCREMENT=1;