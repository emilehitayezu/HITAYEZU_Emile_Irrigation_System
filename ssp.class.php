<?php
class SSP {
    static function data_output($columns, $data) {
        $out = array();
        for ($i = 0, $ien = count($data); $i < $ien; $i++) {
            $row = array();
            for ($j = 0, $jen = count($columns); $j < $jen; $j++) {
                $column = $columns[$j];
                // Is there a formatter?
                if (isset($column['formatter'])) {
                    $row[$column['dt']] = $column['formatter']($data[$i][$column['db']], $data[$i]);
                } else {
                    $row[$column['dt']] = $data[$i][$columns[$j]['db']];
                }
            }
            $out[] = $row;
        }
        return $out;
    }

    static function limit($request, $columns) {
        $limit = '';
        if (isset($request['start']) && $request['length'] != -1) {
            $limit = "LIMIT " . intval($request['start']) . ", " . intval($request['length']);
        }
        return $limit;
    }

    static function order($request, $columns) {
        $order = '';
        if (isset($request['order']) && count($request['order'])) {
            $orderBy = array();
            $dtColumns = self::pluck($columns, 'dt');
            
            for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];
                
                if ($requestColumn['orderable'] == 'true') {
                    $dir = $request['order'][$i]['dir'] === 'asc' ? 'ASC' : 'DESC';
                    $orderBy[] = '`' . $column['db'] . '` ' . $dir;
                }
            }
            
            if (count($orderBy)) {
                $order = 'ORDER BY ' . implode(', ', $orderBy);
            }
        }
        return $order;
    }

    static function filter($request, $columns, &$bindings) {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = self::pluck($columns, 'dt');
        
        if (isset($request['search']) && $request['search']['value'] != '') {
            $str = $request['search']['value'];
            
            for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];
                
                if ($requestColumn['searchable'] == 'true') {
                    $binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
                    $globalSearch[] = "`" . $column['db'] . "` LIKE " . $binding;
                }
            }
        }
        
        // Individual column filtering
        for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search($requestColumn['data'], $dtColumns);
            $column = $columns[$columnIdx];
            
            $str = $requestColumn['search']['value'];
            
            if ($requestColumn['searchable'] == 'true' && $str != '') {
                $binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
                $columnSearch[] = "`" . $column['db'] . "` LIKE " . $binding;
            }
        }
        
        // Combine the filters into a single string
        $where = '';
        
        if (count($globalSearch)) {
            $where = '(' . implode(' OR ', $globalSearch) . ')';
        }
        
        if (count($columnSearch)) {
            $where = $where === '' ?
                implode(' AND ', $columnSearch) :
                $where . ' AND ' . implode(' AND ', $columnSearch);
        }
        
        return $where;
    }

    static function simple($request, $sql_details, $table, $primaryKey, $columns, $where = '') {
        $bindings = array();
        $db = self::db($sql_details);
        
        // Build the SQL query string from the request
        $limit = self::limit($request, $columns);
        $order = self::order($request, $columns);
        $where = self::_complex($request, $columns, $bindings, $where);
        
        // Main query to actually get the data
        $data = self::sql_exec($db, $bindings,
            "SELECT SQL_CALC_FOUND_ROWS `" . implode("`, `", self::pluck($columns, 'db')) . "` 
             FROM `$table` 
             $where
             $order
             $limit"
        );
        
        // Data set length after filtering
        $resFilterLength = self::sql_exec($db, "SELECT FOUND_ROWS()");
        $recordsFiltered = $resFilterLength[0][0];
        
        // Total data set length
        $resTotalLength = self::sql_exec($db, "SELECT COUNT(`{$primaryKey}`) FROM `$table`");
        $recordsTotal = $resTotalLength[0][0];
        
        // Output
        return array(
            "draw" => isset($request['draw']) ? intval($request['draw']) : 0,
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => self::data_output($columns, $data)
        );
    }

    static function _complex($request, $columns, &$bindings, $where = '') {
        $where = $where === '' ? 'WHERE ' : $where . ' AND (';
        $whereResult = self::filter($request, $columns, $bindings);
        
        if ($whereResult) {
            $where .= $whereResult . ')';
        } else {
            $where = str_replace('WHERE ', '', $where);
            $where = str_replace(' AND (', '', $where);
        }
        
        return $where;
    }

    static function bind(&$a, $val, $type) {
        $key = ':binding_' . count($a);
        $a[] = array('key' => $key, 'val' => $val, 'type' => $type);
        return $key;
    }

    static function pluck($a, $prop) {
        $out = array();
        for ($i = 0, $len = count($a); $i < $len; $i++) {
            $out[] = $a[$i][$prop];
        }
        return $out;
    }

    static function db($sql_details) {
        try {
            $db = @new PDO(
                "mysql:host={$sql_details['host']};dbname={$sql_details['db']};charset=utf8",
                $sql_details['user'],
                $sql_details['pass'],
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch (PDOException $e) {
            self::fatal("An error occurred while connecting to the database. The error reported by the server was: " . $e->getMessage());
        }
        return $db;
    }

    static function sql_exec($db, $bindings, $sql = null) {
        // Argument shifting
        if ($sql === null) {
            $sql = $bindings;
        }
        
        $stmt = $db->prepare($sql);
        
        // Bind parameters
        if (is_array($bindings)) {
            for ($i = 0, $ien = count($bindings); $i < $ien; $i++) {
                $binding = $bindings[$i];
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }
        
        // Execute
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            self::fatal("An SQL error occurred: " . $e->getMessage());
        }
        
        // Return all
        return $stmt->fetchAll(PDO::FETCH_BOTH);
    }

    static function fatal($msg) {
        echo json_encode(array(
            "error" => $msg
        ));
        
        exit(0);
    }
}