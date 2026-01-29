<?php
class Config {
    // Static array to hold all configuration values
    private static array $settings = [];

    /**
     * Initialize configuration from an INI/.env file, once at bootstrap
     */
    public static function init(String $file): void {
        if (!file_exists($file)) self::$settings=["error"=>"No se encontró archivo de configuración","file"=>$file];
        else if (!is_readable($file)) self::$settings=["error"=>"No puede leer archivo de configuración","file"=>$file];
        else {
            self::$settings = parse_ini_file($file, true, INI_SCANNER_TYPED);
            if (self::$settings === false) self::$settings=["error"=>"Falló análisis del archivo de configuración","file"=>$file];
        }
    }

    /**
     * Generic getter: supports variadic keys or array of keys
     */
    public static function get(...$keys) {
        if (count($keys) === 1 && is_array($keys[0])) {
            $keys = $keys[0];
        }
        $value = self::$settings;
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Type‑casting helpers
     */
    public static function getString(...$keys): ?string {
        $val = self::get(...$keys);
        return $val !== null ? (string)$val : null;
    }

    public static function getInt(...$keys): ?int {
        $val = self::get(...$keys);
        return $val !== null ? (int)$val : null;
    }

    public static function getBool(...$keys): ?bool {
        $val = self::get(...$keys);
        if ($val === null) return false;
        // Normalize common truthy/falsey values
        if (is_bool($val)) return $val;
        $val = strtolower((string)$val);
        return in_array($val, ["1","true","yes","on"], true);
    }

    public static function getArray(...$keys): array {
        $val = self::get(...$keys);
        return is_array($val) ? $val : ($val !== null ? [$val] : []);
    }

    public static function default($value, $defaultValue) {
        if (!isset($value)) return $defaultValue;
        return $value;
    }

    // Convenience method for paths
    public static function getDocPath(): string {
        $sharePath = static::default(static::getString("project", "sharePath"), ".." . DIRECTORY_SEPARATOR);
        return $sharePath . "docs" . DIRECTORY_SEPARATOR;
    }

    // Optional: set or override a value at runtime
    public static function set(...$keysAndValue): void {
        $value = array_pop($keysAndValue);

        // If first argument is an array, use that as keys
        if (count($keysAndValue) === 1 && is_array($keysAndValue[0])) {
            $keys = $keysAndValue[0];
        } else {
            $keys = $keysAndValue;
        }

        // Walk down into settings by reference
        $ref = &self::$settings;
        foreach ($keys as $key) {
            if (!isset($ref[$key]) || !is_array($ref[$key])) {
                $ref[$key] = [];
            }
            $ref = &$ref[$key];
        }
        // Assign value
        $ref = $value;
    }
}
Config::set(null,["error"=>"Error Message"]);