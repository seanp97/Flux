<?php

class FluxModel {
    private static $modelQueryBuilder = '';

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

    public static function HydratedPostModelData() {
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

    public static function All() {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT * FROM $callingClass";
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
                return 'VARCHAR(65530)';
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
                return 'VARCHAR(65530)';
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
            $sqlCreate = "CREATE TABLE $tableName (";
    
            if (self::FirstProperty($className) == NULL) {
                $idColumnName = $className . 'Id';
                $sqlCreate .= "$idColumnName INT AUTO_INCREMENT PRIMARY KEY, ";
            } else {
                $idColumnName = self::FirstProperty($className);
                $sqlCreate .= "$idColumnName INT AUTO_INCREMENT PRIMARY KEY, ";
            }
    
            $firstProperty = true;
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                if ($propertyName === self::FirstProperty($className)) {
                    continue;
                }
                $propertyType = 'VARCHAR(65530)';
                if ($property->hasType()) {
                    $propertyType = self::GetColumnType($property->getType()->getName());
                }
                if (!$firstProperty) {
                    $sqlCreate .= ", ";
                } else {
                    $firstProperty = false;
                }
                $sqlCreate .= "$propertyName $propertyType";
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

    public static function Where($q) {
        self::$modelQueryBuilder .= " WHERE $q ";
        return new self();
    }

    public static function Select($q) {
        $callingClass = get_called_class();
        self::$modelQueryBuilder .= "SELECT $q FROM $callingClass";
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

    public static function Insert($obj, $cb = false) {
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
}
