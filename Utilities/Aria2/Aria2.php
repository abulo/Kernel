<?php
namespace Kernel\Utilities\Aria2;

class Aria2
{

    const POS_SET = 'POS_SET';
    const POS_CUR = 'POS_CUR';
    const POS_END = 'POS_END';

    private $aria2;
    public $savePath;

    public function __construct(string $url, string $token = null, string $savePath = '')
    {
        $this->aria2    = new SimpleAria2JsonRpcClient($url, $token);
        $this->savePath = $savePath;
    }


    /**
     * 投递一个下载项至 aria2
     *
     * 支持 HTTP/HTTPS/FTP/SFTP/Magnet 链接
     * 一个下载项（资源）可指向多条链接，当你投递的多条链接指向的不是同一个资源，有可能会导致下载失败
     * 当投递 Magnet 链接时，一次只能投递一个链接
     *
     * @todo savePath 合并，相对路径转绝对路径
     *
     * @link https://aria2.github.io/manual/en/html/aria2c.html#aria2.addUri
     *
     * @param  string[]|string  $url      投递链接
     * @param  string           $savePath 保存位置，当以“/”结尾时，将文件保存到指定目录，否则保存到指定的文件名
     * @param  mixed[]          $options  见 {@see https://aria2.github.io/manual/en/html/aria2c.html#options aria2 Options} 文档
     * @param  int|null         $position 插入队列位置，当大于队列长度或未指定时插入到队列尾部
     * @return mixed[]|false
     */
    public function addUri($url, string $savePath = '', array $options = [], int $position = null)
    {
        if ($this->savePath) {
            $options['dir'] = $this->savePath;
        }
        if ($savePath) {
            $savePath = str_replace('\\', '/', $savePath);
            if (substr($savePath, -1) === '/') {
                $options['dir'] = substr($savePath, 0, -1);
            } else {
                $options['out'] = $savePath;
            }
        }
        $params = [
            is_array($url) ? $url : [$url],
            $options ?: '{}',
        ];
        if ($position !== null) {
            $params[] = $position;
        }
        $response = $this->aria2->addUri(...$params);
        return $response;
    }
    /**
     * 投递一个种子下载项至 aria2
     *
     * 种子文件使用 base64 进行编码
     *
     * @todo savePath 合并，相对路径转绝对路径
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.addTorrent
     *
     * @param  string            $torrent     经 base64 编码后的种子文件
     * @param  string[]|string   $urls        used for Web-seeding
     * @param  string            $savePath
     * @param  mixed[]           $options
     * @param  int|null          $position
     * @return miexed[]|false
     */
    public function addTorrent(string $torrent, $urls = [], string $savePath = '', array $options = [], int $position = null)
    {
        if ($this->savePath) {
            $options['dir'] = $this->savePath;
        }
        if ($savePath) {
            $savePath = str_replace('\\', '/', $savePath);
            if (substr($savePath, -1) === '/') {
                $options['dir'] = substr($savePath, 0, -1);
            } else {
                $options['out'] = $savePath;
            }
        }
        $params = [
            $torrent,
        ];
        if (!is_array($urls)) {
            $urls = [$urls];
        }
        if ($urls) {
            $params[] = $urls;
        }
        if ($options) {
            $params[] = $options;
        }
        if ($position !== null) {
            $params[] = $position;
        }
        $response = $this->aria2->addTorrent(...$params);
        return $response;
    }
    /**
     * 投递一个 metalink 文件下载项至 aria2
     *
     * metalink 文件使用 base64 进行编码
     *
     * @todo savePath合并，相对路径转绝对路径
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.addMetalink
     *
     * @param  string            $metalink    经 base64 编码后的 metalink 文件
     * @param  string            $savePath
     * @param  mixed[]           $options
     * @param  int|null          $position
     * @return miexed[]|false
     */
    public function addMetalink(string $metalink, string $savePath = '', array $options = [], int $position = null)
    {
        if ($this->savePath) {
            $options['dir'] = $this->savePath;
        }
        if ($savePath) {
            $savePath = str_replace('\\', '/', $savePath);
            if (substr($savePath, -1) === '/') {
                $options['dir'] = substr($savePath, 0, -1);
            } else {
                $options['out'] = $savePath;
            }
        }
        $params = [
            $metalink,
            $options ?: '{}',
        ];
        if ($position !== null) {
            $params[] = $position;
        }
        $response = $this->aria2->addMetalink(...$params);
        return $response;
    }
    /**
     * 移除指定下载项
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.remove
     *
     * @param  string         $gid 下载项 GID
     * @return string|false
     */
    public function remove(string $gid)
    {
        $response = $this->aria2->remove($gid);
        return $response;
    }
    /**
     * 强制移除指定下载项
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.forceRemove
     *
     * @param  string         $gid 下载项 GID
     * @return string|false
     */
    public function forceRemove(string $gid)
    {
        $response = $this->aria2->forceRemove($gid);
        return $response;
    }
    /**
     * 暂停指定下载项
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.pause
     *
     * @param  string         $gid 下载项 GID
     * @return string|false
     */
    public function pause(string $gid)
    {
        $response = $this->aria2->pause($gid);
        return $response;
    }
    /**
     * 暂停所有下载
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.pauseAll
     *
     * @return bool
     */
    public function pauseAll()
    {
        $response = $this->aria2->pauseAll();
        return $response;
    }
    /**
     * 强制暂停指定下载项
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.forcePause
     *
     * @param  string         $gid 下载项 GID
     * @return string|false
     */
    public function forcePause(string $gid)
    {
        $response = $this->aria2->forcePause($gid);
        return $response;
    }
    /**
     * 强制暂停所有下载
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.forcePauseAll
     *
     * @return bool
     */
    public function forcePauseAll()
    {
        $response = $this->aria2->forcePauseAll();
        return $response;
    }
    /**
     * 恢复指定下载项
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.unpause
     *
     * @param  string         $gid 下载项 GID
     * @return string|false
     */
    public function unpause(string $gid)
    {
        $response = $this->aria2->unpause($gid);
        return $response;
    }
    /**
     * 恢复所有下载
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.unpauseAll
     *
     * @return bool
     */
    public function unpauseAll()
    {
        $response = $this->aria2->unpauseAll();
        return $response;
    }
    /**
     * 查询指定下载项状态
     *
     * 当指定 keys 时，仅查询特定的状态，否则查询所有状态
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.tellStatus
     *
     * @param  string    $gid  下载项 gid
     * @param  string[]  $keys 查询项
     * @return mixed[]
     */
    public function tellStatus(string $gid, array $keys = [])
    {
        $params = [
            $gid,
        ];
        if ($keys) {
            $params[] = $keys;
        }
        $response = $this->aria2->tellStatus(...$params);
        return $response;
    }
    /**
     * 获取指定下载项中的链接
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getUris
     *
     * @param  string    $gid 下载项 GID
     * @return mixed[]
     */
    public function getUris(string $gid)
    {
        $response = $this->aria2->getUris($gid);
        return $response;
    }
    /**
     * 获取指定下载项中的文件
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getFiles
     *
     * @param  string    $gid 下载项 GID
     * @return mixed[]
     */
    public function getFiles(string $gid)
    {
        $response = $this->aria2->getFiles($gid);
        return $response;
    }
    /**
     * 获取指定种子下载项中的种子信息
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getPeers
     *
     * @param  string    $gid 下载项 GID
     * @return mixed[]
     */
    public function getPeers(string $gid)
    {
        $response = $this->aria2->getPeers($gid);
        return $response;
    }
    /**
     * 获取指定下载项的服务器信息
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getServers
     *
     * @param  string    $gid 下载项 GID
     * @return mixed[]
     */
    public function getServers(string $gid)
    {
        $response = $this->aria2->getServers($gid);
        return $response;
    }
    /**
     * 获取正在下载的下载项信息
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.tellActive
     *
     * @param  string[]  $keys 查询项
     * @return mixed[]
     */
    public function tellActive(array $keys = [])
    {
        $params = [];
        if ($keys) {
            $params[] = $keys;
        }
        $response = $this->aria2->tellActive(...$params);
        return $response;
    }
    /**
     * 获取等待中的下载项信息（包括暂停）
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.tellWaiting
     *
     * @param  int       $offset 从队列的指定位置取起，从0开始，支持负数，指定负数时表示从倒数第N个取起，此时返回的列表是倒序的！
     * @param  int       $num    获取数量
     * @param  string[]  $keys   查询项
     * @return mixed[]
     */
    public function tellWaiting(int $offset, int $num, array $keys = [])
    {
        $params = [
            $offset,
            $num,
        ];
        if ($keys) {
            $params[] = $keys;
        }
        $response = $this->aria2->tellActive(...$params);
        return $response;
    }
    /**
     * 获取暂停中的下载项信息（不包括未开始过下载等待中的）
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.tellStopped
     *
     * @param  int       $offset
     * @param  int       $num
     * @param  string[]  $keys
     * @return mixed[]
     */
    public function tellStopped(int $offset, int $num, array $keys = [])
    {
        $params = [
            $offset,
            $num,
        ];
        if ($keys) {
            $params[] = $keys;
        }
        $response = $this->aria2->tellStopped(...$params);
        return $response;
    }
    /**
     * 移动指定下载项的位置
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.changePosition
     *
     * @param  string      $gid 下载项 GID
     * @param  int         $pos 要移动的位置量，与 $how 参数关联
     * @param  string      $how POS_* 常量
     * @return int|false
     */
    public function changePosition(string $gid, int $pos, string $how)
    {
        if ($how !== self::POS_SET && $how !== self::POS_CUR && $how !== self::POS_END) {
            trigger_error('unrecognized position string.');
            return false;
        }
        $response = $this->aria2->tellStopped($gid, $pos, $how);
        return $response;
    }
    /**
     * 移除或添加指定下载项中的文件的下载连接
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.changeUri
     *
     * @param  string   $gid       下载项 GID
     * @param  int      $fileIndex 文件在下载项中的位置，从1开始
     * @param  string[] $delUris   要删除的url数组，注意：当文件存在N个相同的下载链接时，如果你想把他们都移除掉，你就要指定N次该链接
     * @param  string[] $addUris   要添加的url数组
     * @param  int|null $position  添加url的位置，从0开始，当未指定时将插入到最后。当同时指定了删除数组以及添加数组时，先执行删除操作，再执行添加操作，所以position应当是删除过后的位置，而不是删除前的。
     * @return int[]
     */
    public function changeUri(string $gid, int $fileIndex, array $delUris = [], array $addUris = [], int $position = null)
    {
        $params = [
            $gid,
            $fileIndex,
            $delUris,
            $addUris,
        ];
        if ($position !== null) {
            $params[] = $position;
        }
        $response             = $this->aria2->changeUri(...$params);
        $counts               = $response;
        @list($deled, $added) = $counts;
        return [$deled ?? 0, $added ?? 0];
    }
    /**
     * 获取指定下载项的配置信息（不包括没默认配置以及没手动配置的）
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getOption
     *
     * @param  string    $gid 下载项 GID
     * @return mixed[]
     */
    public function getOption(string $gid)
    {
        $response = $this->aria2->getOption($gid);
        return $response;
    }
    /**
     * 更改指定下载项的配置
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.changeOption
     *
     * @param  string  $gid     下载项 GID
     * @param  mixed[] $options 要更改的配置项，有部分配置无法修改
     * @return bool
     */
    public function changeOption(string $gid, array $options)
    {
        $response = $this->aria2->changeOption($gid, $options);
        return $response;
    }
    /**
     * 获取所有全局配置
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getGlobalOption
     *
     * @return mixed[]
     */
    public function getGlobalOption()
    {
        $response = $this->aria2->getGlobalOption();
        return $response;
    }
    /**
     * 更改全局的配置项
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.changeGlobalOption
     *
     * @param  mixed[] $options 要更改的配置项，有部分配置无法修改
     * @return bool
     */
    public function changeGlobalOption(array $options)
    {
        $response = $this->aria2->changeGlobalOption($options);
        return $response;
    }
    /**
     * 获取全局的状态
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getGlobalStat
     *
     * @return mixed[]
     */
    public function getGlobalStat()
    {
        $response = $this->aria2->getGlobalStat();
        return $response;
    }
    /**
     * 清除已完成/出错/已移除的下载项
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.purgeDownloadResult
     *
     * @return bool
     */
    public function purgeDownloadResult()
    {
        $response = $this->aria2->purgeDownloadResult();
        return $response;
    }
    /**
     * 移除指定已完成/出错/已移除的下载项
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.removeDownloadResult
     *
     * @param  string $gid 下载项 GID
     * @return bool
     */
    public function removeDownloadResult(string $gid)
    {
        $response = $this->aria2->removeDownloadResult($gid);
        return $response;
    }
    /**
     * 获取 aria2 的版本号以及启用的特性
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getVersion
     *
     * @return mixed[]
     */
    public function getVersion()
    {
        $response = $this->aria2->getVersion();
        return $response;
    }
    /**
     * 获取session信息
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.getSessionInfo
     *
     * @return mixed[]
     */
    public function getSessionInfo()
    {
        $response = $this->aria2->getSessionInfo();
        return $response;
    }
    /**
     * 关闭 aria2
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.shutdown
     *
     * @return bool
     */
    public function shutdown()
    {
        $response = $this->aria2->shutdown();
        return $response;
    }
    /**
     * 强制关闭 aria2
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.forceShutdown
     *
     * @return bool
     */
    public function forceShutdown()
    {
        $response = $this->aria2->forceShutdown();
        return $response;
    }
    /**
     * 保存当前 session 信息
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#aria2.saveSession
     *
     * @return bool
     */
    public function saveSession()
    {
        $response = $this->aria2->saveSession();
        return $response;
    }
    /**
     * 批量调用
     *
     * 不支持调用 system.* 方法
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#system.multicall
     *
     * @param  mixed[]   $methods
     * @return mixed[]
     */
    public function multicall(array $methods)
    {
        $formated = [];
        foreach ($methods as $method) {
            $formated[] = [
                'methodName' => "aria2.{$method['method']}",
                'params'     => $method['args'] ?? [],
            ];
        }
        $response = $this->aria2->stsyemCall('multicall', $formated);
        return $response;
    }
    /**
     * 列举 aria2 RPC 服务支持的方法名（包含“aria2.”前缀）
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#system.listMethods
     *
     * @return string[]
     */
    public function listMethods()
    {
        $response = $this->aria2->stsyemCall('listMethods');
        return $response;
    }
    /**
     * 列举 aria2 RPC 服务支持的通知回调（包含“aria2.”前缀）
     *
     * @see https://aria2.github.io/manual/en/html/aria2c.html#system.listNotifications
     *
     * @return string[]
     */
    public function listNotifications()
    {
        $response = $this->aria2->stsyemCall('listNotifications');
        return $response;
    }
}
