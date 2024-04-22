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
}

