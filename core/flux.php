<?php

class Flux {
  public $pdo;

  public $queryBuilder;

  function __construct() {
      $this->Connect();
  }

  function Connect() {
    $host = Configuration::$connection["servername"];
    $user = Configuration::$connection["username"];
    $pass = Configuration::$connection["password"];
    $db = Configuration::$connection["database"];

    try {
        $this->pdo = new PDO("mysql:host=$host;", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if(empty($db)) {
            $db = "FluxDB";
        }
        $this->CreateDatabase($db);
        
        $this->pdo->exec("USE $db");
    } catch(PDOException $e) {
        throw new Exception("Connection failed: " . $e->getMessage());
    }
  }

    private function CreateDatabase($databaseName) {
        try {
            $sql = "CREATE DATABASE IF NOT EXISTS $databaseName";
            $this->pdo->exec($sql);
        } catch(PDOException $e) {
            throw new Exception("Error creating database: " . $e->getMessage());
        }
    }

    public function query($query, $params = null) {
        try {
            if (!$params) {
                $stmt = $this->pdo->query($query);
                return $stmt;
            } else {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params);
                return $stmt;
            }
        } catch (PDOException $e) {
            throw new Exception("Error executing query: " . $e->getMessage());
        }
    }

  function Select($values = '') {
    if(!empty($values)) {
        $this->queryBuilder .= "SELECT $values";
    }
    else {
        $this->queryBuilder .= "SELECT";
    }
    
    return $this;
  }

    function All($table) {
        $this->queryBuilder .= "SELECT * FROM $table";
        return $this;
    }

    function GetColumnType($phpType) {
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

    public function FirstProperty($className) {
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

    function InsertObject($object, $tableName) {
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
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($propertyValues);
        } catch (PDOException $e) {
            echo "PDOException: " . $e->getMessage();
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    }

    function MigrateTable($className, $cb = false) {
        try {
            $reflection = new ReflectionClass($className);
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    
            $tableName = strtolower($className);
            $sqlDrop = "DROP TABLE IF EXISTS $tableName";
            $sqlCreate = "CREATE TABLE $tableName (";
    
            if ($this->FirstProperty($className) == NULL) {
                $idColumnName = $className . 'Id';
                $sqlCreate .= "$idColumnName INT AUTO_INCREMENT PRIMARY KEY, ";
            } else {
                $idColumnName = $this->FirstProperty($className);
                $sqlCreate .= "$idColumnName INT AUTO_INCREMENT PRIMARY KEY, ";
            }
    
            $firstProperty = true;
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                if ($propertyName === $this->FirstProperty($className)) {
                    // Skip the first property
                    continue;
                }
                $propertyType = 'VARCHAR(65530)';
                if ($propertyName !== 'id' && $property->hasType()) {
                    $propertyType = $this->GetColumnType($property->getType()->getName());
                }
                if (!$firstProperty) {
                    // Add a comma before adding new columns except for the first one
                    $sqlCreate .= ", ";
                } else {
                    $firstProperty = false;
                }
                $sqlCreate .= "$propertyName $propertyType";
            }
    
            $sqlCreate .= ')';
    
            $stmtDrop = $this->pdo->prepare($sqlDrop);
            $stmtDrop->execute();
    
            $stmtCreate = $this->pdo->prepare($sqlCreate);
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

    public function DeleteTable($tableName, $cb = false) {
        $sql = "DROP TABLE IF EXISTS $tableName";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        if($cb) {
            $cb();
        }
    }

    private function DropTable($tableName) {
        try {
            $sql = "DROP TABLE IF EXISTS $tableName";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error dropping table: " . $e->getMessage();
        }
    }


    private function UpdateTable($className, $cb = false) {
        $this->DropTable($className);
        $this->MigrateTable($className);
    
        if ($cb) {
            $cb();
        }
    }

    function GetModelData($className) {
        try {
            $reflection = new ReflectionClass($className);
            $object = $reflection->newInstanceWithoutConstructor();
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                if (isset($_POST[$propertyName])) {
                    $object->$propertyName = $_POST[$propertyName];
                }
            }
    
            return $object;
    
        } catch (ReflectionException $e) {
            echo "ReflectionException: " . $e->getMessage();
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    }

    function Exec() {
        try {
            $stmt = $this->pdo->prepare($this->queryBuilder);
            $stmt->execute();

            if (str_contains($this->queryBuilder, "COUNT")) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                return $data[array_key_first($data)];
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) == 1) {
                return $data[0];
            }

            return $data;
        } catch (PDOException $e) {
            throw new Exception("Error querying database: " . $e->getMessage());
        }
    }

    function From($table) {
        $this->queryBuilder .= " FROM $table";
        return $this;
    }

    function InsertInto($table) {
        $this->queryBuilder .= "INSERT INTO $table";
        return $this;
    }

    function Delete() {
        $this->queryBuilder .= "DELETE";
        return $this;
    }

    function Values($values) {
        $this->queryBuilder .= " VALUES($values)";
        return $this;
    }

    function Like($q) {
        $this->queryBuilder .= " LIKE '%$q%'";
        return $this;
    }

    function Update($table) {
        $this->queryBuilder .= "UPDATE $table";
        return $this;
    }

    function Set($value) {
        $this->queryBuilder .= " SET $value";
        return $this;
    }

    function Desc($q) {
        $this->queryBuilder .= " ORDER BY $q DESC";
        return $this;
    }

    function Asc($q) {
        $this->queryBuilder .= " ORDER BY $q ASC";
        return $this;
    }

    function Where($q) {
        $this->queryBuilder .= " WHERE $q";
        return $this;
    }

    function And($q) {
        $this->queryBuilder .= " AND $q";
        return $this;
    }

    function Or($q) {
        $this->queryBuilder .= " OR $q";
        return $this;
    }

    function Equals($e) {
        $this->queryBuilder .= " = $e";
        return $this;
    }

    function stored_proc($sp, $params = null) {
        if(!$params) {
        $stmt = $this->pdo->prepare("CALL $sp");
        $stmt->execute();
        return $stmt;
        }

        else {
        $stmt = $this->pdo->prepare("CALL $sp($params)");
        $stmt->bindParam(':param1', $params, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
        }
    }

}