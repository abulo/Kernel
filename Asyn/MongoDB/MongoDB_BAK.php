<?php
/**
 * @category MySql.Pdo
 * @package MySql.Pdo
 * @link @MySql.Pdo
 * @author abulo.hoo
 */
namespace Kernel\Store\Datebase\MongoDB;

// use Kernel\Store\Datebase\Base;

use Kernel\Config\Log;
use MongoDB\Driver\Manager;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\BulkWrite;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\Query;
use MongoDB\BSON\fromPHP;
use MongoDB\BSON\toJSON;
use MongoDB\Driver\Command;
use MongoDB\BSON\UTCDatetime;
use MongoDB\BSON\Timestamp;

class MongoDB// extends Base
{


    private $config;
    private $database;
    private $collection = '';
    private $selects;
    private $wheres;
    private $updates;
    private $limit = 999999;
    private $offset = 0;
    private $sorts;
    private $manager;
    private $result;
    /**
     * 数据库连接串
     *
     * @param $conf
     */
    public function __construct($conf)
    {
        if (!class_exists('\MongoDB\Driver\Manager')) {
            throw new Exception('require mongodb > 1.0');
        }
        $this->config = $conf;

        $this->connect();
    }

    /**
     * 创建私有链接
     * @return  obj
     */
    private function connect()
    {
        $uri = 'mongodb://'.implode($this->config['host'], ',').'/';
        try {
            $link = new Manager($uri, $this->config['uriOptions'], $this->config['driverOptions']);
        } catch (\Exception $e) {
            throw new \Exception("mongodb ");

            $link = null;
        }
        $this->manager = $link;
        $this->database = $this->config['database'];
    }




    /**
     * 获取当前连接
     * @return mixed
     */
    public function getManager()
    {
        return $this->manager;
    }



    /**
     * @param mixed $collection
     */
    public function getCollection(): string
    {
        return $this->collection;
    }
    /**
     * 获取查询所需胡字段
     * @return array
     */
    public function getSelects(): array
    {
        return $this->selects;
    }

    /**
     * 获取条件
     * @return array
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }
    /**
     * 获取更新内容
     * @return array
     */
    public function getUpdates(): array
    {
        return $this->updates;
    }
    /**
     * 获取条数
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * 获取偏移量
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
    /**
     * 获取排序
     * @return array
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }
    /**
     * @param mixed $collection
     */
    public function setCollection(string $collection)
    {
        $this->collection = $collection;
    }
    /**
     * @param array $selects
     */
    public function setSelects(array $selects)
    {
        $this->selects = $selects;
    }
    /**
     * @param array $wheres
     */
    public function setWheres(array $wheres)
    {
        $this->wheres = $wheres;
    }
    /**
     * @param array $updates
     */
    public function setUpdates(array $updates)
    {
        $this->updates = $updates;
    }
    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }
    /**
     * @param int $offset
     */
    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }
    /**
     * @param array $sorts
     */
    public function setSorts(array $sorts)
    {
        $this->sorts = $sorts;
    }
    /**
     * @param $database
     * @return $this
     */
    public function db($database)
    {
        $this->database = $database;
        return $this;
    }
    /**
     * @param $table
     * @return $this
     */
    public function collection($collection)
    {
        $this->collection = $collection;
        return $this;
    }
    /**
     * @param $collection
     * @return Mongo_db
     */
    public function table($collection)
    {
        return $this->collection($collection);
    }

    /**
     * 插入一条数据
     * @param   array  $document
     * @param  string  $wstring
     * @param  integer $wtimeout
     * @return mixed
     */
    public function insertOne($document, $wstring = WriteConcern::MAJORITY, $wtimeout = 1000)
    {
        try {
            $wc = new WriteConcern($wstring, $wtimeout);
            $bulk = new BulkWrite();
            $bulk->insert($document);
            $dbc = $this->database . '.' . $this->collection;
            $result = $this->manager->executeBulkWrite($dbc, $bulk, $wc);
            $this->result = $result;
            //增加几条
            return $result->getInsertedCount();
        } catch (\Exception $e) {
            $this->showError($e);

            return false;
        }
    }
    /**
     * 插入多条数据
     * @param   array  $document
     * @param  string  $wstring
     * @param  integer $wtimeout
     * @return mixed
     */
    public function insertMany($documents = array(), $wstring = WriteConcern::MAJORITY, $wtimeout = 1000)
    {
        try {
            $wc = new WriteConcern($wstring, $wtimeout);
            $bulk = new BulkWrite();
            foreach ($documents as $k => $document) {
                $bulk->insert($document);
            }
            $dbc = $this->database . '.' . $this->collection;
            $result = $this->manager->executeBulkWrite($dbc, $bulk, $wc);
            $this->result = $result;
            //增加几条
            return $result->getInsertedCount();
        } catch (\Exception $e) {
            $this->showError($e);

            return false;
        }
    }
    /**
     * 删
     * @param array $deleteOptions
     * @param string $wstring
     * @param int $wtimeout
     * @return mixed
     */
    public function delete($deleteOptions = ["limit" => 1], $wstring = WriteConcern::MAJORITY, $wtimeout = 1000)
    {
        try {
            $wc = new WriteConcern($wstring, $wtimeout);
            $bulk = new BulkWrite();
            $filter = (array)$this->wheres;
            if (count($filter) < 1 && $deleteOptions['limit'] == 1) {
                throw new \Exception('filter is error!');
            }
            $bulk->delete($filter, $deleteOptions);
            $dbc = $this->database . '.' . $this->collection;
            $result = $this->manager->executeBulkWrite($dbc, $bulk, $wc);
            $this->result = $result;
            //删除几条
            return $result->getDeletedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 删除所有
     * @param array $deleteOptions
     * @param string $wstring
     * @param int $wtimeout
     * @return mixed
     */
    public function deleteAll($deleteOptions = ["limit" => 0], $wstring = WriteConcern::MAJORITY, $wtimeout = 1000)
    {
        return $this->delete($deleteOptions, $wstring, $wtimeout);
    }

    /**
     * 更新
     * @param array $updateOptions
     * @param string $wstring
     * @param int $wtimeout
     */
    public function update($updateOptions = ['multi' => false, 'upsert' => false], $wstring = WriteConcern::MAJORITY, $wtimeout = 1000)
    {
        try {
            $wc = new WriteConcern($wstring, $wtimeout);
            $bulk = new BulkWrite();
            $filter = (array)$this->wheres;
            if (count($filter) < 1 && $updateOptions['multi'] == false) {
                throw new \Exception('filter is error!');
            }
            $newObj = (array)$this->updates;
            $bulk->update(
                $filter,
                $newObj,
                $updateOptions
            );
            $dbc = $this->database . '.' . $this->collection;
            $result = $this->manager->executeBulkWrite($dbc, $bulk, $wc);
            $this->result = $result;
            return $result->getModifiedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }


    /**
    * 更新所有
    * @param array $updateOptions
    * @param string $wstring
    * @param int $wtimeout
    */
    public function updateAll($updateOptions = ['multi' => true, 'upsert' => false], $wstring = WriteConcern::MAJORITY, $wtimeout = 1000)
    {
        return $this->update($updateOptions, $wstring, $wtimeout);
    }


    /**
     * 查询单条
     * @param null $id
     * @return mixed|null
     */
    public function findOne($id = null)
    {
        if ($id != null) {
            $this->where('_id', new ObjectID($id));
        }
        $filter = (array)$this->wheres;
        $this->offset = 0;
        $this->limit = 1;
        $this->orderBy($this->sorts);
        $options = [
            'projection' => (array)$this->selects,
            "sort" => (array)$this->sorts,
            "skip" => (int)$this->offset,
            "limit" => (int)$this->limit,
        ];
        $query = new Query($filter, $options);
        $dbc = $this->database . '.' . $this->collection;
        $documents = $this->manager->executeQuery($dbc, $query);
        $this->result = $documents;
        $returns = null;
        foreach ($documents as $document) {
            foreach ($document as $key => $val) {
                if ($document->$key instanceof \MongoDB\BSON\UTCDateTime) {
                    $document->$key = (string)$document->$key;
                }
                if ($document->$key instanceof \MongoDB\BSON\ObjectID) {
                    $document->$key = (string)$document->$key;
                }
            }
            $bson = \MongoDB\BSON\fromPHP($document);
            $returns = json_decode(\MongoDB\BSON\toJSON($bson), true);
        }
        return $returns;
    }

    /**
     * 查
     * @return mixed
     */
    public function find()
    {
        try {
            $filter = (array)$this->wheres;
            $this->orderBy($this->sorts);
            $options = [
                'projection' => (array)$this->selects,
                "sort" => (array)$this->sorts,
                "skip" => (int)$this->offset,
                "limit" => (int)$this->limit,
            ];
            $query = new Query($filter, $options);
            $dbc = $this->database . '.' . $this->collection;
            $documents = $this->manager->executeQuery($dbc, $query);
            $this->result = $documents;
            $returns = array();
            foreach ($documents as $document) {
                foreach ($document as $key => $val) {
                    if ($document->$key instanceof \MongoDB\BSON\UTCDateTime) {
                        $document->$key = (string)$document->$key;
                    }
                    if ($document->$key instanceof \MongoDB\BSON\ObjectID) {
                        $document->$key = (string)$document->$key;
                    }
                }
                $bson = \MongoDB\BSON\fromPHP($document);
                $returns[] = json_decode(\MongoDB\BSON\toJSON($bson), true);
            }
            return $returns;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }



    /**
     * command
     * @param $db
     * @param $commands
     * @return mixed
     */
    public function command($db, $commands)
    {
        try {
            $cursor = $this->manager->executeCommand($db, $commands);
            $this->result = $cursor;
            return $cursor;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 删除数据库
     * @return  mixed
     */
    public function dropDatabase()
    {
        $cmd = array(
            'dropDatabase' => 1,
        );
        $db = $this->database;
        $commands = new Command($cmd);
        $cursor = $this->command($db, $commands);
        $this->result = $cursor;
        $response = current($cursor->toArray());
        return $response;
    }

    /**
     * 删除集合
     * @return  mixed
     */
    public function dropCollection()
    {
        $cmd = array(
            'drop' => $this->collection,
        );
        $db = $this->database;
        $commands = new Command($cmd);
        $cursor = $this->command($db, $commands);
        $this->result = $cursor;
        $response = current($cursor->toArray());
        return $response;
    }

    /**
     * 添加索引
     * @param  $key
     * @param  $name
     */
    public function addIndex($key, $name = 'index')
    {
        $cmd = array(
            'createIndexes' => $this->collection,
            'indexes' => array(
                array(
                    'name' => $name,
                    'key' => $key,
                )
            )
        );
        $db = $this->database;
        $commands = new Command($cmd);
        $cursor = $this->command($db, $commands);
        $this->result = $cursor;
        $response = current($cursor->toArray());
        return $response;
    }

    /**
     * 删除索引
     * @param   $index
     * @return
     */
    public function removeIndex($index)
    {
        $cmd = array(
           'dropIndexes' => $this->collection,
           'index' => $index
        );
        $db = $this->database;
        $commands = new Command($cmd);
        $cursor = $this->command($db, $commands);
        $this->result = $cursor;
        $response = current($cursor->toArray());
        return $response;
    }
    /**
     * 获取索引
     * @return
     */
    public function listIndexes()
    {
        $cmd = array(
            'listIndexes' => $this->collection,
        );
        $db = $this->database;
        $commands = new Command($cmd);
        $cursor = $this->command($db, $commands);
        $this->result = $cursor;
        return $cursor;
    }
    /**
     * 聚合查询
     * @param   $commands
     * @return
     */
    public function aggregate($commands)
    {
        $db = $this->database;
        $commands = new Command(
            [
                'aggregate' => $this->collection,
                'pipeline' => [$commands]
            ]
        );
        $cursor = $this->command($db, $commands);
        $this->result = $cursor;
        $response = current($cursor->toArray())->result;
        return $response;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function distinct($key)
    {
        $db = $this->database;
        $commands = new Command(
            [
                'distinct' => $this->collection,
                'key' => $key,
                'query' => (array)$this->wheres
            ]
        );
        $cursor = $this->command($db, $commands);
        $this->result = $cursor;
        $response = current($cursor->toArray())->values;
        return $response;
    }



    /**
     * count
     * @return mixed
     */
    public function count()
    {
        $db = $this->database;
        $commands = new Command(
            [
                "count" => $this->collection,
                "query" => (array)$this->wheres
            ]
        );
        $cursor = $this->command($db, $commands);
        $this->result = $cursor;
        $response = $cursor->toArray()[0];
        return $response->n;
    }


    /**
     * @param $fields
     * @param null $value
     * @return $this
     */
    public function set($fields, $value = null)
    {
        if (is_string($fields)) {
            $this->updates['$set'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$set'][$field] = $value;
            }
        }
        return $this;
    }


    /**
     * 要获取的字段
     * @param $wheres
     * @param null $value
     * @return $this
     */
    public function field($includes = array(), $excludes = array())
    {
        if (!is_array($includes)) {
            $includes = array();
        }
        if (!is_array($excludes)) {
            $excludes = array();
        }
        if (!empty($includes)) {
            foreach ($includes as $col) {
                $this->selects[$col] = 1;
            }
        }
        if (!empty($excludes)) {
            foreach ($excludes as $col) {
                $this->selects[$col] = 0;
            }
        }
        return $this;
    }

    /**
    * 条件
    * @param $wheres
    * @param null $value
    * @return $this
    */
    public function where($wheres, $value = null)
    {
        if (is_array($wheres)) {
            foreach ($wheres as $wh => $val) {
                $this->wheres[$wh] = $val;
            }
        } else {
            $this->wheres[$wheres] = $value;
        }
         return $this;
    }
    public function whereIn($field = "", $in = array())
    {
        $this->wheres[$field]['$in'] = $in;
        return $this;
    }
    public function whereInAll($field = "", $in = array())
    {
        $this->wheres[$field]['$all'] = $in;
        return $this;
    }
    public function whereOr($wheres = array())
    {
        foreach ($wheres as $wh => $val) {
            $this->wheres['$or'][] = array($wh => $val);
        }
        return $this;
    }
    public function whereNotIn($field = "", $in = array())
    {
        $this->wheres[$field]['$nin'] = $in;
        return $this;
    }

    public function whereGt($field = "", $x)
    {
        $this->wheres[$field]['$gt'] = $x;
        return $this;
    }
    public function whereGte($field = "", $x)
    {
        $this->wheres[$field]['$gte'] = $x;
        return $this;
    }
    public function whereLt($field = "", $x)
    {
        $this->wheres[$field]['$lt'] = $x;
        return $this;
    }
    public function whereLte($field = "", $x)
    {
        $this->wheres[$field]['$lte'] = $x;
        return $this;
    }
    public function whereBetween($field = "", $x, $y)
    {
        $this->wheres[$field]['$gte'] = $x;
        $this->wheres[$field]['$lte'] = $y;
        return $this;
    }
    public function whereNetweenNe($field = "", $x, $y)
    {
        $this->wheres[$field]['$gt'] = $x;
        $this->wheres[$field]['$lt'] = $y;
        return $this;
    }
    public function whereNe($field = '', $x)
    {
        $this->wheres[$field]['$ne'] = $x;
        return $this;
    }
    public function push($fields, $value = array())
    {
        if (is_string($fields)) {
            $this->updates['$push'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$push'][$field] = $value;
            }
        }
        return $this;
    }
    public function addtoset($field, $values)
    {
        if (is_string($values)) {
            $this->updates['$addToSet'][$field] = $values;
        } elseif (is_array($values)) {
            $this->updates['$addToSet'][$field] = array('$each' => $values);
        }
        return $this;
    }
    public function pop($field)
    {
        if (is_string($field)) {
            $this->updates['$pop'][$field] = -1;
        } elseif (is_array($field)) {
            foreach ($field as $pop_field) {
                $this->updates['$pop'][$pop_field] = -1;
            }
        }
        return $this;
    }
    public function pull($field = "", $value = array())
    {
        $this->updates['$pull'] = array($field => $value);
        return $this;
    }
    public function renameField($old, $new)
    {
        $this->updates['$rename'] = array($old => $new);
        return $this;
    }
    public function unsetField($fields)
    {
        if (is_string($fields)) {
            $this->updates['$unset'][$fields] = 1;
        } elseif (is_array($fields)) {
            foreach ($fields as $field) {
                $this->updates['$unset'][$field] = 1;
            }
        }
        return $this;
    }
    public function inc($fields = array(), $value = 0)
    {
        if (is_string($fields)) {
            $this->updates['$inc'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$inc'][$field] = $value;
            }
        }
        return $this;
    }
    public function mul($fields = array(), $value = 0)
    {
        if (is_string($fields)) {
            $this->updates['$mul'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$mul'][$field] = $value;
            }
        }
        return $this;
    }
    public function max($fields = array(), $value = 0)
    {
        if (is_string($fields)) {
            $this->updates['$max'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$max'][$field] = $value;
            }
        }
        return $this;
    }
    public function min($fields = array(), $value = 0)
    {
        if (is_string($fields)) {
            $this->updates['$min'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$min'][$field] = $value;
            }
        }
        return $this;
    }
    /**
     * 排序
     * @param array $fields
     * @return $this
     */
    public function orderBy($fields = array())
    {
        if (empty($fields)) {
            return $this;
        }
        foreach ($fields as $col => $val) {
            if ($val == -1 || $val === false || strtolower($val) == 'desc') {
                $this->sorts[$col] = -1;
            } else {
                $this->sorts[$col] = 1;
            }
        }
        return $this;
    }
    /**
     * 条数
     * @param int $x
     * @return $this
     */
    public function limit($x = 99999)
    {
        if ($x !== null && is_numeric($x) && $x >= 1) {
            $this->limit = (int)$x;
        }
        return $this;
    }
    /**
     * 偏移量
     * @param int $x
     * @return $this
     */
    public function offset($x = 0)
    {
        if ($x !== null && is_numeric($x) && $x >= 1) {
            $this->offset = (int)$x;
        }
        return $this;
    }
    /**
     * 生成mongo时间
     * @param bool $stamp
     * @return \MongoDB\BSON\UTCDatetime
     */
    public function date($stamp = false)
    {
        if ($stamp == false) {
            return new UTCDatetime(time() * 1000);
        } else {
            return new UTCDatetime($stamp);
        }
    }
    /**
     * 生成mongo时间戳
     * @param bool $stamp
     * @return \MongoDB\BSON\Timestamp
     */
    public function timestamp($stamp = false)
    {
        if ($stamp == false) {
            return new Timestamp(0, time());
        } else {
            return new Timestamp(0, $stamp);
        }
    }



    /**
     * 抛出异常
     * @param $e
     */
    public function showError($e)
    {
        throw new \Exception($e->getMessage());
    }
}
