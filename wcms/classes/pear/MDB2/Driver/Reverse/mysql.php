<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@backendmedia.com>                         |
// +----------------------------------------------------------------------+
//
// $Id: mysql.php,v 1.1 2005/08/28 06:00:14 streaky Exp $
//

require_once 'MDB2/Driver/Reverse/Common.php';

/**
 * MDB2 MySQL driver for the schema reverse engineering module
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@backendmedia.com>
 */
class MDB2_Driver_Reverse_mysql extends MDB2_Driver_Reverse_Common
{
    // {{{ getTableFieldDefinition()

    /**
     * get the stucture of a field into an array
     *
     * @param string    $table         name of table that should be used in method
     * @param string    $field_name     name of field that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableFieldDefinition($table, $field_name)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $result = $db->loadModule('Datatype');
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($field_name == $db->dummy_primary_key) {
            return $db->raiseError(MDB2_ERROR, null, null,
                'getTableFieldDefinition: '.$db->dummy_primary_key.' is an hidden column');
        }
        $columns = $db->queryAll("SHOW COLUMNS FROM $table", null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($columns)) {
            return $columns;
        }
        foreach ($columns as $column) {
            if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
                $column['field'] = strtolower($column['field']);
            } else {
                $column = array_change_key_case($column, CASE_LOWER);
            }
            if ($field_name == $column['field']) {
                list($types, $length) = $db->datatype->mapNativeDatatype($column);
                unset($notnull);
                if (isset($column['null']) && $column['null'] != 'YES') {
                    $notnull = true;
                }
                unset($default);
                if (isset($column['default'])) {
                    $default = $column['default'];
                }
                $definition = array();
                foreach ($types as $key => $type) {
                    $definition[0][$key] = array('type' => $type);
                    if (isset($notnull)) {
                        $definition[0][$key]['notnull'] = true;
                    }
                    if (isset($default)) {
                        $definition[0][$key]['default'] = $default;
                    }
                    if (isset($length)) {
                        $definition[0][$key]['length'] = $length;
                    }
                }
                if (isset($column['extra']) && $column['extra'] == 'auto_increment') {
                    $implicit_sequence = array();
                    $implicit_sequence['on'] = array();
                    $implicit_sequence['on']['table'] = $table;
                    $implicit_sequence['on']['field'] = $field_name;
                    $definition[1]['name'] = $table;
                    $definition[1]['definition'] = $implicit_sequence;
                }
                if (isset($column['key']) && $column['key'] == 'PRI') {
                    // check that its not just a unique field
                    $query = "SHOW INDEX FROM $table";
                    $indexes = $db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
                    if (PEAR::isError($indexes)) {
                        return $indexes;
                    }
                    $is_primary = false;
                    foreach ($indexes as $index) {
                        if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
                            $index['column_name'] = strtolower($index['column_name']);
                        } else {
                            $index = array_change_key_case($index, CASE_LOWER);
                        }
                        if ($index['key_name'] == 'PRIMARY' && $index['column_name'] == $field_name) {
                            $is_primary = true;
                            break;
                        }
                    }
                    if ($is_primary) {
                        $implicit_index = array();
                        $implicit_index['unique'] = true;
                        $implicit_index['fields'][$field_name] = '';
                        $definition[2]['name'] = $field_name;
                        $definition[2]['definition'] = $implicit_index;
                    }
                }
                return $definition;
            }
        }

        return $db->raiseError(MDB2_ERROR, null, null,
            'getTableFieldDefinition: it was not specified an existing table column');
    }

    // }}}
    // {{{ getTableIndexDefinition()

    /**
     * get the stucture of an index into an array
     *
     * @param string    $table      name of table that should be used in method
     * @param string    $index_name name of index that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableIndexDefinition($table, $index_name)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        if ($index_name == 'PRIMARY') {
            return $db->raiseError(MDB2_ERROR, null, null,
                'getTableIndexDefinition: PRIMARY is an hidden index');
        }
        $result = $db->query("SHOW INDEX FROM $table");
        if (PEAR::isError($result)) {
            return $result;
        }
        $definition = array();
        while (is_array($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))) {
            if (!($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE)) {
                $row = array_change_key_case($row, CASE_LOWER);
            }
            $key_name = $row['key_name'];
            if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
                $key_name = strtolower($key_name);
            }
            if ($index_name == $key_name) {
                if (!$row['non_unique']) {
                    $definition['unique'] = true;
                }
                $column_name = $row['column_name'];
                if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
                    $column_name = strtolower($column_name);
                }
                $definition['fields'][$column_name] = array();
                if (isset($row['collation'])) {
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] == 'A'
                        ? 'ascending' : 'descending');
                }
            }
        }
        $result->free();
        if (!isset($definition['fields'])) {
            return $db->raiseError(MDB2_ERROR, null, null,
                'getTableIndexDefinition: it was not specified an existing table index');
        }
        return $definition;
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set
     *
     * @param object|string  $result  MDB2_result object from a query or a
     *                                 string containing the name of a table.
     *                                 While this also accepts a query result
     *                                 resource identifier, this behavior is
     *                                 deprecated.
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A MDB2_Error object on failure.
     *
     * @see MDB2_common::tableInfo()
     */
    function tableInfo($result, $mode = null)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = @mysql_list_fields($db->database_name, $result, $db->connection);
            $got_string = true;
        } elseif (MDB2::isResultCommon($result)) {
            /*
             * Probably received a result object.
             * Extract the result resource identifier.
             */
            $id = $result->getResource();
            $got_string = false;
        } else {
            /*
             * Probably received a result resource identifier.
             * Copy it.
             * Deprecated.  Here for compatibility only.
             */
            $id = $result;
            $got_string = false;
        }

        if (!is_resource($id)) {
            return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA);
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = @mysql_num_fields($id);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        for ($i = 0; $i < $count; $i++) {
            $res[$i] = array(
                'table' => $case_func(@mysql_field_table($id, $i)),
                'name'  => $case_func(@mysql_field_name($id, $i)),
                'type'  => @mysql_field_type($id, $i),
                'len'   => @mysql_field_len($id, $i),
                'flags' => @mysql_field_flags($id, $i),
            );
            if ($mode & MDB2_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }

        // free the result only if we were called on a table
        if ($got_string) {
            @mysql_free_result($id);
        }
        return $res;
    }
}
?>