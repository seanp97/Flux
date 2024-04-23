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