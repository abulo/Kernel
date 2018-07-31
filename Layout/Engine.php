<?php


namespace Kernel\Layout;

use Kernel\CoreBase\SwooleException;

class Engine
{
    public $tpl;


    public function __construct()
    {
    }
    /**
     * 获取一个模板
     * @param  $tpl
     * @return
     */
    public function make($tpl)
    {
        $this->tpl = $tpl.'.php';
        return $this;
    }

    /**
     * 获取Html
     * @param   $data
     * @return
     */
    public function render($data = array())
    {
        extract($data);
        $level = ob_get_level();
        try {
            ob_start();
            include $this->tpl;
            $content = ob_get_clean();
            $this->clean();
            return $content;
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            $this->clean();

            throw $e;
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            $this->clean();

            throw $e;
        }
    }

    public function clean()
    {
        $this->tpl = null;
    }
}
