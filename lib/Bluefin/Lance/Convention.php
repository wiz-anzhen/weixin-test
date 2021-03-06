<?php

namespace Bluefin\Lance;

/**
 * Lance的默认约定
 */
class Convention
{
    const ENTITY_TYPE_ENTITY = 'entity';
    const ENTITY_TYPE_ABSTRACT = 'abstract';
    const ENTITY_TYPE_ENUM = 'enum';
    const ENTITY_TYPE_FST = 'state';
    const ENTITY_TYPE_RELATION = 'relation';
    const PATTERN_ENTITY_PREFIX = '(\~|\@|\$|\&|abstract\`|enum\`|state\`|relation\`)';

    const PATTERN_PRAGMA_PREFIX = '(?:\!|pragma\`)';

    const PREFIX_CUSTOM_TYPE = '(\+|type\`)';

    const PREFIX_PRIMARY_KEY = 'pk_';
    const PREFIX_FOREIGN_KEY = 'fk_';
    const PREFIX_UNIQUE_KEY = 'uk_';
    const PREFIX_NORMAL_KEY = 'ak_';

    const MODIFIER_MIXTURE_PREFIX = '^';
    const MODIFIER_MIXTURE_SUFFIX = '$';
    const MODIFIER_MIXTURE_NON_REQUIRED = '?';
    const MODIFIER_MIXTURE_EXCLUDE = '-';

    const MODIFIER_FIELD_NOT_REQUIRED = '?';
    const MODIFIER_FIELD_ONLY_ONE = '1';

    const MODIFIER_TYPE_COMMENT = 'comment=';
    const MODIFIER_TYPE_LT = '<';
    const MODIFIER_TYPE_LTE = '<=';
    const MODIFIER_TYPE_GT = '>';
    const MODIFIER_TYPE_GTE = '>=';
    const MODIFIER_TYPE_DEFAULT = '=';
    const MODIFIER_TYPE_LENGTH = '+';
    const MODIFIER_TYPE_PRECISION = '%';
    const MODIFIER_TYPE_NO_INSERT = 'auto';
    const MODIFIER_TYPE_NO_UPDATE = 'fixed';
    const MODIFIER_TYPE_POST_PROCESSOR = '$=';
    const MODIFIER_TYPE_ON = 'join_on=';
    const MODIFIER_TYPE_ON_DELETE = 'on_reference_delete=';
    const MODIFIER_TYPE_ON_UPDATE = 'on_reference_update=';
    const MODIFIER_TYPE_NO_INPUT = 'no_input';

    const MODIFIER_INDEX_UNIQUE = 'u';
    const MODIFIER_INDEX_PRIMARY = 'p';

    const KEYWORD_PRAGMA_COMMENT_LOCALE = 'comment-locale';
    const DEFAULT_PRAGMA_COMMENT_LOCALE = 'en_US';

    const KEYWORD_SCHEMA_COMMENT = 'comment';
    const KEYWORD_SCHEMA_LOCALE = 'locale';
    const KEYWORD_SCHEMA_DB = 'db';
    const KEYWORD_SCHEMA_DB_TYPE = 'type';
    const KEYWORD_SCHEMA_DB_ENGINE = 'engine';
    const KEYWORD_SCHEMA_DB_ADAPTER = 'adapter';
    const KEYWORD_SCHEMA_DB_CHARSET = 'charset';
    const KEYWORD_SCHEMA_DB_CONNECTION = 'connection';
    const KEYWORD_SCHEMA_ENTITIES = 'entities';
    const KEYWORD_SCHEMA_NAMESPACE = 'namespace';
    const KEYWORD_SCHEMA_DATA = 'data';
    const KEYWORD_SCHEMA_FEATURES = 'features';
    const KEYWORD_SCHEMA_AUTH = 'auth';

    const KEYWORD_ENTITY_COMMENT = 'comment';
    const KEYWORD_ENTITY_FEATURE = 'with';
    const KEYWORD_ENTITY_INHERIT = 'is';
    const KEYWORD_ENTITY_MIX = 'mix';
    const KEYWORD_ENTITY_MEMBER = 'has';
    const KEYWORD_ENTITY_RELATION = 'between';
    const KEYWORD_ENTITY_INDEX = 'keys';
    const KEYWORD_ENTITY_VALUES = 'values';
    const KEYWORD_ENTITY_STATES = 'states';
    const KEYWORD_ENTITY_PHP_TRIGGERS = 'php-triggers';
    const KEYWORD_ENTITY_DEFAULT_VALUE = 'default';
    const KEYWORD_ENTITY_FSM = 'fsm';
    const KEYWORD_ENTITY_SERVICE = 'crud';

    const KEYWORD_ENTITY_FSM_TRANSITIONS = 'TRANSITIONS';
    const KEYWORD_ENTITY_FSM_FROM = 'FROM';
    const KEYWORD_ENTITY_FSM_TARGET = 'TARGET';
    const KEYWORD_ENTITY_FSM_ALLOWED_ROLES = 'ROLES';
    const KEYWORD_ENTITY_FSM_INPUT = 'INPUT';
    const KEYWORD_ENTITY_FSM_UPDATE_FILEDS = 'UPDATE';
    const KEYWORD_ENTITY_FSM_SPECIFIC_ROLES = 'SPECIFIC_ROLES';
    const KEYWORD_ENTITY_FSM_CALLER = 'CALLER';
    const KEYWORD_ENTITY_FSM_ROLE_OWNER_TOKEN = '*owner*';
    const KEYWORD_ENTITY_FSM_ROLE_CREATOR_TOKEN = '*creator*';

    const KEYWORD_VAR_TEXT_PREFIX = '@';
    const KEYWORD_AUTO_VALUE = '@auto';
    const KEYWORD_TRIGGER_VALUE = '@trigger';
    const KEYWORD_DB_EXPR_PREFIX = '@expr:';
    const KEYWORD_PHP_DEFAULT_VALUE = '@php:';
    const KEYWORD_DB_EXPR_VT_PREFIX = '@expr:@';

    const FIELD_NAME_MAX_LENGTH = 64;

    const FIELD_NAME_STATE = 'state';
    const FIELD_TYPE_STATE = 'idname';

    const FIELD_NAME_COMMENT = 'comment';
    const FIELD_TYPE_COMMENT = 'text|<=200';

    const FIELD_NAME_ENUM_VALUE = 'value';
    const FIELD_TYPE_ENUM_VALUE = 'text|<=32';

    const FIELD_NAME_DISPLAY_NAME = 'display_name';
    const FIELD_TYPE_DISPLAY_NAME = 'text|<=40';

    const FEATURE_AUTO_INCREMENT_ID = 'auto_increment_id';
    const FEATURE_AUTO_UUID = 'auto_uuid';
    const FEATURE_CREATE_TIMESTAMP = 'create_timestamp';
    const FEATURE_UPDATE_TIMESTAMP = 'update_timestamp';
    const FEATURE_LOGICAL_DELETION = 'logical_deletion';
    const FEATURE_HAS_SCHEDULED_TASK = 'has_scheduled_task';
    const FEATURE_OWNER_FIELD = 'owner_field';
    const FEATURE_CREATOR_FIELD = 'creator_field';
    const FEATURE_KEY_VALUE_CACHE = 'key_value_cache';

    const ENTITY_OPTION_DBMS_ENGINE = 'dbms_engine';
    const ENTITY_OPTION_CHARSET = 'charset';
    const ENTITY_OPTION_AUTO_INCREMENT_BASE = 'auto_increment_base';

    const ENTITY_STATUS_INITIAL = 0;
    const ENTITY_STATUS_INHERIT = 1;
    const ENTITY_STATUS_TO_MIX = 2;
    const ENTITY_STATUS_MIX_ONE = 3;
    const ENTITY_STATUS_FEATURE1 = 4;
    const ENTITY_STATUS_MEMBER = 5;
    const ENTITY_STATUS_TO_ADD_REFERENCE = 6;
    const ENTITY_STATUS_ADDING_A_REFERENCE = 7;
    const ENTITY_STATUS_KEY = 8;
    const ENTITY_STATUS_FEATURE2 = 9;
    const ENTITY_STATUS_TO_ADD_REFERENCE2 = 10;
    const ENTITY_STATUS_ADDING_A_REFERENCE2 = 11;
    const ENTITY_STATUS_FINAL_CHECK = 12;
    const ENTITY_STATUS_READY = 13;

    const SQL_INSERT_MAX_LINES = 200;

    const LOG_CAT_LANCE_CORE = 'core';
    const LOG_CAT_LANCE_DIAG = 'diag';

    private static $__keywords;

    public static function isKeyword($word)
    {
        isset(self::$__keywords) || (
            self::$__keywords = [
                'private',
                'public',
                'class',
                'const'
            ]
        );

        return strict_in_array($word, self::$__keywords);
    }

    public static function dumpArray(array $array)
    {
        $result = '[';
        $parts = array();
        foreach ($array as $key => $value)
        {
            if (is_int($key))
            {
                $parts[] = self::dumpValue($value);
            }
            else
            {
                $part = "'{$key}' => ";

                if (is_array($value))
                {
                    $part .= self::dumpArray($value);
                }
                else if (is_bool($value))
                {
                    $part .= $value ? 'true' : 'false';
                }
                else
                {
                    $part .= self::dumpValue($value);
                }

                $parts[] = $part;
            }
        }

        $result .= implode(', ', $parts) . ']';

        return $result;
    }

    public static function dumpValue($value)
    {
        if (is_array($value)) return self::dumpArray($value);

        if (is_null($value) || mb_strtolower($value) === 'null') return 'NULL';

        if ($value === '') return "''";

        if (is_numeric($value)) return $value;

        if (is_bool($value)) return $value ? 'true' : 'false';

        if ($value instanceof PHPCodingLogic) return $value->__toString();

        if ($value instanceof \Bluefin\Data\DbExpr) return $value->__toString();

        if ($value instanceof \Bluefin\Data\DbCondition) return $value->__toString();

        if (is_string($value) && !str_is_quoted($value))
        {
            return str_quote($value, true);
        }

        return $value;
    }

    public static function buildDisplayName($locale, $fullName, $preferredName)
    {
        self::addMetadataTranslation($locale, $fullName, $preferredName);

        return _T($fullName, \Bluefin\Convention::LOCALE_METADATA);
    }

    public static function getNormalKeyName($entityName, $fieldName)
    {
        return Convention::PREFIX_NORMAL_KEY . combine_usw($entityName, $fieldName);
    }

    public static function getForeignKeyName($entityName, $fieldName)
    {
        return Convention::PREFIX_FOREIGN_KEY . combine_usw($entityName, $fieldName);
    }

    public static function addMetadataTranslation($locale, $key, $translation)
    {
        $exportPath = APP_LOCALE . "/{$locale}";
        $exportFile = $exportPath . '/' . \Bluefin\Convention::LOCALE_METADATA . \Bluefin\Convention::FILE_TYPE_LOCALE_FILE;

        ensure_dir_exist($exportPath);

        if ($locale == \Bluefin\App::getInstance()->currentLocale())
        {
            \Bluefin\App::getInstance()->addTranslation(
                \Bluefin\Convention::LOCALE_METADATA, $key, $translation);
        }
        else
        {
            if (file_exists($exportFile))
            {
                $domainText = \Symfony\Component\Yaml\Yaml::load($exportFile);
            }
            else
            {
                $domainText = array();
            }

            $domainText[$key] = $translation;
            file_put_contents($exportFile, \Symfony\Component\Yaml\Yaml::dump($domainText), LOCK_EX);
        }
    }

    public static function getTemplateRoot($path)
    {
        $extend = LANCE_EXTEND . "/templates/{$path}";
        return (file_exists($extend) ? LANCE_EXTEND : BLUEFIN_LANCE) . '/templates';
    }
}
