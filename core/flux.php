<?php

class Flux {
  public $pdo;

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

    public function Query($query, $params = null) {
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

    private function MapData($data) {
        $mappedData = array();
        
        foreach($data as $d) {
            $mappedData[] = (object) $d;
        }
        
        return $mappedData;
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