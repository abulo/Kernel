<?php
/**
 * @category MySql.Pdo
 * @package MySql.Pdo
 * @link @MySql.Pdo
 * @author abulo.hoo
 */
namespace Kernel\Asyn\MongoDB;

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
use MongoDB\Client;
use Kernel\Asyn\IAsynPool;

class MongoDB implements IAsynPool
{
    const AsynName = 'mongodb';
    private $config = [];
    private $active;
    private $manager;
    private $table;
    private $wheres = [];
    private $limit = 999999;
    private $offset = 0;
    private $sorts = [];
    private $selects = [];
    private $database;
    private $collection = '';
    private $updates = [];
    private $result;
    private $tz;
    /**
     * 数据库连接串
     *
     * @param $conf
     */
    public function __construct($config, $active)
    {
        if (!class_exists('\MongoDB\Driver\Manager')) {
            throw new \Exception('require mongodb > 1.0');
        }
        $this->config = $config;
        $this->active = $active;
        $this->tz = $this->config->get('common.timezone', 'Asia/Shanghai');
        $this->connect();
    }
    /**
     * 创建私有链接
     * @return  obj
     */
    private function connect()
    {
        $config = $this->config->get('mongodb.'.$this->active);
        $uri = 'mongodb://'.implode($config['host'], ',').'/';
        try {
            $client = new Client($uri, $config['uriOptions'], $config['driverOptions']);
        } catch (\Exception $e) {
            throw new \Exception("无法链接 mongodb  url=$uri");
            $client = null;
        }
        $this->manager = $client;
        $this->database = $config['database'];
    }


    /**
     * 获取当前连接
     * @return mixed
     */
    public function getManager()
    {
        return $this->manager;
    }


    public function getAsynName()
    {
        return self::AsynName .':'. $this->active;
    }

    public function pushToPool($client)
    {
        return $this->manager;
    }

    public function getSync()
    {
        return $this->manager;
    }


    /**
     * @param $name
     */
    public function setName($name)
    {

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
     * @param   array  $param
     * @return mixed
     */
    public function insertOne($param)
    {
        try {
            $table = $this->collection;
            $db = $this->database;
            $result = $this->manager->$db->$table->insertOne($param);
            $this->clean();
            return (string)$result->getInsertedId();
        } catch (\Exception $e) {
            $this->showError($e);

            return false;
        }
    }

    /**
     * 插入多条数据
     * @param   array  $param
     * @return mixed
     */
    public function insertMany($param)
    {
        try {
            $table = $this->collection;
            $db = $this->database;
            $result = $this->manager->$db->$table->insertMany($param);
            $this->clean();
            return $result->getInsertedCount();
        } catch (\Exception $e) {
            $this->showError($e);

            return false;
        }
    }



    /**
     * 查询单条
     * @param null $id
     * @return mixed|null
     */
    public function findOne($id = null)
    {
        try {
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

            $table = $this->collection;
            $db = $this->database;



            $documents = $this->manager->$db->$table->find($filter, $options);
            $returns = null;
            foreach ($documents as $document) {
                foreach ($document as $key => $val) {
                    if ($document->$key instanceof \MongoDB\BSON\UTCDateTime) {
                        $document->$key = $document->$key->toDateTime()->setTimezone(new \DateTimeZone($this->tz))->format('Y-m-d H:i:s');
                    }
                    if ($document->$key instanceof \MongoDB\BSON\ObjectID) {
                        $document->$key = (string)$document->$key;
                    }
                }
                $bson = \MongoDB\BSON\fromPHP($document);
                $returns = json_decode(\MongoDB\BSON\toJSON($bson), true);
            }
            $this->clean();
            return $returns;
        } catch (\Exception $e) {
            $this->showError($e);

            return false;
        }
    }

    /**
     * 查询多条
     * @param null $id
     * @return mixed|null
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
            $table = $this->collection;
            $db = $this->database;

            $documents = $this->manager->$db->$table->find($filter, $options);
            $returns = array();
            foreach ($documents as $document) {
                foreach ($document as $key => $val) {
                    if ($document->$key instanceof \MongoDB\BSON\UTCDateTime) {
                        $document->$key = $document->$key->toDateTime()->setTimezone(new \DateTimeZone($this->tz))->format('Y-m-d H:i:s');
                    }
                    if ($document->$key instanceof \MongoDB\BSON\ObjectID) {
                        $document->$key = (string)$document->$key;
                    }
                }
                $bson = \MongoDB\BSON\fromPHP($document);
                $returns[] = json_decode(\MongoDB\BSON\toJSON($bson), true);
            }
            $this->clean();
            return $returns;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }


    /**
     * 单条记录更新
     * @return
     */
    public function updateOne()
    {
        try {
            $filter = (array)$this->wheres;
            $newObj = (array)$this->updates;
            $updateOptions = ['multi' => false, 'upsert' => false];
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->updateOne($filter, $newObj, $updateOptions);
            $this->clean();
            return $documents->getModifiedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 单条记录替换
     * @return
     */
    public function replaceOne()
    {
        try {
            $filter = (array)$this->wheres;
            $newObj = (array)$this->updates;
            $updateOptions = ['multi' => false, 'upsert' => false];
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->replaceOne($filter, $newObj, $updateOptions);
            $this->clean();
            return $documents->getModifiedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }


    /**
     * 单条记录更新
     * @return
     */
    public function updateMany()
    {
        try {
            $filter = (array)$this->wheres;
            $newObj = (array)$this->updates;
            $updateOptions = ['multi' => false, 'upsert' => false];
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->updateMany($filter, $newObj, $updateOptions);
            $this->clean();
            return $documents->getModifiedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }


    public function findOneAndUpdate()
    {
        try {
            $filter = (array)$this->wheres;
            $newObj = (array)$this->updates;
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->findOneAndUpdate($filter, $newObj);
            $this->clean();
            return $documents->getModifiedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }



    /**
     * 单个记录删除
     * @return
     */
    public function deleteOne()
    {
        try {
            $filter = (array)$this->wheres;
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->deleteOne($filter);
            $this->clean();
            return $documents->getDeletedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }


    /**
     * 多个记录三处
     * @return
     */
    public function deleteMany()
    {
        try {
            $filter = (array)$this->wheres;
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->deleteMany($filter);
            $this->clean();
            return $documents->getDeletedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }


    /**
     * 多个记录三处
     * @return
     */
    public function findOneAndDelete()
    {
        try {
            $filter = (array)$this->wheres;
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->findOneAndDelete($filter);
            $this->clean();
            return $documents->getDeletedCount();
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 获取数据中有多少文档
     * @return  array
     */
    public function listCollections()
    {
        try {
            $db = $this->database;
            $documents = $this->manager->$db->command(['listCollections' => 1]);
            $result = [];
            if ($documents) {
                foreach ($documents as $collection) {
                    $result[]=$collection['name'];
                }
            }
            $this->clean();
            return $result;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }


    /**
     * 创建索引
     * @param  array $key
     * @param  array  $options
     * @return string
     */
    public function createIndex($key, $options = [])
    {
        try {
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->createIndex($key, $options);
            $this->clean();
            return $documents;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 创建索引
     * @param  array $key
     * @param  array  $options
     * @return string/array
     */
    public function createIndexes(array $indexes, array $options = [])
    {
        try {
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->createIndex($indexes, $options);
            $this->clean();
            return $documents;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 删除索引
     * @param  string $index
     * @return array / object
     */
    public function dropIndex($index)
    {
        try {
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->dropIndex($index);
            $this->clean();
            return $documents;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 删除文档下所有索引
     * @return  array|objec
     */
    public function dropIndexes()
    {
        try {
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->dropIndexes();
            $this->clean();
            return $documents;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 索引列表
     * @return
     */
    public function listIndexes()
    {
        try {
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->listIndexes();
            $returns = array();
            foreach ($documents as $document) {
                $bson = \MongoDB\BSON\fromPHP($document);
                $returns[] = json_decode(\MongoDB\BSON\toJSON($bson), true);
            }
            $this->clean();
            return $returns;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 去重
     * @param  string $fieldName
     * @return
     */
    public function distinct($fieldName)
    {
        try {
            $filter = (array)$this->wheres;
            $this->orderBy($this->sorts);
            $options = [

            ];
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->distinct($fieldName, $filter, $options);
            $this->clean();
            return $documents;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 管道聚合
     * @param  array  $pipeline
     * @param  array  $options
     * @return
     */
    public function aggregate(array $pipeline, array $options = [])
    {
        try {
            $filter = (array)$this->wheres;
            $this->orderBy($this->sorts);
            // $options = [

            // ];
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->aggregate($pipeline, $options);
//            $this->clean();

            $returns = array();
            foreach ($documents as $document) {
                foreach ($document as $key => $val) {
                    if ($document->$key instanceof \MongoDB\BSON\UTCDateTime) {
                        $document->$key = $document->$key->toDateTime()->setTimezone(new \DateTimeZone($this->tz))->format('Y-m-d H:i:s');
                    }
                    if ($document->$key instanceof \MongoDB\BSON\ObjectID) {
                        $document->$key = (string)$document->$key;
                    }
                }
                $bson = \MongoDB\BSON\fromPHP($document);
                $returns[] = json_decode(\MongoDB\BSON\toJSON($bson), true);
            }
            $this->clean();
            return $returns;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * 获取记录总数
     * @return integer /bool
     */
    public function count()
    {
        try {
            $filter = (array)$this->wheres;
            $this->orderBy($this->sorts);
            $options = [
                // 'projection' => (array)$this->selects,
                // "sort" => (array)$this->sorts,
                // "skip" => (int)$this->offset,
                // "limit" => (int)$this->limit,
            ];
            $table = $this->collection;
            $db = $this->database;
            $documents = $this->manager->$db->$table->count($filter, $options);
            $this->clean();
            return $documents;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
    }

    /**
     * lbsCommand
     * @param  Array $param
     * @return
     */
    public function lbsCommand($param)
    {
        try {
            $db = $this->database;
            $documents = $this->manager->$db->command($param);
            $returns = null;
            foreach ($documents as $document) {
                foreach ($document as $key => $val) {
                    if ($document->$key instanceof \MongoDB\BSON\UTCDateTime) {
                        $document->$key = $document->$key->toDateTime()->setTimezone(new \DateTimeZone($this->tz))->format('Y-m-d H:i:s');
                    }
                    if ($document->$key instanceof \MongoDB\BSON\ObjectID) {
                        $document->$key = (string)$document->$key;
                    }
                }
                $bson = \MongoDB\BSON\fromPHP($document);
                $returns = json_decode(\MongoDB\BSON\toJSON($bson), true);
            }
            $this->clean();
            return $returns;
        } catch (\Exception $e) {
            $this->showError($e);
            return false;
        }
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






    public function clean()
    {
        $this->wheres = [];
        $this->limit = 999999;
        $this->offset = 0;
        $this->sorts = [];
        $this->selects = [];
        $this->database;
        $this->collection = '';
        $this->updates = [];
        $this->result = [];
        $this->tz;
        $this->active = null;
    }
}
