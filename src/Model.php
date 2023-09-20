<?php

namespace Nskdmitry\Cutlink;

class Model {
    protected \PDO $db;
    protected static string $table = "links";
    protected static array $props = ['full', 'short'];

    public string $full;
    public string $short;
    protected bool $new = true;

    public function __construct(\PDO $db) {
        $this->db = $db;
        if (!$this->db) {
            $this->db = static::getConnection();
        }
    }

    public function uploaded() {
        $this->new = false;
    }

    public function all(): array {
        $list = [];
        $q = $this->db->query("SELECT * FROM links");
        $all = $q->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($all as $arr) {
            $model = new Model();
            $model->id = intval($arr['id']);
            $model->full = htmlentities($arr['full']);
            $model->short = htmlentities($arr['short']);
            $model->new = false;
            $list[] = $model;
        }
        return $list;
    }

    public function save() {
        $columns = [];
        $values = [];
        foreach (static::$props as $column) {
            $columns[] = $column;
            $val = (is_string($this->$column)) ? "'{$this->$column}'" : $this->$column;
            $values[] = $val;
        }
        $table = static::$table;

        if ($this->new || $this->id == 0) {
            $columns = implode(", ", $columns);
            $values = implode(", ", $values);
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ($values);";
        } else {
            $sql = "UPDATE {$table} WHERE id={$this->id} SET short='{$this->short}'";
        }
        $this->new = $this->db->exec($sql) > 0;
    }

    public function getCount() {
        $q = $this->db->query("SELECT COUNT(id) FROM links");
        return $q->fetchColumn();
    }

    public static function findBy(string $column, string $value): Model {
        $db = static::getConnection();

        $table = static::$table;
        $sql = "SELECT *, ROWID FROM {$table} WHERE {$column}={$value};";
        try {
            $res = $db->query($sql);
            $data = $res->fetch(\PDO::FETCH_ASSOC);
            $result = new Model($db);
            foreach ($data as $column => $value) {
                $result->$column = $value;
            }
            $result->id = \intval($data['ROWID']);
            $result->uploaded();
        } catch (\Exception $ex) {
            echo "<p style='background-color: red;'>{$ex->getMessage()}<br/>Line {$ex->getLine()}</p>";
        }
        return $result;
    }

    public static function makeShort(int $number, \PDO $db): string {
        $stepSize = count(static::$chars);
        $code = "";
        while ($number > $stepSize - 1) {
            $code = static::$chars[fmod($number, $stepSize)] . $code;
            $number = floor($number / $stepSize);
        }
        $code = static::$chars[$number].$code;
        return static::getShortUrl($code);
    }

    public static function getShortUrl(string $code): string {
        return "http://{$_SERVER['SERVER_NAME']}/{$code}";
    }

    protected static function getConnection(): \PDO {
        $config = require_once(__DIR__ . '/config/db.php');
        return new \PDO($config['dsn'], $config['user'], $config['passw'], $config['attr']);
    }

    public static function existsUrl(string $url): bool {
        $curl = curl_init($url);
        //curl_setopt_array($curl, []);
        curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        return $status < 400;
    }

    protected static array $chars = [
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 
        'a','b','c','d','e','f','g','h','k','l','m','n','o','p','q','r','s','t','u','w','x','y','z',
        'A','B','C','D','E','F','G','H','K','L','M','N','O','P','Q','R','S','T','U','W','X','Y','Z'
    ];
}