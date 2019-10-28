<?php
namespace Kernel;

use Kernel\Utilities\Arr;

abstract class ServerPid
{
    protected static $pidFileName;
    /**
     * 初始化
     * @param $path
     * @param $name
     */
    public static function init($path)
    {
        self::$pidFileName = $path;
    }
    /**
     * 获取pidList
     * @param $file
     * @return array|mixed
     */
    public static function getPidList($file)
    {
        if (is_file($file)) {
            $fileData = file_get_contents($file);
            $pidList = json_decode($fileData, true);
        }
        return !empty($pidList)?$pidList:[];
    }
    public static function cleanPidList()
    {
        if (!file_exists(self::$pidFileName)) {
            return;
        }
        file_put_contents(self::$pidFileName, "");
    }
    public static function reSavePid($data)
    {
        self::cleanPidList();
        file_put_contents(self::$pidFileName, json_encode($data));
    }
    /**
     * 输入pidlist
     * @param $pidList
     * @param $file
     */
    public static function putPidList($pidList)
    {
        //文件不存在则先创建文件
        if (!file_exists(self::$pidFileName)) {
            return;
        }
        $fp = fopen(self::$pidFileName, "r+b");
        while ($fp) {
            if (flock($fp, LOCK_EX)) {
                $myPidList = self::getPidList(self::$pidFileName);
                $myPidList = self::mergeList($myPidList, $pidList);
                self::writePidFile($myPidList);
                flock($fp, LOCK_UN);
                break;
            } else {
                usleep(1000);
            }
        }
        if ($fp) {
            fclose($fp);
        }
    }
    /**
     * @param $type
     * @param $pid
     * @param int $status
     * @param string $taskType
     * @return array  = ['work'=>[['pid'=>1,'status'=>0]]];
     */

    public static function makePidList($type, $pid, $process_name)
    {
        $result = [];
        $result[$type][$process_name] = [
            'pid' => $pid,
            'datetime' => date('Y-m-d H:i:s'),
        ];
        return $result;
    }
    public static function getMasterPid($file)
    {
        $pidList = self::getPidList($file);
        if (empty($pidList)) {
            return 0;
        }
        if (!isset($pidList['master']) || empty($pidList['master'])) {
            return 0;
        }
        if (!key($pidList['master'])) {
            return 0;
        }
        $key = key($pidList['master']);
        if (!isset($pidList['master'][$key]['pid'])) {
            return 0;
        }
        return $pidList['master'][$key]['pid'];
    }
    public static function getManagerPid($file)
    {
        $pidList = self::getPidList($file);
        if (empty($pidList)) {
            return 0;
        }
        if (!isset($pidList['manager']) || empty($pidList['manager'])) {
            return 0;
        }
        if (!key($pidList['manager'])) {
            return 0;
        }
        $key = key($pidList['manager']);
        if (!isset($pidList['manager'][$key]['pid'])) {
            return 0;
        }
        return $pidList['manager'][$key]['pid'];
    }
    /**
     * @param $allPidList
     * @param $pidlist = ['work'=>[['pid'=>1,'status'=>0]]];
     * @return mixed
     */
    protected static function mergeList($allPidList, $pidlist)
    {
        if (!is_array($allPidList)) {
            $allPidList = [];
        }
        if (!is_array($pidlist)) {
            $pidlist = [];
        }
        return Arr::merge($allPidList, $pidlist);
    }
    /**
     * 写入pid文件
     * @param $pidList
     */
    protected static function writePidFile($pidList)
    {
        if (is_array($pidList)) {
            file_put_contents(self::$pidFileName, json_encode($pidList));
        }
    }
}
