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

    public static function HydratedPostModelData($className) {
        try {
            $reflection = new ReflectionClass($className);
            $object = $reflection->newInstanceWithoutConstructor();
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                if (isset($_POST[$propertyName]) && !empty($_POST[$propertyName])) {
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

    public static function Where($q) {
        self::$modelQueryBuilder .= " WHERE $q ";
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

    public static function Insert($obj) {
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
