<?php

class DataBase extends PDO
{
    private $dbh = NULL;

    public function __construct($conName = 'conn1')
    {
        $this->$conName();
    }

    public function __call($name, $arguments)
    {
        $this->conn1();
    }

    private function conn1()
    {
        try {
            $this->dbh = new PDO('mysql:host=localhost;dbname=DBNAME;charset=utf8', 'root', 'pass');
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbh->exec("SET character_set_results=utf8;");
            $this->dbh->exec("SET character_set_client=utf8;");
            $this->dbh->exec("SET character_set_connection=utf8;");
            $this->dbh->exec("SET character_set_database=utf8;");
            $this->dbh->exec("SET character_set_server=utf8;");
        }
        catch(PDOException $e){
            throw new Exception("Connection failed: " . $e->getMessage());
        }

    }


    public function read($tableName, $fields = array(), $where = array(), $operations = '=', $andOr = '', $options = '', $mode = 'm')
    {
        if ($this->dbh) {
            if (count($fields) > 0) {
                foreach ($fields as $key => $value) {
                    if (strpos(strtolower($value), 'as') !== false) {
                        $fieldsArray[] =  $value ;
                    }else{
                        $fieldsArray[] = '`' . $value . '`';
                    }
                }

                $fieldsText = implode(',', $fieldsArray);
            }
            else {
                $fieldsText = '*';
            }

            $whereArrayCount = count($where);

            if ($whereArrayCount > 0) {
                $whereText = 'WHERE (';

                $andOrArray = explode('-', $andOr);
                $andOrArrayCount = count($andOrArray);

                $operationsArray = explode('-', $operations);
                $operationsArrayCount = count($operationsArray);

                if ($andOr != '' && $andOrArrayCount == $whereArrayCount - 1 && $operationsArrayCount == $whereArrayCount) {

                    $i = 0; // for operations
                    $j = 0; // for AND OR

                

                    foreach ($where as $key => $value) {
                        $whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . ' ' . (isset($andOrArray[$j]) ? $andOrArray[$j] : '') . ' ';
                        $i++;
                        $j++;
                    }


                }
                elseif ($andOr == '' && $andOrArrayCount == $whereArrayCount && $operationsArrayCount == $whereArrayCount) {

                    $i = 0; // for operations

                
                    foreach ($where as $key => $value) {
                        $whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . '  ';
                        $i++;
                    }


                }
                else {
                    die("count operations and where is not ok ");
                }
            
                $whereText = substr($whereText, 0, -2) . ')';

                if (strlen($options) > 0) {
                    $query = "SELECT {$fieldsText} FROM `{$tableName}` {$whereText} {$options}";
                }
                elseif (strlen($options) == 0) {
                    $query = "SELECT {$fieldsText} FROM `{$tableName}` {$whereText}";
                }


                $stmt = $this->dbh->prepare($query);

                // jadid

                if (in_array('IN', $operationsArray)) {
                    $k = 0;

                    $keysArray = array_keys($where);

                    $valuesArray = array_values($where);

                    for ($k = 0; $k < $operationsArrayCount - 1; $k++) {
                        $stmt->bindValue(':' . $keysArray[$k], $valuesArray[$k]);
                    }
                }
                else {
                    foreach ($where as $key => $value) {
                        $stmt->bindValue(':' . $key, $value);
                    }
                }


                // end jadid

                $stmt->execute();
            }
            else {
                if (strlen($options) > 0) {
                    $query = "SELECT {$fieldsText} FROM `{$tableName}` {$options}";
                }
                elseif (strlen($options) == 0) {
                    $query = "SELECT {$fieldsText} FROM `{$tableName}`";
                }

                $stmt = $this->dbh->prepare($query);
                $stmt->execute();
            }

            if ($stmt && $stmt->rowCount() > 0) {

                switch ((string)$mode) {
                    case 's':
                        return $stmt->fetchObject();
                        break;
                    case 'm':
                        return $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;
                    case 'count':
                        return $stmt->rowCount();
                        break;
                }
                
            }
            else {
                return array();
            }
        }
    }

    public function create($tableName, $inputs = array())
    {
        if ($this->dbh && count($inputs) > 0) {
            foreach ($inputs as $key => $value) {
                $fields[] = '`' . $key . '`';
                $values[] = ':' . $key;
            }

            $fields = implode(',', $fields);
            $values = implode(',', $values);

            $stmt = $this->dbh->prepare("INSERT INTO `{$tableName}` ($fields) VALUES ($values)");

            foreach ($inputs as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            if ($stmt && $stmt->rowCount()) {
                return $this->dbh->lastInsertId();
            }
            else {
                throw new Exception("Error: Khatayi Dar Darj Rokh Dadeh Ast!");
                return false;
            }
        }
    }

    public function delete($tableName, $where = array(), $operations = '=', $andOr = '')
    {
        $whereArrayCount = count($where);

        if ($this->dbh && $whereArrayCount > 0) {
            $whereText = 'WHERE (';

            $andOrArray = explode('-', $andOr);
            $andOrArrayCount = count($andOrArray);

            $operationsArray = explode('-', $operations);
            $operationsArrayCount = count($operationsArray);

            if ($andOr != '' && $andOrArrayCount == $whereArrayCount - 1 && $operationsArrayCount == $whereArrayCount) {

                $i = 0; // for operations
                $j = 0; // for AND OR

                foreach ($where as $key => $value) {
                    $whereText .= '`' . $key . '` ' . $operationsArray[$i] . ' :' . $key . ' ' . (isset($andOrArray[$j]) ? $andOrArray[$j] : '') . ' ';
                    $i++;
                    $j++;
                }
            }
            elseif ($andOr == '' && $andOrArrayCount == $whereArrayCount && $operationsArrayCount == $whereArrayCount) {

                $i = 0; // for operations

                foreach ($where as $key => $value) {
                    $whereText .= '`' . $key . '` ' . $operationsArray[$i] . ' :' . $key . '  ';
                    $i++;
                }
            }
            else {
                die("Counts operations and where Not ok ");
            }
            
            $whereText = substr($whereText, 0, -2) . ')';

            $stmt = $this->dbh->prepare("DELETE FROM `{$tableName}` {$whereText}");

            foreach ($where as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            if ($stmt && $stmt->rowCount()) {
                return true;
            }
            else {
                throw new Exception("Error: Error On delete !");
            }
        }
    }

    public function update($tableName, $update = array(), $where = array(), $operations = '=', $andOr = '')
    {
        $whereArrayCount = count($where);

        if ($this->dbh && count($update) > 0 && $whereArrayCount > 0) {
            $whereText = 'WHERE (';

            $andOrArray = explode('-', $andOr);
            $andOrArrayCount = count($andOrArray);

            $operationsArray = explode('-', $operations);
            $operationsArrayCount = count($operationsArray);

            if ($andOr != '' && $andOrArrayCount == $whereArrayCount - 1 && $operationsArrayCount == $whereArrayCount) {

                $i = 0; // for operations
                $j = 0; // for AND OR



                foreach ($where as $key => $value) {
                    $whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . ' ' . (isset($andOrArray[$j]) ? $andOrArray[$j] : '') . ' ';
                    $i++;
                    $j++;
                }

            }
            elseif ($andOr == '' && $andOrArrayCount == $whereArrayCount && $operationsArrayCount == $whereArrayCount) {

                $i = 0; // for operations

                foreach ($where as $key => $value) {
                    $whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . '  ';
                    $i++;
                }

            }
            else {
                die();
            }
            
            $whereText = substr($whereText, 0, -2) . ')';

            $updateText = '';

            foreach ($update as $key => $value) {
                $updateText .= '`' . $key . '` = :' . $key . ' , ';
            }

            $updateText = substr($updateText, 0, -3);

            $stmt = $this->dbh->prepare("UPDATE `{$tableName}` SET {$updateText} {$whereText}");

            foreach ($update as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            foreach ($where as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            if ($stmt && $stmt->rowCount()) {
                return true;
            }
            else {
                throw new Exception("Error: Error On Update !");
            }
        }
    }

    public function generalRead($tableName, $preText = '', $fields, $where = array(), $operations = '=', $andOr = '', $options = '', $mode = 'm')
    {
        if ($this->dbh && strlen($fields) > 0) {
            $whereArrayCount = count($where);

            if ($whereArrayCount > 0) {
                $whereText = 'WHERE (';

                $andOrArray = explode('-', $andOr);
                $andOrArrayCount = count($andOrArray);

                $operationsArray = explode('-', $operations);
                $operationsArrayCount = count($operationsArray);

                if ($andOr != '' && $andOrArrayCount == $whereArrayCount - 1 && $operationsArrayCount == $whereArrayCount) {

                    $i = 0; // for operations
                    $j = 0; // for AND OR


                    foreach ($where as $key => $value) {
                        $whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . ' ' . (isset($andOrArray[$j]) ? $andOrArray[$j] : '') . ' ';
                        $i++;
                        $j++;
                    }

                }
                elseif ($andOr == '' && $andOrArrayCount == $whereArrayCount && $operationsArrayCount == $whereArrayCount) {

                    $i = 0; // for operations


                    foreach ($where as $key => $value) {
                        $whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . '  ';
                        $i++;
                    }

                }
                else {
                    die();
                }
            
                $whereText = substr($whereText, 0, -2) . ')';

                $query = (strlen($preText) > 0 ? $preText . ' ' : '');

                if (strlen($options) > 0) {
                    $query .= "SELECT {$fields} FROM `{$tableName}` {$whereText} {$options}";
                }
                elseif (strlen($options) == 0) {
                    $query .= "SELECT {$fields} FROM `{$tableName}` {$whereText}";
                }
                $stmt = $this->dbh->prepare($query);

                foreach ($where as $key => $value) {
                    $stmt->bindValue(':' . $key, $value);
                }

                $stmt->execute();
            }
            else {
                $query = (strlen($preText) > 0 ? $preText . ' ' : '');

                if (strlen($options) > 0) {
                    $query .= "SELECT {$fields} FROM `{$tableName}` {$options}";
                }
                elseif (strlen($options) == 0) {
                    $query .= "SELECT {$fields} FROM `{$tableName}`";
                }
                $stmt = $this->dbh->prepare($query);
                $stmt->execute();
            }

            if ($stmt && $stmt->rowCount() > 0) {

                switch ((string)$mode) {
                    case 's':
                        return $stmt->fetchObject();
                        break;
                    case 'm':
                        return $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;
                    case 'count':
                        return $stmt->rowCount();
                        break;

                }
                
            }
            else {
                return array();
            }
        }
    }

    public function closeDataBase()
    {
        $this->dbh = NULL;
    }
}
