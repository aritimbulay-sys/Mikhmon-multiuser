<?php
/*
 *  Penyimpanan data berbasis file JSON (pengganti DB) untuk Mikhmon multi-user.
 *  Data disimpan di include/dbdata/*.json. File JSON diblokir akses web oleh nginx.
 */
class FileDb {
    private $dir;
    private $lockFile;

    public function __construct($dir) {
        $this->dir = rtrim($dir, '/');
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0775, true);
        }
        $this->lockFile = $this->dir . '/.db.lock';
    }

    private function tableFile($table) {
        return $this->dir . '/' . preg_replace('/[^a-z0-9_]/i', '', $table) . '.json';
    }

    private function withLock($callback, $exclusive = true) {
        $fp = fopen($this->lockFile, 'c+');
        if (!$fp) {
            throw new Exception('Cannot open db lock');
        }
        flock($fp, $exclusive ? LOCK_EX : LOCK_SH);
        try {
            $result = $callback();
        } catch (Exception $e) {
            flock($fp, LOCK_UN);
            fclose($fp);
            throw $e;
        }
        flock($fp, LOCK_UN);
        fclose($fp);
        return $result;
    }

    private function readTable($table) {
        $file = $this->tableFile($table);
        if (!file_exists($file)) {
            return array();
        }
        $raw = file_get_contents($file);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : array();
    }

    private function writeTable($table, $rows) {
        $file = $this->tableFile($table);
        $tmp = $file . '.tmp';
        file_put_contents($tmp, json_encode(array_values($rows), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        rename($tmp, $file);
    }

    private function readSeq() {
        $file = $this->dir . '/.seq.json';
        if (!file_exists($file)) {
            return array();
        }
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : array();
    }

    private function writeSeq($seq) {
        $file = $this->dir . '/.seq.json';
        $tmp = $file . '.tmp';
        file_put_contents($tmp, json_encode($seq, JSON_UNESCAPED_SLASHES));
        rename($tmp, $file);
    }

    private function nextId($table) {
        return $this->withLock(function () use ($table) {
            $seq = $this->readSeq();
            $id = isset($seq[$table]) ? (int)$seq[$table] + 1 : 1;
            $seq[$table] = $id;
            $this->writeSeq($seq);
            return $id;
        }, true);
    }

    public function ensureTable($table) {
        $file = $this->tableFile($table);
        if (!file_exists($file)) {
            $this->writeTable($table, array());
        }
    }

    public function all($table) {
        return $this->withLock(function () use ($table) {
            $this->ensureTable($table);
            return $this->readTable($table);
        }, false);
    }

    public function find($table, $filter = null) {
        $rows = $this->all($table);
        if ($filter === null) {
            return $rows;
        }
        $out = array();
        foreach ($rows as $row) {
            $ok = true;
            foreach ($filter as $k => $v) {
                if (!array_key_exists($k, $row) || (string)$row[$k] !== (string)$v) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                $out[] = $row;
            }
        }
        return array_values($out);
    }

    public function findOne($table, $filter) {
        $rows = $this->find($table, $filter);
        return count($rows) ? $rows[0] : null;
    }

    public function insert($table, $row) {
        return $this->withLock(function () use ($table, $row) {
            $this->ensureTable($table);
            $rows = $this->readTable($table);
            $row['id'] = $this->nextIdUnlocked($table);
            $rows[] = $row;
            $this->writeTable($table, $rows);
            return $row['id'];
        }, true);
    }

    private function nextIdUnlocked($table) {
        $seq = $this->readSeq();
        $id = isset($seq[$table]) ? (int)$seq[$table] + 1 : 1;
        $seq[$table] = $id;
        $this->writeSeq($seq);
        return $id;
    }

    public function update($table, $id, $fields) {
        return $this->withLock(function () use ($table, $id, $fields) {
            $this->ensureTable($table);
            $rows = $this->readTable($table);
            $found = false;
            foreach ($rows as &$row) {
                if (isset($row['id']) && (string)$row['id'] === (string)$id) {
                    foreach ($fields as $k => $v) {
                        $row[$k] = $v;
                    }
                    $found = true;
                    break;
                }
            }
            unset($row);
            if ($found) {
                $this->writeTable($table, $rows);
            }
            return $found;
        }, true);
    }

    public function delete($table, $id) {
        return $this->withLock(function () use ($table, $id) {
            $this->ensureTable($table);
            $rows = $this->readTable($table);
            $new = array();
            $deleted = false;
            foreach ($rows as $row) {
                if (isset($row['id']) && (string)$row['id'] === (string)$id) {
                    $deleted = true;
                } else {
                    $new[] = $row;
                }
            }
            if ($deleted) {
                $this->writeTable($table, $new);
            }
            return $deleted;
        }, true);
    }

    public function count($table, $filter = null) {
        return count($this->find($table, $filter));
    }
}
?>