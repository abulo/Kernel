<?php

namespace Kernel\Asyn\Mysql;

class Pdo
{
    /**
     * 执行 sql 时绑定的参数
     *
     * @var array
     */
    protected $bindParams = [];
    /**
     * @var array
     */
    protected $conf; // 配置
    /**
     *
     * @var string
     */
    protected $sql;
    /**
     * @var array 操作的表
     */
    protected $table = array();


    /**
     * 数据库连接串
     *
     * @param $conf
     */
    public function __construct($conf)
    {
        $this->conf = $conf;
        $this->link = $this->connect($this->conf);
    }


    /**
     * 获取当前 db 所有表名
     *
     * @return array
     */
    public function getTables($dbname)
    {
        $stmt = $this->prepare('SHOW TABLE STATUS FROM '.$dbname, $this->link);
        $this->execute($stmt);
        $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $return;
    }
    /**
     * 获取表字段
     *
     * @param string $table 表名
     * @param mixed $tablePrefix 表前缀 为 null 时代表 table 已经带了前缀
     * @param int $filter 0 获取表字段详细信息数组 1 获取字段以, 号相隔组成的字符串
     *
     * @return mixed
     */
    public function getDbFields($table)
    {
        $info = [];
        $stmt = $this->prepare("SHOW FULL COLUMNS FROM $table", $this->link, false);
        $this->execute($stmt, array(), false);
        $info = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $info[$row['Field']] = array(
                'name'    => $row['Field'],
                'type'    => $row['Type'],
                'encode'  => $row['Collation'],
                'notnull' => (bool) ($row['Null'] === ''), // not null is empty, null is yes
                'default' => $row['Default'],
                'primary' => (strtolower($row['Key']) == 'pri'),
                'autoinc' => (strtolower($row['Extra']) == 'auto_increment'),
                'comment' => $row['Comment'],
            );
        }
        return $info;
    }
    /**
     * 根据 key 取出数据
     *
     * @param  string $key   主键;
     * @param  string $table 表名;
     * @param  string $field 字段
     * @return array array('uid'=>123, 'username'=>'abc')/false
     */
    public function get($key, $table, $field = array())
    {
        $fields = '*';
        if (!empty($field) && is_array($field)) {
            $fields = implode(',', $field);
        }
        $this->sql = "SELECT {$fields} FROM {$table} WHERE `id`=:id  Limit 1";
        $this->bindParams = [':id'=>$key];
        return $this;
    }
    /**
     * 根据 key 新增 一条数据
     *
     * @param  array  $data  eg: array('username'=>'admin', 'email'=>'xxx@live.com')
     * @param  string $table 表名;
     * @return bool
     */
    public function insert($table, $data)
    {
        if (!is_array($data)) {
            return false;
        }
        $s = $this->arrToCondition($data, $table);
        $this->sql = "INSERT INTO {$table} SET {$s}";
        return $this;
    }
    /**
     * 根据 key 更新一条数据
     *
     * @param string $table     eg 'table'
     * @param array  $data      eg: array('username'=>'admin', 'email'=>'xxx@live.com')
     * @param string $where     username=:username and password=:password
     * @param array  $condition array(':username'=>'aaa',':password'=>'xxxxxx');
     *
     * @return boolean
     */
    public function update($table, $data, $where, $condition)
    {
        if (empty($table)) {
            return false;
        }
        $s = $this->arrToCondition($data, $table);
        if (empty($where)) {
            return false;
        }
        $this->bindParams = array_merge($this->bindParams, $condition);
        $this->sql="UPDATE {$table} SET {$s} WHERE {$where}";
        return $this;
    }
    /**
     *
     * @param string $table     eg 'table'
     * @param string $where     username=:username and password=:password
     * @param array  $condition array(':username'=>'aaa',':password'=>'xxxxxx');
     *
     * @return boolean
     */
    public function delete($table, $where = null, $condition = [])
    {
        if (empty($table)) {
            return false;
        }
        $sql = "DELETE FROM {$table} ";
        if ($where) {
               $sql .= " WHERE ".$where;
        }
        $this->bindParams = array_merge($this->bindParams, $condition);
        $this->sql = $sql;
        return $this;
    }
    /**
     * 根据表名删除数据
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function truncate($tableName)
    {
        $stmt = $this->prepare("TRUNCATE {$tableName}");
        return $stmt->execute();// 不存在会报错，但无关紧要
    }
    /**
     * 通过 Sql 语句获取
     *
     * @param  [string] $sql
     * @param  array    $bindParams
     * @return
     */
    public function query($sql, $bindParams = array())
    {
        $this->bindParams = $bindParams;
        $this->sql = $sql;
        return $this;
    }
    /**
     * SQL 语句条件组装
     *
     * @param  array $arr; 要组装的数组
     * @return string
     */
    protected function arrToCondition($arr)
    {
        $s = $p = '';
        $params = array();
        foreach ($arr as $k => $v) {
            $p = "`{$k}`= :{$k}";
            $params[':'.$k] = $v;
            $s .= (empty($s) ? '' : ',').$p;
        }
        $this->bindParams = array_merge($params, $this->bindParams);
        return $s;
    }
    /**
     * 返回查询的 sql 语句
     *
     * @return string
     */
    public function toSql()
    {
        $r = $this->bindParams;
        $sql = $this->sql;
        $i = 0;
        $ret = preg_replace_callback(
            '/:([0-9a-z_]+)|\?+/i',
            function ($m) use ($r, &$i) {
                $k = array_keys($r);
                $v = $m[0] == '?' ? $r[$i] : (substr($k[$i], 0, 1) == ':' ? $r[$m[0]] : $r[$m[1]]);
                if ($v === null) {
                    return "NULL";
                }
                if (!is_numeric($v)) {
                    $v = "'{$v}'";
                }
                $i++;
                return $v;
            },
            $sql
        );
        return $ret;
    }
    /**
     * 获取所有数据, 以二维数组返回
     * @return array
     */
    public function getAll()
    {
        $stmt = $this->prepare($this->sql, $this->link);
        $this->execute($stmt, $this->bindParams);
        $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->sql = null;
        $this->bindParams = array();
        if (empty($return)) {
            return false;
        }
        return $return;
    }
    /**
     * 获取一条数据 以一维数据返回
     */
    public function getOne()
    {
        $stmt = $this->prepare($this->sql, $this->link);
        $this->execute($stmt, $this->bindParams);
        $return = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->sql = null;
        $this->bindParams = array();
        if (empty($return)) {
            return false;
        }
        return $return;
    }
    /**
     * 获取数据返回
     */
    public function getValue()
    {
        $stmt = $this->prepare($this->sql, $this->link);
        $this->execute($stmt, $this->bindParams);
        $return = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->sql = null;
        $this->bindParams = array();
        if (empty($return)) {
            return false;
        }
        $v = array_values($return);
        return $v[0];
    }
    /**
     * 返回 INSERT，UPDATE 或 DELETE 查询所影响的记录行数。
     *
     * @param $handle \PDOStatement
     *
     * @return int
     */
    public function affectedRows($handle)
    {
        return $handle->rowCount();
    }
    /**
     * 获取上一 INSERT 的主键值
     *
     * @param \PDO $link
     *
     * @return int
     */
    public function insertId($link = null)
    {
        is_null($link) && $link = $this->link;
        return $link->lastInsertId();
    }
    /**
     * Db 连接
     *
     * @param $conf
     * @param $charset
     * @param $enable_pool
     * @param bool|false $pConnect
     *
     * @return \Pdo
     */

    // host'=> '113.136.199.139',
    // 'port'=> 3306,
    // 'user'=> 'root',
    // 'password'=> 'Sxdx_2018!',
    // 'database'=> 'ott_Child',
    // 'charset'=> 'utf8mb4',

    public function connect($conf)
    {
        $link = '';
        try {
            $source = "mysql:host=".$conf['host'].";port=".$conf['port'].";dbname=".$conf['database'];
            $link = new \PDO($source, $conf['user'], $conf['password']);
        } catch (Exception $e) {
            throw new \Exception('PDO Connect Error! Code:'.$e->getCode().',ErrorInfo!:'.$e->getMessage().'<br />');
        }
        return $link;
    }
    /**
     * 预处理语句
     *
     * @param string $sql
     * @param \PDO $link
     * @param bool $resetParams
     *
     * @return \PDOStatement
     */
    public function prepare($sql, $link = null)
    {
        is_null($link) && $link = $this->link;
        try {
            return $link->prepare($sql);
        } catch (\PDOException $e) {
            throw new \Exception('Pdo Prepare Sql error! Code:'.$e->getCode().',ErrorInfo!:'.$e->getMessage().'<br />');
        }
        return false;
    }
    /**
     * 执行预处理语句
     *
     * @param object $stmt PDOStatement
     * @param array $param
     * @param bool $clearBindParams
     *
     * @return bool
     */
    private function execute($stmt, $param = array(), $clearBindParams = true)
    {
        empty($param) && $param = $this->bindParams;
        $clearBindParams && $this->bindParams = array();
        if (!$stmt->execute($param)) {
            $error = $stmt->errorInfo();
            throw new \Exception(var_export($error, true));
        }
        return true;
    }
    /**
     * 析构函数
     *
     */
    public function __destruct()
    {
        $this->close();
    }
    /**
     * 关闭连接
     *
     */
    public function close()
    {
        $this->link = null; // 开启会话自定义保存时，不关闭防止会话保存失败
    }
    /**
     * 获取 mysql 版本
     *
     *@param \PDO $link
     *
     *@return string
     */
    public function version($link = null)
    {
        is_null($link) && $link = $this->link;
        return $link->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }
    /**
     * 开启事务
     *
     * @return bool
     */
    public function begin()
    {
        return $this->link->beginTransaction();
    }
    /**
     * 提交事务
     *
     * @return bool
     */
    public function commit()
    {
        return $this->link->commit();
    }
    /**
     * 设置一个事务保存点
     *
     * @param string $pointName
     *
     * @return bool
     */
    public function savePoint($pointName)
    {
        return $this->link->exec("SAVEPOINT {$pointName}");
    }
    /**
     * 回滚事务
     *
     * @param string $rollBackTo 是否为还原到某个保存点
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->link->rollBack();
    }
    /**
     * 调用存储过程
     *
     * @param string $procedureName 要调用的存储过程名称
     * @param array $bindParams 绑定的参数
     * @param bool|true $isSelect 是否为返回数据集的语句
     *
     * @return array|int
     */
    public function callProcedure($procedureName = '', $bindParams = array(), $isSelect = true)
    {
        $stmt = $this->prepare("exec {$procedureName}");
        $this->execute($stmt, $bindParams);
        if ($isSelect) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            return  $stmt->rowCount();
        }
    }
    /**
     * 执行 sql 语句
     */
    public function exec()
    {
        $stmt = $this->prepare($this->sql, $this->link);
        $this->execute($stmt, $this->bindParams);
        $this->sql = null;
        $this->bindParams = array();
        return  $stmt->rowCount();
    }
    /**
     * 释放数据库链接
     */
    public function pool($link = 'r')
    {
        return true;
    }
}