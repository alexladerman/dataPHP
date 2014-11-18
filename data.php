<?php
// Opens a connection to the database
mysql_connect('localhost', 'username', 'password');
mysql_select_db('database');

class Data {

    public static $TableName;
    public static $ClassName;
    public static $IDName;

    static function ToKeyValue($object) {
        return get_object_vars($object);
    }

    static function Create($object) {
        $query = 'INSERT INTO ' . static::$TableName . ' (';
        $dict = static::ToKeyValue($object);

        $isFirst = true;
        $fields = '';
        $values = '';
        foreach ($dict as $key => $val) {
            if ($key != static::$IDName) {
                if (!$isFirst) {
                    $fields .= ',';
                    $values .= ',';
                } else {
                    $isFirst = false;
                }
                $fields .= $key;
                $values .= '\'' . mysql_real_escape_string($val) . '\'';
            }
        }

        $query .= $fields . ') VALUES (' . $values . ')';

        if (!($result = mysql_query($query)))
            throw new Exception('Error in Data->Create: ' . $query);

        $mysql_insert_id = mysql_insert_id();

        return $mysql_insert_id;
    }

    static function Read($id) {
        $query = 'SELECT * FROM ' . static::$TableName . ' WHERE ' .
                static::$IDName . ' = ' . mysql_real_escape_string($id);

        if (!($result = mysql_query($query)))
            throw new Exception('Error in Data->Read: ' . $query);

        if (mysql_num_rows($result) == 1)
            return static::RowToObject(mysql_fetch_array($result));
        elseif (mysql_num_rows($result) == 0)
            return null;
        else
            throw new Exception('Error in Data->Read: ' . $query);
    }

    static function Update($object) {
        $query = 'UPDATE ' . static::$TableName . ' SET ';
        $dict = static::ToKeyValue($object);

        $isFirst = true;

        foreach ($dict as $key => $val) {
            if ($key != static::$IDName) {
                if (!$isFirst)
                    $query .= ',';
                else
                    $isFirst = false;

                $query .= $key . ' = ';
                $query .= '\'' . mysql_real_escape_string($val) . '\'';
            }
        }

        $query .= ' WHERE ' . static::$IDName . ' = ' . $dict[static::$IDName];

        if (!($result = mysql_query($query)))
            throw new Exception('Error in Data->Update: ' . $query);
    }

    static function Delete($id) {
        $query = 'DELETE FROM ' . static::$TableName . ' WHERE ' . static::$IDName . ' = ' . mysql_real_escape_string($id);

        if (!($result = mysql_query($query)))
            throw new Exception('Error in Data->Delete: ' . $query);
    }

    static function Exists($object) {
        $dict = static::ToKeyValue($object);

        $query = 'SELECT COUNT(*) FROM %s WHERE %sID = %d';
        $query = sprintf($query, static::$TableName, static::$ClassName, mysql_real_escape_string($dict[static::$IDName]));

        return (self::GetSQL($query) != 0);
    }

    static function RowToObject($row) {
        $object = new static::$ClassName;

        foreach (get_class_vars(static::$ClassName) as $key => $val) {
            $object->$key = $row[$key];
        }

        return $object;
    }

    //Returns the value of the first field of the first row
    static function GetSQL($query) {
        $result = mysql_query($query);

        if (mysql_num_rows($result) != 0)
            return mysql_result($result, 0, 0);

        return null;
    }

    //Runs an SQL query
    static function RunSQL($query) {
        $result = mysql_query($query);
    }

    //Returns all records as an array
    static function GetRecordset($query) {
        $rs = array();

        if (!($result = mysql_query($query)))
            throw new Exception('Error in Data->GetRecordset: ' . $query);

        while ($row = mysql_fetch_array($result)) {
            $rs[] = $row;
        }

        return $rs;
    }

    //Returns all records as an array of objects
    static function GetRecordsetObject($query) {
        $rs = array();

        if (!($result = mysql_query($query)))
            throw new Exception('Error in Data->GetRecordsetObject: ' . $query);

        while ($row = mysql_fetch_object($result)) {
            $rs[] = $row;
        }

        return $rs;
    }
}
?>