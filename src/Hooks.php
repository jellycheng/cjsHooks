<?php
namespace CjsHook;

use ArrayAccess;
class Hooks implements ArrayAccess {

    /**
     * 是否开启钩子
     * @var	bool
     */
    protected $enabled = false;

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
    protected $_in_progress = false;

    public function __construct($hook=null)
    {
        if ( is_null($hook) || !is_array($hook))
        {
            return;
        }
        $this->hooks = $hook;
        $this->enabled = true;
    }

    public static function create($hook=null)
    {
        return new static($hook);
    }

    public function call_hook($which = '')
    {
        if ( ! $this->enabled || ! isset($this->hooks[$which]))
        {
            return false;
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

        return true;
    }

    protected function _run_hook($data)
    {
        // 闭包函数 and array($object, 'method') callables
        if (is_callable($data))
        {
            is_array($data)
                ? $data[0]->{$data[1]}()
                : $data();

            return true;
        }
        elseif ( ! is_array($data))
        {
            return false;
        }

        if ($this->_in_progress === true)
        {
            return false;
        }

        if (isset($data['filename']) && file_exists($data['filename']))
        {
            require_once($data['filename']);
        }

        $class		= empty($data['class']) ? false : $data['class'];
        $function	= empty($data['function']) ? false : $data['function'];
        $params		= isset($data['params']) ? $data['params'] : '';

        if (empty($function))
        {
            return false;
        }

        $this->_in_progress = true;

        if ($class !== false)
        {//面向对象方式
            if (isset($this->_objects[$class]))
            {
                if (method_exists($this->_objects[$class], $function))
                {
                    $this->_objects[$class]->$function($params);
                }
                else
                {
                    return $this->_in_progress = false;
                }
            } else {
                if ( ! class_exists($class, true) || ! method_exists($class, $function))
                {
                    return $this->_in_progress = false;
                }
                $this->_objects[$class] = new $class();
                $this->_objects[$class]->$function($params);
            }
        } else {//面向过程方式
            if ( ! function_exists($function))
            {
                return $this->_in_progress = false;
            }
            call_user_func($function, $params);
        }

        $this->_in_progress = false;
        return true;
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

    public function addHook($key, $val)
    {
        $this->offsetSet($key, $val);
        return $this;
    }

    public function delHook($key)
    {
        $this->offsetUnset($key);
        return $this;
    }

    public function offsetExists($offset)
    {
        return isset($this->hooks[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->hooks[$offset])?$this->hooks[$offset]:'';
    }

    public function offsetSet($offset, $value)
    {
        if (isset($this->hooks[$offset]))
        {
            $this->hooks[$offset][] = $value;
        }
        else
        {
            $this->hooks[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->hooks[$offset]);
    }


}
