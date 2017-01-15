<?php
namespace CjsHook;

class Hooks {

    /**
     * 是否开启钩子
     * @var	bool
     */
    protected $enabled = FALSE;

    /**
     * 配置的钩子
     * @var	array
     */
    protected $hooks =	array();

    /**
     *
     * @var array
     */
    protected $_objects = array();

    /**
     *
     * @var	bool
     */
    protected $_in_progress = FALSE;

    public function __construct($hook=null)
    {
        if ( is_null($hook) || !is_array($hook))
        {
            return;
        }
        $this->hooks = $hook;
        $this->enabled = TRUE;
    }

    public static function create($hook=null)
    {
        return new static($hook);
    }

    public function call_hook($which = '')
    {
        if ( ! $this->enabled || ! isset($this->hooks[$which]))
        {
            return FALSE;
        }

        if (is_array($this->hooks[$which]) && ! isset($this->hooks[$which]['function']))
        {
            foreach ($this->hooks[$which] as $val)
            {
                $this->_run_hook($val);
            }
        }
        else
        {
            $this->_run_hook($this->hooks[$which]);
        }

        return TRUE;
    }

    protected function _run_hook($data)
    {
        // 闭包函数 and array($object, 'method') callables
        if (is_callable($data))
        {
            is_array($data)
                ? $data[0]->{$data[1]}()
                : $data();

            return TRUE;
        }
        elseif ( ! is_array($data))
        {
            return FALSE;
        }


        if ($this->_in_progress === TRUE)
        {
            return;
        }

        if ( ! isset($data['filepath'], $data['filename']))
        {
            return FALSE;
        }

        $filepath = $data['filepath'].'/'.$data['filename'];

        if ( ! file_exists($filepath))
        {
            return FALSE;
        }

        $class		= empty($data['class']) ? FALSE : $data['class'];
        $function	= empty($data['function']) ? FALSE : $data['function'];
        $params		= isset($data['params']) ? $data['params'] : '';

        if (empty($function))
        {
            return FALSE;
        }

        $this->_in_progress = TRUE;

        if ($class !== FALSE)
        {
            if (isset($this->_objects[$class]))
            {
                if (method_exists($this->_objects[$class], $function))
                {
                    $this->_objects[$class]->$function($params);
                }
                else
                {
                    return $this->_in_progress = FALSE;
                }
            }
            else
            {
                class_exists($class, FALSE) || require_once($filepath);

                if ( ! class_exists($class, FALSE) || ! method_exists($class, $function))
                {
                    return $this->_in_progress = FALSE;
                }

                $this->_objects[$class] = new $class();
                $this->_objects[$class]->$function($params);
            }
        }
        else
        {
            function_exists($function) || require_once($filepath);

            if ( ! function_exists($function))
            {
                return $this->_in_progress = FALSE;
            }

            $function($params);
        }

        $this->_in_progress = FALSE;
        return TRUE;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return array
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * @param array $hooks
     */
    public function setHooks($hooks)
    {
        $this->hooks = $hooks;
        return $this;
    }

}
