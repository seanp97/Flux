<?php 

class FluxModel {

    public $modelQueryBuilder;

    public function QueryBuilder() {
        return $this->modelQueryBuilder;
    }

    private function MapData($data) {
        $mappedData = array();
        
        foreach($data as $d) {
            $mappedData[] = (object) $d;
        }
        
        return $mappedData;
    }

    public function All() {
        $callingClass = get_called_class();
        $callingClass = strval($callingClass);
        $this->modelQueryBuilder .= "SELECT * FROM $callingClass";
        return $this;
    }

    public function Where($q) {
        $this->modelQueryBuilder .= " WHERE $q ";
        return $this;
    }

    public function And($q) {
        $this->modelQueryBuilder .= " AND $q ";
        return $this;
    }

    public function Or($q) {
        $this->modelQueryBuilder .= " OR $q ";
        return $this;
    }

    public function To($q) {
        $this->modelQueryBuilder .= " = '$q' ";
        return $this;
    }

    public function Is($q) { 
        $this->modelQueryBuilder .= " = '$q' ";
        return $this;
    }
    
    public function Update() {
        $callingClass = get_called_class();
        $callingClass = strval($callingClass);
        $this->modelQueryBuilder .= "UPDATE $callingClass ";
        return $this;
    }

    public function Set($q) {
        $this->modelQueryBuilder .= "SET $q ";
        return $this;
    }

    public function Insert($obj) {
        $db = new Flux();
        $callingClass = get_class($obj);
    
        $properties = get_object_vars($obj);
        $columns = implode(", ", array_keys($properties));
        $values = "'" . implode("', '", array_values($properties)) . "'";
        $sql = "INSERT INTO $callingClass ($columns) VALUES ($values)";

        $stmt = $db->Query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function Exec() {
        $db = new Flux();
        $stmt = $db->Query($this->modelQueryBuilder);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($data) == 1) {
            return (object) $data[0];
        }
        return $this->MapData($data);
    }
    
    
}

