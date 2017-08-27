SET @@foreign_key_checks = 0;

-- TABLE super_admin
TRUNCATE TABLE `super_admin`;

-- TABLE mp_admin
TRUNCATE TABLE `mp_admin`;

-- TABLE mp_user
TRUNCATE TABLE `mp_user`;

-- TABLE mp_fans_count
TRUNCATE TABLE `mp_fans_count`;

-- TABLE mp_user_nav
TRUNCATE TABLE `mp_user_nav`;

-- TABLE mp_article
TRUNCATE TABLE `mp_article`;

-- TABLE mp_article_daily_traffic
TRUNCATE TABLE `mp_article_daily_traffic`;

-- TABLE wx_user
TRUNCATE TABLE `wx_user`;

-- TABLE app_user
TRUNCATE TABLE `app_user`;

-- TABLE pc_user
TRUNCATE TABLE `pc_user`;

-- TABLE wx_user_ext
TRUNCATE TABLE `wx_user_ext`;

-- TABLE mp_user_config
TRUNCATE TABLE `mp_user_config`;

-- TABLE wx_user_feedback
TRUNCATE TABLE `wx_user_feedback`;

-- TABLE test
TRUNCATE TABLE `test`;

-- TABLE test_test
TRUNCATE TABLE `test_test`;

-- TABLE report
TRUNCATE TABLE `report`;

-- TABLE like_article
TRUNCATE TABLE `like_article`;

-- TABLE article_comment
TRUNCATE TABLE `article_comment`;

-- TABLE wj_questionnaire
TRUNCATE TABLE `wj_questionnaire`;

-- TABLE wj_question
TRUNCATE TABLE `wj_question`;

-- TABLE wj_choice
TRUNCATE TABLE `wj_choice`;

-- TABLE wj_user_answer
TRUNCATE TABLE `wj_user_answer`;

-- TABLE top_directory
TRUNCATE TABLE `top_directory`;

-- TABLE directory
TRUNCATE TABLE `directory`;

-- TABLE directory_daily_traffic
TRUNCATE TABLE `directory_daily_traffic`;

-- TABLE directory_wx_user_visit
TRUNCATE TABLE `directory_wx_user_visit`;

-- TABLE carousel
TRUNCATE TABLE `carousel`;

-- TABLE album
TRUNCATE TABLE `album`;

-- TABLE picture
TRUNCATE TABLE `picture`;

-- TABLE channel
TRUNCATE TABLE `channel`;

-- TABLE channel_article
TRUNCATE TABLE `channel_article`;

-- TABLE article_tag_log
TRUNCATE TABLE `article_tag_log`;

-- TABLE urgent_notice_read_record
TRUNCATE TABLE `urgent_notice_read_record`;

-- TABLE app_notice_read_record
TRUNCATE TABLE `app_notice_read_record`;

-- TABLE company_admin
TRUNCATE TABLE `company_admin`;

-- TABLE community_admin
TRUNCATE TABLE `community_admin`;

-- TABLE community_phone_book
TRUNCATE TABLE `community_phone_book`;

-- TABLE customer_specialist_group
TRUNCATE TABLE `customer_specialist_group`;

-- TABLE customer_specialist
TRUNCATE TABLE `customer_specialist`;

-- TABLE owner_lessee
TRUNCATE TABLE `owner_lessee`;

-- TABLE total_user
TRUNCATE TABLE `total_user`;

-- TABLE beacon_setting
TRUNCATE TABLE `beacon_setting`;

-- TABLE province
TRUNCATE TABLE `province`;

-- TABLE city
TRUNCATE TABLE `city`;

-- TABLE area
TRUNCATE TABLE `area`;

-- TABLE wx_menu
TRUNCATE TABLE `wx_menu`;

-- TABLE wx_sub_menu
TRUNCATE TABLE `wx_sub_menu`;

-- TABLE mp_rule
TRUNCATE TABLE `mp_rule`;

-- TABLE mp_rule_news_item
TRUNCATE TABLE `mp_rule_news_item`;

-- TABLE community
TRUNCATE TABLE `community`;

-- TABLE community_config
TRUNCATE TABLE `community_config`;

-- TABLE house_member
TRUNCATE TABLE `house_member`;

-- TABLE bill_day
TRUNCATE TABLE `bill_day`;

-- TABLE bill
TRUNCATE TABLE `bill`;

-- TABLE bill_detail
TRUNCATE TABLE `bill_detail`;

-- TABLE community_report
TRUNCATE TABLE `community_report`;

-- TABLE cs_chat_record
TRUNCATE TABLE `cs_chat_record`;

-- TABLE chat_room_record
TRUNCATE TABLE `chat_room_record`;

-- TABLE group_send
TRUNCATE TABLE `group_send`;

-- TABLE group_send_item
TRUNCATE TABLE `group_send_item`;

-- TABLE address_level_info
TRUNCATE TABLE `address_level_info`;

-- TABLE user_notify
TRUNCATE TABLE `user_notify`;

-- TABLE push_message
TRUNCATE TABLE `push_message`;

-- TABLE test_procurement
TRUNCATE TABLE `test_procurement`;

-- TABLE restaurant
TRUNCATE TABLE `restaurant`;

-- TABLE part
TRUNCATE TABLE `part`;

-- TABLE store
TRUNCATE TABLE `store`;

-- TABLE category
TRUNCATE TABLE `category`;

-- TABLE product
TRUNCATE TABLE `product`;

-- TABLE order
TRUNCATE TABLE `order`;

-- TABLE order_detail
TRUNCATE TABLE `order_detail`;

-- TABLE order_change_log
TRUNCATE TABLE `order_change_log`;

-- TABLE cart
TRUNCATE TABLE `cart`;

-- TABLE cart_detail
TRUNCATE TABLE `cart_detail`;

-- TABLE wx_pay_record
TRUNCATE TABLE `wx_pay_record`;

-- TABLE product_comment
TRUNCATE TABLE `product_comment`;

-- TABLE procurement_order
TRUNCATE TABLE `procurement_order`;

-- TABLE procurement_order_change_log
TRUNCATE TABLE `procurement_order_change_log`;

-- TABLE procurement_order_change_detail
TRUNCATE TABLE `procurement_order_change_detail`;

-- TABLE procurement_order_detail
TRUNCATE TABLE `procurement_order_detail`;

-- TABLE procurement_cart
TRUNCATE TABLE `procurement_cart`;

-- TABLE procurement_cart_detail
TRUNCATE TABLE `procurement_cart_detail`;


SET @@foreign_key_checks = 1;