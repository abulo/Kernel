<?php
namespace Kernel\Utilities;

class UploadFile
{

    private $config = array(
        'upload_path'=>'',//上传路径
        'maxSize' => -1, //上传文件的最大值
        'allowExts' => array(), //允许上传的文件后缀，留空则不做限制，不带点
        // 'allowTypes' => array(), //允许上传的文件类型，留空不作检查
        // 'thumb' => false, //对上传的图片进行缩略图处理
        // 'thumbMaxWidth' => '100',//缩略图的最大宽度
        // 'thumbMaxHeight' => '100', //缩略图的最大高度
        // 'thumbPrefix' => 'mini_',//缩略图前缀
        // 'thumbPath'         =>  '',// 缩略图保存路径
        // 'thumbFile'         =>  '',// 缩略图文件名 带后缀
        // 'subDir' => true,//启用子目录保存文件
        // 'subDirType' => 'date', //子目录创建方式，hash\date两种
        // 'dateFormat' => 'Y/m/d', //按日期保存的格式
        // 'hashLevel' => 1, //hash的目录层次
        // 'savePath' => '', //上传文件的保存路径
        // 'replace' => false, //替换同名文件
        // 'rename' => true,//是否生成唯一文件名
    );

    //上传失败的信息
    private $errorInfo = '';

    // 上传成功的文件信息
    private $successInfo ;

    /**
     * 魔术方法快速获取配置
     *
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
        return null;
    }

    /**
     * 魔术方法，快速配置参数
     *
     * @param string $name
     * @param string $value
     *
     * @param void
     */
    public function __set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**魔术方法查询是否存在配置项
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * 构造方法
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        is_array($config) && $this->config = array_merge($this->config, $config);
    }

    /**
     * 上传文件
     * @param  array $file
     * @return bool
     */
    public function upload($file)
    {
        $successInfo = [];
        // Array
        // (
        //     [name] => facepalm.jpg
        //     [type] => image/jpeg
        //     [tmp_name] => /tmp/swoole.upfile.n3FmFr
        //     [error] => 0
        //     [size] => 15476
        // )

        if (!$this->secureCheck($file)) {
            return false;
        }



        // if (!empty($this->config['allowExts'])) {
            // $type = $this->parseTpye($file['type']);
            // if (!in_array($type, $this->config['allowExts'])) {
            //     $this->errorInfo = '不能上传该格式文件';
            //     return false;
            // }
        // }

        $successInfo['file_name'] = $file['name'];
        $successInfo['mime_types'] = $file['type'];
        $successInfo['file_size'] = $file['size'];
        $pathinfo = pathinfo($file['name']);
        $pathinfo['extension'] = strtolower($pathinfo['extension']);
        $successInfo['file_ext'] = $pathinfo['extension'];
        $successInfo['file_type'] = $this->parseTpye($file['type']);
        $successInfo['file_path'] = $this->getSaveName($this->config['upload_path'], $pathinfo);

        $save_filename = $this->config['upload_path'].DS.$successInfo['file_path'];
        if ($this->moveUploadFile($file['tmp_name'], $save_filename)) {
            $this->successInfo = $successInfo;
            return true;
        } else {
            $this->errorInfo = '没有选择上传文件';
            return false;
        }
    }

    /**
     * 获取上传之后的文件路径
     * @param  string $savepath 保存的文件夹
     * @param  array $filename
     * @return
     */
    private function getSaveName($savepath, $file)
    {
        //重命名
        $saveName = $this->createUnique().'.'.$file['extension'];

        //文件已日期保存yyyy/mm/dd
        $subDir = date('Y/m/d');
        $dir = $savepath.DS.$subDir;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $saveName = $subDir.DS.$saveName;
        return $saveName;
    }

    /**
     * 创建随机函数
     * @return  string
     */
    private function createUnique()
    {
        $name = uniqid("", true)
                . mt_rand(5, 5900000000)
                . mt_rand(5, 5900000000)
                . '_'
                . mt_rand()
                . '_'
                . mt_rand(5, 5900000000)
                . '_'
                . microtime(true);

        $chars = md5($name);
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);
        return strtolower($uuid);
    }

    /**
     * 判断该文件是视频还是图片
     * @param  string $type
     * @return string
     */
    private function parseTpye($type)
    {
        if (($pos = strpos($type, 'video')) !== false) {
            return  'video';
        } elseif (($pos = strpos($type, 'image')) !== false) {
            return 'image';
        } else {
            return 'other';
        }
    }

    /**
     * 上传文件
     * @param   string $tmpfile
     * @param   $newfile
     * @return bool
     */
    private function moveUploadFile($tmpfile, $newfile)
    {
        // if (!defined('SWOOLE_SERVER')) {
        //     return move_uploaded_file($tmpfile, $newfile);
        // } else {
        if (rename($tmpfile, $newfile) === false) {
            return false;
        }
            return chmod($newfile, 0777);
        // }
    }





    /**
     * 检查上传的文件有没上传成功是否合法
     *
     * @param array $file 上传的单个文件
     *
     * @return bool
     */
    private function secureCheck($file)
    {
        //文件上传失败，检查错误码
        if ($file['error'] != 0) {
            switch ($file['error']) {
                case 1:
                    $this->errorInfo = '上传的文件大小超过了 php.ini 中 upload_max_filesize 选项限制的值';
                    break;
                case 2:
                    $this->errorInfo = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                    break;
                case 3:
                    $this->errorInfo = '文件只有部分被上传';
                    break;
                case 4:
                    $this->errorInfo = '没有文件被上传';
                    break;
                case 6:
                    $this->errorInfo = '找不到临时文件夹';
                    break;
                case 7:
                    $this->errorInfo = '文件写入失败';
                    break;
                default:
                    $this->errorInfo = '未知上传错误！';
            }
            return false;
        }

        //文件上传成功，进行自定义检查
        if ((-1 != $this->config['maxSize']) &&  ($file['size'] > $this->config['maxSize'])) {
            $this->errorInfo = '上传文件大小不符';
            return false;
        }
        return true;
    }



    /**
     * 取得最后一次错误信息
     *
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * 取得上传文件的信息
     *
     * @return array
     */
    public function getSuccessInfo()
    {
        return $this->successInfo;
    }
}
