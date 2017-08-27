-- TABLE procurement_order_change_log

CREATE TABLE IF NOT EXISTS `procurement_order_change_log` (
    `procurement_order_change_log_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `order_id` CHAR(32) NOT NULL COMMENT '订单号',
    `operator` VARCHAR(128) NOT NULL COMMENT '操作人',
    `change_time` DATETIME NOT NULL COMMENT '操作时间',
    `comment` VARCHAR(255) NOT NULL COMMENT '备注',
    `status_before` CHAR(32) NOT NULL DEFAULT 'none' COMMENT '变更前状态',
    `status_after` CHAR(32) NOT NULL DEFAULT 'none' COMMENT '变更后状态',
    PRIMARY KEY (`procurement_order_change_log_id`),
    KEY `ak_procurement_order_change_log_order_id` (`order_id`),
    KEY `ak_procurement_order_change_log_change_time` (`change_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单变更记录' AUTO_INCREMENT=1;