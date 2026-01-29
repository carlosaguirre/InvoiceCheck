<?php
require_once "clases/Tareas.php";
class TareaTest implements Tarea {
    public function prueba() {
        return true;
    }
    public function ejecuta() {
        echo "Test Ejecuta exitoso!";
    }
}