SET @@foreign_key_checks = 0;

-- TABLE oauth_token
TRUNCATE TABLE `oauth_token`;

-- TABLE oauth_client
TRUNCATE TABLE `oauth_client`;

-- TABLE user
TRUNCATE TABLE `user`;

-- TABLE oauth_code
TRUNCATE TABLE `oauth_code`;

-- TABLE personal_profile
TRUNCATE TABLE `personal_profile`;

-- TABLE country
TRUNCATE TABLE `country`;

-- TABLE province
TRUNCATE TABLE `province`;

-- TABLE city
TRUNCATE TABLE `city`;

-- TABLE district
TRUNCATE TABLE `district`;

-- TABLE address
TRUNCATE TABLE `address`;

-- TABLE user_login_record
TRUNCATE TABLE `user_login_record`;

-- TABLE system_property
TRUNCATE TABLE `system_property`;


SET @@foreign_key_checks = 1;