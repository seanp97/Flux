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
        try {
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
        catch(Exception $e) {
            throw new Exception('Find failed: ' . $e->getMessage());
        }
    }   

    public static function FindOne($value) {
        try {
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
    
            self::$modelQueryBuilder .= " LIMIT 1";
            return new self();
        }

        catch(Exception $e) {
            throw new Exception('FindOne failed: ' . $e->getMessage());
        }

    } 

    public static function FirstProperty($className) {
        try {
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
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private static function GetColumnType($phpType) {
        try {
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
        catch(Exception $e) {
            throw new Exception($e->getMessage());
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
            $sqlDrop = "DROP TABLE IF EXISTS `$tableName`";
            $columns = [];
    
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                $propertyType = 'TEXT(65530)';
    
                if (self::FirstProperty($className) && $propertyName === self::FirstProperty($className)) {
                    $columns[] = "$propertyName INT AUTO_INCREMENT PRIMARY KEY"; 
                    continue;
                }
    
                if ($property->hasType()) {
                    $typeName = strtolower($property->getType()->getName());
                    $propertyType = self::GetColumnType($typeName);
    
                    if ($typeName === 'date') {
                        $propertyType = 'DATE';
                    } elseif ($typeName === 'datetime') {
                        $propertyType = 'DATETIME';
                    } elseif ($typeName === 'json') {
                        $propertyType = 'JSON';
                    }
                }
    
                $columns[] = "$propertyName $propertyType";
            }
    
            $sqlCreate = $sqlDrop . "; CREATE TABLE `$tableName` (" . implode(", ", $columns) . ");";
    
            if ($cb) {
                $cb($sqlCreate);
            }
    
            return $sqlCreate;
    
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
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
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder .= "SELECT COUNT(*) FROM $callingClass";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Count Exception: " . $e->getMessage());
        }
    }

    public static function Max($field) {
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder .= "SELECT MAX($field) FROM $callingClass";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Max Exception: " . $e->getMessage());
        }
    }

    public static function Min($field) {
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder .= "SELECT MIN($field) FROM $callingClass";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Min Exception: " . $e->getMessage());
        }
    }

    public static function Sum($field) {
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder .= "SELECT SUM($field) FROM $callingClass";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Sum Exception: " . $e->getMessage());
        }
    }

    public static function Avg($field) {
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder .= "SELECT AVG($field) FROM $callingClass";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Avg Exception: " . $e->getMessage());
        }
    }

    public static function Like($q) {
        try {
            self::$modelQueryBuilder .= " LIKE '%$q%'";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Like Exception: " . $e->getMessage());
        }
    }
    
    public static function All() {
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder .= "SELECT * FROM $callingClass";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("All Exception: " . $e->getMessage());
        }
    }

    public static function OrderBy($field) {
        try {
            self::$modelQueryBuilder .= " ORDER BY $field";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("OrderBy Exception: " . $e->getMessage());
        }
    }

    public static function OrderByAsc($field) {
        try {
            self::$modelQueryBuilder .= " ORDER BY $field ASC";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("OrderByAsc Exception: " . $e->getMessage());
        }
    }

    public static function OrderByDesc($field) {
        try {
            self::$modelQueryBuilder .= " ORDER BY $field DESC";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("OrderByDesc Exception: " . $e->getMessage());
        }
    }

    public static function Where($q) {
        try {
            self::$modelQueryBuilder .= " WHERE $q ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Where Exception: " . $e->getMessage());
        }
    }

    public static function Select($q) {
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder .= "SELECT $q FROM $callingClass";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Select Exception: " . $e->getMessage());
        }
    }

    public static function First() {
        try {
            self::$modelQueryBuilder .= " LIMIT 1 ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("First Exception: " . $e->getMessage());
        }
    }

    public static function Limit($n) {
        try {
            self::$modelQueryBuilder .= " LIMIT $n ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Limit Exception: " . $e->getMessage());
        }
    }

    public static function And($q) {
        try {
            self::$modelQueryBuilder .= " AND $q ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("And Exception: " . $e->getMessage());
        }
    }

    public static function Or($q) {
        try {
            self::$modelQueryBuilder .= " OR $q ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Or Exception: " . $e->getMessage());
        }
    }

    public static function To($q) {
        try {
            self::$modelQueryBuilder .= " = '$q' ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("To Exception: " . $e->getMessage());
        }
    }

    public static function Is($q) {
        try {
            self::$modelQueryBuilder .= " = '$q' ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Is Exception: " . $e->getMessage());
        }
    }

    public static function Update() {
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder .= "UPDATE $callingClass ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Update Exception: " . $e->getMessage());
        }
    }

    public static function Set($q) {
        try {
            self::$modelQueryBuilder .= "SET $q ";
            return new self();
        }
        catch(Exception $e) {
            throw new Exception("Set Exception: " . $e->getMessage());
        }
    }

    /*public static function Delete() {
        try {
            $className = get_called_class();
            self::$modelQueryBuilder .= "DELETE FROM $className ";
            return new self();
        }
        catch (Exception $e) {
            throw new Exception("Error Processing Request", $e->getCode(), $e);
        }
    } */

    public static function Delete($value) {
        try {
            $callingClass = get_called_class();
            self::$modelQueryBuilder = "DELETE FROM $callingClass WHERE ";
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
        catch(Exception $e) {
            throw new Exception("Delete Exception: " . $e->getMessage());
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
            throw new Exception("Create Exception: " . $e->getMessage());
        }
    }

    public static function Exec() {
        try {
            $db = new Flux();
            $stmt = $db->Query(self::$modelQueryBuilder);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($data) == 1) {
                return (object) $data[0];
            }
            return self::MapData($data);
        }
        catch (Exception $e) {
            throw new Exception("Exec Exception: " . $e->getMessage());
        }
    }

    public static function DBQuery() {
        echo self::$modelQueryBuilder;
    }
}
