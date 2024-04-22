<?php 

class ModelLoad {

    public function All() {
        $db = new Flux();
        $callingClass = get_called_class();
        $callingClass = strval($callingClass);
        
        $query = "SELECT * FROM $callingClass";
        
        $stmt = $db->Query($query);

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function Insert($obj) {
        $db = new Flux();
        $callingClass = get_class($obj);
    
        $properties = get_object_vars($obj);
        $columns = implode(", ", array_keys($properties));
        $values = "'" . implode("', '", array_values($properties)) . "'";
    
        $sql = "INSERT INTO $callingClass ($columns) VALUES ($values)";
        echo $sql;
        
        // Execute the SQL query
        $db->Query($sql);
    }
    
    
}

