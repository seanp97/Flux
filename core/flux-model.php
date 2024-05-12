<?php

class FluxModel {
    private static $modelQueryBuilder = '';
    private static $modelQueryParams = [];

    public static function QueryBuilder() {
        return new self();
    }

    private static function MapData($data) {
        $mappedData = array();
        foreach ($data as $d) {
            $mappedData[] = (object) $d;
        }
        return $mappedData;
    }

    public static function InsertObject($object, $tableName, $cb = false) {
        try {
            $propertyNames = [];
            $propertyValues = [];
            foreach ($object as $propertyName => $propertyValue) {
                $propertyNames[] = $propertyName;
                $propertyValues[] = $propertyValue;
            }
    
            $columnNames = implode(', ', $propertyNames);
            $placeholders = rtrim(str_repeat('?, ', count($propertyValues)), ', ');
    
            $query = "INSERT INTO $tableName ($columnNames) VALUES ($placeholders)";
            
            $db = new Flux();
            $stmt = $db->pdo->prepare($query);
            $stmt->execute($propertyValues);

            if($cb) {
                $cb();
            }
            
        } catch (PDOException $e) {
            echo "PDOException: " . $e->getMessage();
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    }

    public static function ModelData() {
        try {
            $className = get_called_class();
            $reflection = new ReflectionClass($className);
            $object = $reflection->newInstanceWithoutConstructor();
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    
            $jsonData = json_decode(file_get_contents('php://input'), true);
    
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                
                if (isset($jsonData[$propertyName]) && !empty($jsonData[$propertyName])) {
                    $value = $jsonData[$propertyName];
                    $sanitizedValue = filter_var($value, FILTER_SANITIZE_STRING);
                    $object->$propertyName = $sanitizedValue;
                } 

                elseif (isset($_POST[$propertyName]) && !empty($_POST[$propertyName])) {
                    $value = $_POST[$propertyName];
                    $sanitizedValue = filter_var($value, FILTER_SANITIZE_STRING);
                    $object->$propertyName = $sanitizedValue;
                }
            }
    
            return $object;
        } catch (ReflectionException $e) {
            throw new Exception("ReflectionException: " . $e->getMessage());
        }
    }

    public static function Find($value) {
        $callingClass = get_called_class();
        self::$modelQueryBuilder = "SELECT * FROM $callingClass WHERE ";
        $properties = get_class_vars($callingClass);
        $first = true;
        foreach ($properties as $propertyName => $propertyValue) {
            if ($propertyName === 'modelQueryBuilder' || $propertyName === 'modelQueryParams') {
                continue;
            }
            if (!$first) {
                self::$modelQueryBuilder .= " OR ";
            } else {
                $first = false;
            }
            self::$modelQueryBuilder .= "$propertyName = $value";
            self::$modelQueryParams[] = $value;
        }
        return new self();
    }   

    public static function FirstProperty($className) {
        $reflectionClass = new ReflectionClass($className);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyType = $property->getType();
            if ($propertyType && ($propertyType->getName() === 'int' || $propertyType->getName() === 'integer')) {
                return $property->getName();
            }
        }
        return null;
    }

    private static function GetColumnType($phpType) {
        switch ($phpType) {
            case 'int':
            case 'integer':
                return 'INT';
            case 'float':
            case 'double':
                return 'FLOAT';
            case 'bool':
            case 'boolean':
                return 'BOOLEAN';
            case 'string':
                return 'TEXT(65535)';
            case 'datetime':
                return 'DATETIME';
            case 'date':
                return 'DATE';
            case 'time':
                return 'TIME';
            case 'text':
                return 'TEXT';
            case 'json':
                return 'JSON';
            default:
                return 'TEXT(65535)';
        }
    }

    public static function DeleteTable($cb = false) {
        try {
            $tableName = get_called_class();
            $db = new Flux();
            $sql = "DROP TABLE IF EXISTS $tableName";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();
    
            if($cb) {
                $cb();
            }
        }
        catch (PDOException $e) {
            echo "Error dropping table: " . $e->getMessage();
        }
    }

    public static function MigrateTable($cb = false) {
        try {
            $className = get_called_class();
            $reflection = new ReflectionClass($className);
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    
            $tableName = strtolower($className);
            $sqlDrop = "DROP TABLE IF EXISTS $tableName";
            $sqlCreate = "CREATE TABLE `$tableName` (";

    
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                $propertyType = 'TEXT(65530)';
            
                if (self::FirstProperty($className) && $propertyName === self::FirstProperty($className) && $propertyType != 'INT') {
                    $idColumnName = $className . 'Id';
                    $sqlCreate .= "$idColumnName INT AUTO_INCREMENT PRIMARY KEY ";
                }
            
                if ($propertyName !== self::FirstProperty($className)) {
                    if ($property->hasType()) {
                        $propertyType = self::GetColumnType($property->getType()->getName());
                    }
            
                    if ($propertyName !== self::FirstProperty($className)) {
                        $sqlCreate .= ", ";
                    }
            
                    $sqlCreate .= "$propertyName $propertyType";
                }
            }
                   
    
            $sqlCreate .= ')';
    
            $db = new Flux();
            $stmtDrop = $db->pdo->prepare($sqlDrop);
            $stmtDrop->execute();
    
            $stmtCreate = $db->pdo->prepare($sqlCreate);
            $stmtCreate->execute();
    
            if ($cb) {
                $cb();
            }
    
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error creating table: " . $e->getMessage());
        } catch (ReflectionException $e) {
            throw new Exception("ReflectionException: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Exception: " . $e->getMessage());
        }
    }   

    private function UpdateTable($className, $cb = false) {
        self::DeleteTable($className);
        self::MigrateTable($className);
    
        if ($cb) {
            $cb();
        }
    }

    public static function Count() {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT COUNT(*) FROM $callingClass";
        return new self();
    }

    public static function Max($field) {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT MAX($field) FROM $callingClass";
        return new self();
    }

    public static function Min($field) {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT MIN($field) FROM $callingClass";
        return new self();
    }

    public static function Sum($field) {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT SUM($field) FROM $callingClass";
        return new self();
    }

    public static function Avg($field) {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT AVG($field) FROM $callingClass";
        return new self();
    }

    public static function Like($q) {
        self::$modelQueryBuilder .= " LIKE '%$q%'";
        return new self();
    }
    
    public static function All() {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT * FROM $callingClass";
        return new self();
    }

    public static function OrderBy($field) {
        self::$modelQueryBuilder .= " ORDER BY $field";
        return new self();
    }

    public static function OrderByAsc($field) {
        self::$modelQueryBuilder .= " ORDER BY $field ASC";
        return new self();
    }

    public static function OrderByDesc($field) {
        self::$modelQueryBuilder .= " ORDER BY $field DESC";
        return new self();
    }

    public static function Where($q) {
        self::$modelQueryBuilder .= " WHERE $q ";
        return new self();
    }

    public static function Select($q) {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT $q FROM $callingClass";
        return new self();
    }

    public static function First() {
        self::$modelQueryBuilder .= " LIMIT 1 ";
        return new self();
    }

    public static function Limit($n) {
        self::$modelQueryBuilder .= " LIMIT $n ";
        return new self();
    }

    public static function And($q) {
        self::$modelQueryBuilder .= " AND $q ";
        return new self();
    }

    public static function Or($q) {
        self::$modelQueryBuilder .= " OR $q ";
        return new self();
    }

    public static function To($q) {
        self::$modelQueryBuilder .= " = '$q' ";
        return new self();
    }

    public static function Is($q) {
        self::$modelQueryBuilder .= " = '$q' ";
        return new self();
    }

    public static function Update() {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "UPDATE $callingClass ";
        return new self();
    }

    public static function Set($q) {
        self::$modelQueryBuilder .= "SET $q ";
        return new self();
    }

    public static function Delete() {
        try {
            $className = get_called_class();
            self::$modelQueryBuilder .= "DELETE FROM $className ";
            return new self();
        }
        catch (Exception $e) {
            throw new Exception("Error Processing Request", $e->getCode(), $e);
        }
    } 

    public static function Create($obj, $cb = false) {
        try {
            $db = new Flux();
            $callingClass = get_class($obj);
            $properties = get_object_vars($obj);
            $columns = implode(", ", array_keys($properties));
            $values = "'" . implode("', '", array_values($properties)) . "'";
            $sql = "INSERT INTO $callingClass ($columns) VALUES ($values)";
            $stmt = $db->Query($sql);

            if($cb) {
                $cb();
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function Exec() {
        $db = new Flux();
        $stmt = $db->Query(self::$modelQueryBuilder);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($data) == 1) {
            return (object) $data[0];
        }
        return self::MapData($data);
    }

    public static function DBQuery() {
        echo self::$modelQueryBuilder;
    }
}