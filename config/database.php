<?php
// Konfigurasi Database
class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'lombabcf';
    private $connection;

    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Query error: " . $e->getMessage());
        }
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        // Check if the table has an auto-increment column
        $columns = $this->fetchAll("SHOW COLUMNS FROM {$table}");
        $hasAutoIncrement = false;
        $autoIncrementColumn = null;
        
        foreach ($columns as $column) {
            if (strpos($column['Extra'], 'auto_increment') !== false) {
                $hasAutoIncrement = true;
                $autoIncrementColumn = $column['Field'];
                break;
            }
        }
        
        if ($hasAutoIncrement) {
            $lastId = $this->connection->lastInsertId();
            return $lastId ? $lastId : true; // Return true if insert successful but no ID
        } else {
            // For tables without auto-increment, return the ID from data if it exists
            return isset($data['id']) ? $data['id'] : true;
        }
    }

    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        $setParams = [];
        $paramIndex = 0;
        
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = ?";
            $setParams[] = $value;
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($setParams, $whereParams);
        
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }
}

// Fungsi helper untuk mendapatkan instance database
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db;
}

// Fungsi untuk mendapatkan pengaturan
function getSetting($nama) {
    $db = getDB();
    $result = $db->fetch("SELECT nilai FROM pengaturan WHERE nama = ?", [$nama]);
    return $result ? $result['nilai'] : null;
}

// Fungsi untuk mendapatkan kategori lomba
function getKategoriLomba() {
    $db = getDB();
    return $db->fetchAll("SELECT * FROM kategori_lomba ORDER BY id");
}

// Fungsi untuk mendapatkan kategori lomba by ID
function getKategoriLombaById($id) {
    $db = getDB();
    return $db->fetch("SELECT * FROM kategori_lomba WHERE id = ?", [$id]);
}

// Fungsi untuk mengecek apakah pendaftaran masih dibuka
function isPendaftaranDibuka() {
    $tanggalSekarang = date('Y-m-d');
    $tanggalMulai = getSetting('tanggal_mulai_pendaftaran');
    $tanggalAkhir = getSetting('tanggal_akhir_pendaftaran');
    
    return $tanggalSekarang >= $tanggalMulai && $tanggalSekarang <= $tanggalAkhir;
}

// Fungsi untuk mengecek apakah pengumpulan karya masih dibuka
function isPengumpulanDibuka() {
    $tanggalSekarang = date('Y-m-d');
    $tanggalMulai = getSetting('tanggal_mulai_pengumpulan');
    $tanggalAkhir = getSetting('tanggal_akhir_pengumpulan');
    
    return $tanggalSekarang >= $tanggalMulai && $tanggalSekarang <= $tanggalAkhir;
}
?>
