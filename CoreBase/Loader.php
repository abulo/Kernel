<?php
/**
 * Loader 加载器
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午12:21
 */

namespace Kernel\CoreBase;

use Kernel\Components\AOP\AOP;
use Kernel\Memory\Pool;

class Loader implements ILoader
{
    private $_task_proxy;
    private $_model_factory;

    public function __construct()
    {
        $this->_task_proxy = new TaskProxy();
        $this->_model_factory = ModelFactory::getInstance();
    }

    /**
     * 获取一个model
     * @param $model
     * @param Child $parent
     * @return mixed|null
     * @throws SwooleException
     */
    public function model($model, Child $parent)
    {
        if (empty($model)) {
            return null;
        }
        if ($parent->root == null) {
            $parent->root = $parent;
        }
        $root = $parent->root;
        if ($root->hasChild($model)) {
            return AOP::getAOP($root->getChild($model));
        }
        $model_instance = $this->_model_factory->getModel($model);
        $model_instance->root = $root;
        $root->addChild($model_instance);
        $model_instance->initialization($parent->getContext());
        return AOP::getAOP($model_instance);
    }

    /**
     * 获取一个task
     * @param $task
     * @param Child $parent
     * @return mixed|null|TaskProxy
     * @throws SwooleException
     */
    public function task($task, Child $parent = null)
    {
        if (empty($task)) {
            return null;
        }
        if (class_exists($task)) {
            $task_class = $task;
        } else {
            throw new SwooleException("class task_class not exists");
        }
        if (!getInstance()->server->taskworker) {//工作进程返回taskproxy
            $this->_task_proxy->core_name = $task_class;
            if ($parent != null) {
                $this->_task_proxy->setContext($parent->getContext());
            }
            return AOP::getAOP($this->_task_proxy);
        }
        $task_instance = Pool::getInstance()->get($task_class);
        $task_instance->reUse();
        return $task_instance;
    }

    /**
     * view 返回一个模板
     * @param $template
     * @return
     */
    public function view($template)
    {
        $template = getInstance()->templateEngine->make($template);
        return $template;
    }

    public function tpl($template)
    {
        return $this->view(TPL_PATH.DS.$template);
    }
}
