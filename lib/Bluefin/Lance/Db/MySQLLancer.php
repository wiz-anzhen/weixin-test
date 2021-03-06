<?php

namespace Bluefin\Lance\Db;

use Bluefin\Common;
use Bluefin\Lance\Entity;
use Bluefin\Lance\Field;
use Bluefin\Data\Type;
use Bluefin\Lance\Convention;
use Bluefin\Lance\PHPCodingLogic;
use Bluefin\Lance\Exception\GrammarException;

class MySQLLancer extends DbLancerBase implements DbLancerInterface
{
    const DEFAULT_INT_DIGITS = 10;
    const INDEXABLE_TEXT_LENGTH = 255;

    const KEYWORD_NOT_NULL = 'NOT NULL';
    const KEYWORD_DEFAULT = 'DEFAULT';
    const KEYWORD_ON_UPDATE = 'ON UPDATE';
    const KEYWORD_AUTO_INCREMENT = 'AUTO_INCREMENT';
    const KEYWORD_CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    const KEYWORD_COMMENT = 'COMMENT';
    const KEYWORD_ENGINE = 'ENGINE';
    const KEYWORD_DEFAULT_CHARSET = 'DEFAULT CHARSET';

    const KEYWORD_CONSTRAINT_CASCADE = '@@cascade';
    const KEYWORD_CONSTRAINT_RESTRICT = '@@restrict';
    const KEYWORD_CONSTRAINT_SET_NULL = '@@set_null';
    const KEYWORD_CONSTRAINT_NO_ACTION = '@@no_action';

    public function getEntitySQLDefinition(Entity $entity)
    {
        $parts = array();
        $engine = $entity->getEntityOption(Convention::ENTITY_OPTION_DBMS_ENGINE);
        $charset = $entity->getEntityOption(Convention::ENTITY_OPTION_CHARSET);
        $aib = $entity->getEntityOption(Convention::ENTITY_OPTION_AUTO_INCREMENT_BASE);

        if (isset($engine))
        {
            $parts[] = self::KEYWORD_ENGINE . "={$engine}";
        }

        if (isset($charset))
        {
            $parts[] = self::KEYWORD_DEFAULT_CHARSET . "={$charset}";
        }

        $parts[] = self::KEYWORD_COMMENT . "='{$entity->getDisplayName()}'";

        if (isset($aib))
        {
            $parts[] = self::KEYWORD_AUTO_INCREMENT . "={$aib}";
        }

        return implode(' ', $parts);
    }
     
    public function getFieldSQLDefinition(Field $field)
    {
        $result = array();
        $dbType = $this->_translateFieldType($field);
        $result[] = $dbType;

        if ($field->isRequired())
        {
            $result[] = self::KEYWORD_NOT_NULL;
        }

        if ($field->hasDefaultValueInDbDefinition())
        {
            //TODO: add forbidden types
            if (strict_in_array($dbType, array()))
            {
                throw new GrammarException("Default value is not allowed for field type: {$dbType}. Field: {$field->getFieldFullName()}");
            }

            $defaultValue = $field->getDefaultValueInDbDefinition();

            if ($field->isRequired() && (is_null($defaultValue) || strcasecmp('null', $defaultValue) == 0))
            {
                throw new GrammarException("Null default value is not allowed for required field: {$field->getFieldName()}. Field: {$field->getFieldFullName()}");
            }

            $result[] = self::KEYWORD_DEFAULT . ' ' . $this->translateValue($defaultValue, $field->getFieldType());
        }

        if ($field->getOwnerFeature() == Convention::FEATURE_UPDATE_TIMESTAMP)
        {
            $result[] = self::KEYWORD_DEFAULT . ' ' . self::KEYWORD_CURRENT_TIMESTAMP;
            $result[] = self::KEYWORD_ON_UPDATE . ' ' . self::KEYWORD_CURRENT_TIMESTAMP;
        }

        if ($field->getOwnerFeature() == Convention::FEATURE_AUTO_INCREMENT_ID)
        {
            $result[] = self::KEYWORD_AUTO_INCREMENT;
        }

        if ($field->getOwnerFeature() == Convention::FEATURE_CREATE_TIMESTAMP)
        {                        
            $result[] = self::KEYWORD_DEFAULT . ' 0';
        }

        $result[] = self::KEYWORD_COMMENT . " '{$field->getDisplayName()}'";

        return implode(' ', $result);
    }

    public function getForeignConstraintSQLDefinition(Field $field)
    {
        $onDelete = $field->getForeignDeletionTrigger();
        $onUpdate = $field->getForeignUpdateTrigger();

        if (!isset($onDelete))
        {
            $onDelete = self::KEYWORD_CONSTRAINT_RESTRICT;
        }

        if (!isset($onUpdate))
        {
            $onUpdate = self::KEYWORD_CONSTRAINT_RESTRICT;
        }

        $delete = $this->translateForeignConstraintAction($onDelete);
        $update = $this->translateForeignConstraintAction($onUpdate);

        return " ON UPDATE {$delete} ON DELETE {$update}";
    }

    public function translateForeignConstraintAction($action)
    {
        switch ($action)
        {
            case self::KEYWORD_CONSTRAINT_RESTRICT:
                return 'RESTRICT';

            case self::KEYWORD_CONSTRAINT_CASCADE:
                return 'CASCADE';

            case self::KEYWORD_CONSTRAINT_NO_ACTION:
                return 'NO ACTION';

            case self::KEYWORD_CONSTRAINT_SET_NULL:
                return 'SET NULL';
        }

        throw new GrammarException("Unsupported constraint action: '{$action}'");
    }

    public function translateValue($value, $type)
    {
        if ($type == Type::TYPE_BOOL)
        {
            if ($value === false || mb_strtolower($value) == 'false') return 0;
            if ($value === true || mb_strtolower($value) == 'true') return 1;

            if ($value == 0 || $value == 1) return $value;

            return $value ? 1 : 0;
        }

        if ($type == Type::TYPE_UUID)
        {
            if (strlen($value) != 16)
            {
                if (1 !== preg_match(Type::PATTERN_UUID, $value, $matches))
                {
                    \Bluefin\App::assert(false, "Invalid UUID value: {$value}");
                }

                $value = str_replace('-', '', $value);
            }
            else
            {
                $value = bin2hex($value);
            }

            return new PHPCodingLogic("0x{$value}");
        }

        return Convention::dumpValue($value);
    }

    public function translateSQL($command, $target, array $columns, array $data)
    {
        $result = '';

        switch ($command)
        {
            case 'truncate':
                if (!isset($target) || $target == '') throw new GrammarException('Invalid target.');
                $result = "TRUNCATE TABLE `{$target}`;\n";
                break;

            case 'insert':
                if (!isset($target) || $target == '') throw new GrammarException('Invalid target.');
                if (empty($columns)) throw new GrammarException('Invalid columns.');

                $count = count($data);
                if ($count == 0) throw new GrammarException('Empty data.');
                $parts = (int)($count / Convention::SQL_INSERT_MAX_LINES);
                $offset = 0;

                $entity = $this->_schema->getLoadedModelEntity($target);

                $columnTypes = array();

                foreach ($columns as &$val)
                {
                    $trimmed = trim($val);

                    /**
                     * @var Field $field
                     */
                    $field = $entity->getField($trimmed);

                    if (!isset($field))
                    {
                        throw new GrammarException("Invalid column [{$target}.{$trimmed}].");
                    }

                    $val = "`{$field->getFieldName()}`";
                    $columnTypes[] = $field;
                }

                for ($i = 0; $i < $parts; $i++)
                {
                    $result .= "INSERT INTO `{$target}` (" . implode(', ', $columns) . ") VALUES\n";
                    $values = array();
                    for ($j = 0; $j < Convention::SQL_INSERT_MAX_LINES; $j++)
                    {
                        $row = str_getcsv($data[$offset]);
                        $colIndex = 0;

                        foreach ($row as &$f)
                        {
                            $f = $this->translateValue(PHPCodingLogic::evaluateValue($columnTypes[$colIndex], trim($f)), $columnTypes[$colIndex]->getFieldType());
                            $colIndex++;
                        }
                        $values[] = "(" . implode(', ', $row) . ")";
                        $offset++;
                    }

                    $result .= implode(",\n", $values);
                    $result .= ";\n";
                }

                if ($offset < $count)
                {
                    $result .= "INSERT INTO `{$target}` (" . implode(', ', $columns) . ") VALUES\n";
                    $values = array();
                    for (;$offset < $count; $offset++)
                    {
                        $row = str_getcsv($data[$offset]);
                        $colIndex = 0;

                        foreach ($row as &$f)
                        {
                            $f = $this->translateValue(PHPCodingLogic::evaluateValue($columnTypes[$colIndex], trim($f)), $columnTypes[$colIndex]->getFieldType());
                            $colIndex++;
                        }
                        $values[] = "(" . implode(', ', $row) . ")";
                    }

                    $result .= implode(",\n", $values);
                    $result .= ";\n";
                }

                break;

            case 'update':
                if (!isset($target) || $target == '') throw new GrammarException('Invalid target.');
                if (empty($columns)) throw new GrammarException('Invalid columns.');

                $count = count($data);
                if ($count == 0) throw new GrammarException('Empty data.');

                $entity = $this->_schema->getLoadedModelEntity($target);

                $columnTypes = array();

                foreach ($columns as &$val)
                {
                    $trimmed = trim($val);

                    $field = $entity->getField($trimmed);

                    if (!isset($field))
                    {
                        throw new GrammarException("Invalid column [{$target}.{$trimmed}].");
                    }

                    $val = "`{$field->getFieldName()}`";
                    $columnTypes[] = $field;
                }

                for ($offset = 0; $offset < $count; $offset++)
                {
                    $row = str_getcsv($data[$offset]);
                    $colCount = count($row);
                    if ($colCount != count($columns)) throw new GrammarException("Invalid data. Offset: {$offset}");

                    $colIndex = 0;

                    foreach ($row as &$f)
                    {
                        $f = $this->translateValue(PHPCodingLogic::evaluateValue($columnTypes[$colIndex], trim($f)), $columnTypes[$colIndex]->getFieldType());
                        $colIndex++;
                    }

                    $set = array_combine($columns, $row);
                    $pk = $this->_schema->getLoadedModelEntity($target)->getPrimaryKey();
                    $pk = "`{$pk}`";
                    if (!array_key_exists($pk, $set))
                    {
                        throw new GrammarException("Missing primary key column.");
                    }
                    $pkV = $set[$pk];
                    unset($set[$pk]);

                    $parts = array();
                    foreach ($set as $k => $v)
                    {
                        $parts[] = "{$k} = {$v}";
                    }

                    $result .= "UPDATE `{$target}` SET " . implode(', ', $parts) . " WHERE {$pk} = {$pkV};\n";
                }
                break;
        }

        return $result;
    }

    private function _translateFieldType(Field $field)
    {        
        $type = $field->getFieldType();

        switch ($type)
        {
            case Type::TYPE_INT:
                $digits = (int)$field->getFilter(Type::ATTR_LENGTH, self::DEFAULT_INT_DIGITS);

                if ($digits < 3)
                {
                    return "TINYINT({$digits})";
                }
                else if ($digits < 6)
                {
                    return "SMALLINT({$digits})";
                }
                else if ($digits < 8)
                {
                    return "MEDIUMINT({$digits})";
                }
                else if ($digits < 11)
                {
                    return "INT({$digits})";
                }
                else
                {
                    return "BIGINT({$digits})";
                }
                break;

            case Type::TYPE_FLOAT:
                $precision = $field->getFilter(Type::ATTR_FLOAT_PRECISION);
                if (is_null($precision))
                {
                    return "FLOAT";
                }
                else
                {
                    $precision = (int)$precision;
                    return "FLOAT({$precision})";
                }

            case Type::TYPE_BOOL:
                return "TINYINT(1)";

            case Type::TYPE_XML:
            case Type::TYPE_JSON:
            case Type::TYPE_TEXT:
                $max = $field->getFilter(Type::ATTR_MAX);

                if (is_null($max))
                {
                    $fixedLength = $field->getFilter(Type::ATTR_LENGTH);
                    if (isset($fixedLength))
                    {
                        $fixedLength = parse_size($fixedLength);
                        \Bluefin\App::assert($fixedLength > 0);

                        if($fixedLength <= 64)
                        {
                            return "CHAR({$fixedLength})";
                        }
                        else
                        {
                            return "VARCHAR({$fixedLength})";
                        }
                    }
				
                    return "TEXT";
                }
                else
                {
                    $max = parse_size($max);
                    \Bluefin\App::assert($max > 0);

                    if ($max < 2)
                    {
                        return "CHAR(1)";
                    }
                    else if ($max <= 64)
                    {
                        return "CHAR({$max})";
                    }
                    else if ($max <= 4096)
                    {
                        return "VARCHAR({$max})";
                    }
                    else if ($max <= 65535)
                    {
                        return "TEXT";
                    }
                    else if ($max <= 16777215)
                    {
                        return "MEDIUMTEXT";
                    }
                    else
                    {
                        return "LONGTEXT";
                    }
                }
                break;

            case Type::TYPE_BINARY:
                $max = $field->getFilter(Type::ATTR_MAX);

                if (is_null($max))
                {
                    return "BLOB";
                }
                else
                {
                    $max = parse_size($max);
                    \Bluefin\App::assert($max > 0);

                    if ($max < 2)
                    {
                        return "BINARY(1)";
                    }
                    else if ($max <= 255)
                    {
                        return "VARBINARY({$max})";
                    }
                    else if ($max <= 65535)
                    {
                        return "BLOB";
                    }
                    else if ($max <= 16777215)
                    {
                        return "MEDIUMBLOB";
                    }
                    else
                    {
                        return "LONGBLOB";
                    }
                }
                break;

            case Type::TYPE_DATE:
                return "DATE";

            case Type::TYPE_TIME:
                return "TIME";

            case Type::TYPE_DATE_TIME:
                return "DATETIME";

            case Type::TYPE_TIMESTAMP:
                return "TIMESTAMP";

            case Type::TYPE_IDNAME:
                return "CHAR(32)";

            case Type::TYPE_PASSWORD:
                return "CHAR(128)";

            case Type::TYPE_DIGITS:
                $digits = (int)$field->getFilter(Type::ATTR_LENGTH);

                if (is_null($digits))
                {
                    $modifier = Type::ATTR_LENGTH;
                    throw new GrammarException("Type \"{$type}\" requires modifier: {$modifier}");
                }

                if ($digits < 3)
                {
                    return "TINYINT({$digits}) ZEROFILL";
                }
                else if ($digits < 6)
                {
                    return "SMALLINT({$digits}) ZEROFILL";
                }
                else if ($digits < 8)
                {
                    return "MEDIUMINT({$digits}) ZEROFILL";
                }
                else if ($digits < 11)
                {
                    return "INT({$digits}) ZEROFILL";
                }

                return "BIGINT({$digits}) ZEROFILL";

            case Type::TYPE_URL:
                if ($field->isIndexed())
                {
                    $len = self::INDEXABLE_TEXT_LENGTH;
                }
                else
                {
                    $len = Common::MAX_LENGTH_URL;
                }

                return "VARCHAR({$len})";

            case Type::TYPE_PATH:
                $len = self::INDEXABLE_TEXT_LENGTH;
                return "VARCHAR({$len})";

            case Type::TYPE_EMAIL:
                if ($field->isIndexed())
                {
                    $len = self::INDEXABLE_TEXT_LENGTH;
                }
                else
                {
                    $len = Common::MAX_LENGTH_EMAIL_ADDRESS;
                }

                return "VARCHAR({$len})";

            case Type::TYPE_PHONE:
                return "CHAR(20)";

            case Type::TYPE_MONEY:
                return "DECIMAL(15,2)";

            case Type::TYPE_UUID:
                return "BINARY(16)";

            case Type::TYPE_IPV4:
                return "CHAR(15)";

            default;
                throw new GrammarException("Unsupported type: {$type}");
        }        
    }
}
