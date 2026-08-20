<?php

require_once "config.php";

abstract class DataObject{
    protected $data = array();

    public function __construct($data) {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->data) ) $this->data[$key] = $value;
        }
    }

    public function getValue($field) {
        if (array_key_exists($field, $this->data)) {
            return htmlspecialchars((string)($this->data[$field] ?? ''));
        } else {
            trigger_error("Campo no encontrado: $field", E_USER_NOTICE);
            return null;
        }
    }

    public function getValueEncoded($field) {
        return htmlspecialchars($this->getValue($field));
    }

    protected static function connect() {
        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
    
    protected static function disconnect(&$conn) {
        $conn= null;
    }
}
?>
