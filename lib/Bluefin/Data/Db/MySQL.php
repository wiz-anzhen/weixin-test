<?php

namespace Bluefin\Data\Db;

use Bluefin\App;
use Bluefin\Data\Database;
use Bluefin\Data\Relation;
use Bluefin\Data\Select;
use Bluefin\Data\Relations;
use Bluefin\Data\Type;
use Bluefin\Convention;
use Bluefin\Log;
use Bluefin\Dummy;

class MySQL extends PDO implements DbInterface
{
    const SQL_IDENTIFIER_QUOTE = '`';

    public function __construct(array $config)
    {
        $dsnParams = array_get_all($config, array('unix_socket', 'host', 'port', 'dbname', 'charset'), true);
        $config[PDO::CONFIG_DSN] = 'mysql:' . join_key_value_pairs($dsnParams);

        parent::__construct($this, $config);
    }

    public function isIdentifierQuoted($id)
    {
        return $id[0] == self::SQL_IDENTIFIER_QUOTE && $id[mb_strlen($id)-1] == self::SQL_IDENTIFIER_QUOTE;
    }

    public function quoteIdentifier($id)
    {
        if ($id == '*') return $id;
        if (strpos($id, '(') !== false) return $id;

        return $this->isIdentifierQuoted($id) ? $id : ('`' . str_replace('`', '``', $id) . '`');
    }

    public function quoteValue($value)
    {
        return isset($value) ? str_quote($value, true) : null;
    }

    public function combineOrCondition(array $terms)
    {
        return '(' . implode(' OR ', $terms) . ')';
    }

    public function combineCondition($quotedColumn, $value, $negative = false)
    {
        if (is_array($value))
        {
            $value = implode(',', $value);
            return $negative ? "{$quotedColumn} NOT IN ({$value})" : "{$quotedColumn} IN ({$value})";
        }
        else if (isset($value))
        {
            return $negative ? "{$quotedColumn}<>{$value}" : "{$quotedColumn}={$value}";
        }
        else
        {
            return $negative ? "{$quotedColumn} IS NOT NULL" : "{$quotedColumn} IS NULL";
        }
    }

    public function combineTableAndColumn($tableName, $columnName)
    {
        return $tableName . '.' . $this->quoteIdentifier($columnName);
    }

    public function combineColumnAndAlias($columnName, $alias)
    {
        return $columnName . ' AS ' . $alias;
    }

    public function buildInsertSQL($table, array $columns, array $values, array $onDuplicateUpdate = null)
    {
        $quotedTableName = $this->quoteIdentifier($table);

        $sql = "INSERT INTO "
            . $quotedTableName
            . ' (' . implode(', ', $columns) . ') '
            . 'VALUES (' . implode(', ', $values) . ')';

        if (!empty($onDuplicateUpdate))
        {
            $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $onDuplicateUpdate);
        }

        //[+]DEBUG
        if (App::getInstance()->log()->isLogOn(\Bluefin\Log::DEBUG, \Bluefin\Log::CHANNEL_DIAG))
        {
            App::getInstance()->log()->debug("INSERT SQL: {$sql}", \Bluefin\Log::CHANNEL_DIAG);
        }
        //[-]DEBUG

        return $sql;
    }

    public function buildUpdateSQL(Relations $relations, $set, $where)
    {
        $sql = 'UPDATE ' . $this->quoteIdentifier($relations->getTableMetadata()->getModelName());

        if ($relations->hasAnyRelations())
        {
            $sql .= ' AS ' . $relations->getTableAlias()
                . $this->buildReferencedTableList($relations);
        }

        $sql .= ' SET ' . implode(', ', $set);
        $sql .= $this->buildWhereStyledRelations($relations, $where);

        //[+]DEBUG
        if (App::getInstance()->log()->isLogOn(\Bluefin\Log::DEBUG, \Bluefin\Log::CHANNEL_DIAG))
        {
            App::getInstance()->log()->debug("UPDATE SQL: {$sql}", \Bluefin\Log::CHANNEL_DIAG);
        }
        //[-]DEBUG

        return $sql;
    }

    public function buildDeleteSQL(Relations $relations, $where)
    {
        $quotedMainTable = $this->quoteIdentifier($relations->getTableMetadata()->getModelName());

        $sql = 'DELETE ' . $quotedMainTable;

        if ($relations->hasAnyRelations())
        {
            $sql .= ' AS ' . $relations->getTableAlias()
                . $this->buildReferencedTableList($relations);
        }

        $sql .= ' FROM ' . $quotedMainTable;
        $sql .= $this->buildWhereStyledRelations($relations, $where);

        //[+]DEBUG
        if (App::getInstance()->log()->isLogOn(\Bluefin\Log::DEBUG, \Bluefin\Log::CHANNEL_DIAG))
        {
            App::getInstance()->log()->debug("DELETE SQL: {$sql}", \Bluefin\Log::CHANNEL_DIAG);
        }
        //[-]DEBUG

        return $sql;
    }

    public function buildWhereClause($where)
    {
        return (is_array($where) && !empty($where)) ? (' WHERE ' . implode(' AND ', $where)) : '';
    }

    public function buildGroupByClause(array $grouping)
    {
        if (empty($grouping)) return '';

        $clauses = array();

        foreach ($grouping as $column)
        {
            $clauses[] = $this->quoteIdentifier($column);
        }

        return ' GROUP BY ' . implode(',', $clauses);
    }

    public function buildOrderByClause(array $ranking)
    {
        if (empty($ranking)) return '';

        $clauses = array();

        foreach ($ranking as $column => $desc)
        {
            if (is_int($column) || !$desc)
            {
                $q = $this->quoteIdentifier($desc);
            }
            else
            {
                $q = $this->quoteIdentifier($column);
                $desc && ($q .= " DESC");
            }

            $clauses[] = $q;
        }

        return ' ORDER BY ' . implode(',', $clauses);
    }

    public function buildSelectSQL(Select $select, array &$pagination = null)
    {
        $from = $this->quoteIdentifier($select->getFrom());
        $sql = "SELECT {$select->getSelect()} FROM {$from}";

        $alias = $select->getAlias();
        isset($alias) && ($sql .= " AS {$alias}");

        $sql .= $select->getJoin();
        $sql .= $select->getWhere();
        $sql .= $select->getGroupBy();
        $sql .= $select->getOrderBy();

        if (isset($pagination) && isset($pagination[Database::KW_SQL_ROWS_PER_PAGE]))
        {
            $rowsPerPage = (int)array_try_get($pagination, Database::KW_SQL_ROWS_PER_PAGE, Database::DEFAULT_ROWS_PER_PAGE);
            if ($rowsPerPage == 0) $rowsPerPage = 1;

            $pageIndex = (int)array_try_get($pagination, Database::KW_SQL_PAGE_INDEX, 1);
            $totalRows = (int)array_try_get($pagination, Database::KW_SQL_TOTAL_ROWS, -1);
            $maxPage = ($totalRows > -1) ? (int)(($totalRows-1)/$rowsPerPage)+1 : -1;

            if ($pageIndex <= 0) $pageIndex = 1;
            if ($maxPage > -1 && $pageIndex > $maxPage) $pageIndex = $maxPage;

            $offset = $rowsPerPage > 0 ? ($pageIndex-1)*$rowsPerPage : $pageIndex-1;

            $pagination[Database::KW_SQL_ROWS_PER_PAGE] = $rowsPerPage;
            $pagination[Database::KW_SQL_PAGE_INDEX] = $pageIndex;

            $sql .= " LIMIT {$offset}";
            $rowsPerPage > 0 && ($sql .= ",{$rowsPerPage}");
        }

        //[+]DEBUG
        if (App::getInstance()->log()->isLogOn(\Bluefin\Log::DEBUG, \Bluefin\Log::CHANNEL_DIAG))
        {
            App::getInstance()->log()->debug("SELECT SQL: {$sql}", \Bluefin\Log::CHANNEL_DIAG);
        }
        //[-]DEBUG

        return $sql;
    }
    
    public function buildJoinRelations(Relations $relations)
    {
        if (!$relations->hasAnyRelations()) return '';

        $clauses = array();

        foreach ($relations->getRelations() as $relation)
        {
            /**
             * @var \Bluefin\Data\Relation $relation
             */
            $leftPart = $this->combineTableAndColumn($relation->getLeftTableAlias(), $relation->getLeftFieldName());
            $rightTable = $this->quoteIdentifier($relation->getRightTableName());
            $rightPart = $this->combineTableAndColumn($relation->getRightTableAlias(), $relation->getRightFieldName());
            $clauses[] = "LEFT JOIN {$rightTable} AS {$relation->getRightTableAlias()} ON {$leftPart} = {$rightPart}";
        }

        return ' ' . implode(' ', $clauses);
    }

    public function buildReferencedTableList(Relations $relations)
    {
        $clauses = array();

        foreach ($relations->getRelations() as $relation)
        {
            /**
             * @var \Bluefin\Data\Relation $relation
             */
            $rightTable = $this->quoteIdentifier($relation->getRightTableName());
            $clauses[] = "{$rightTable} AS {$relation->getRightTableAlias()})";
        }

        return ' ' . implode(', ', $clauses);
    }

    public function buildWhereStyledRelations(Relations $relations, $where = null)
    {
        if (!$relations->hasAnyRelations()) return $this->buildWhereClause($where);

        $clauses = array();

        foreach ($relations->getRelations() as $relation)
        {
            /**
             * @var \Bluefin\Data\Relation $relation
             */
            $leftPart = $this->combineTableAndColumn($relation->getLeftTableAlias(), $relation->getLeftFieldName());
            $rightPart = $this->combineTableAndColumn($relation->getRightTableAlias(), $relation->getRightFieldName());
            $clauses[] = "({$leftPart}={$rightPart})";
        }

        isset($where) && ($clauses = array_merge($clauses, $where));

        return ' WHERE ' . implode(' AND ', $clauses);
    }

    public function wrapColumnOnSelect($type, $columnName)
    {
        if ($type == Type::TYPE_UUID)
        {
            return "HEX({$columnName})";
        }

        return $columnName;
    }
}
