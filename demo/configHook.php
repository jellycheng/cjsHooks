<?php
$enable_hooks = true;

$hook['pre_controller'] = array(
    'class'    => 'MyClass',   //调用的类名,没有就留空
    'function' => 'Myfunction',  //调用的函数,没有就留空
    'filename' => 'Myclass.php',  //函数或类 所在的文件,没有就留空
    'filepath' => 'hooks',
    'params'   => array('beer', 'wine', 'snacks'),   //钩子接受的参数
);

$hook['post_controller'] = function()
{
    /* 闭包方式配置的钩子 */
};

//一个钩子多个调用点的方式
$hook['pre_controller2'][] = array(
    'class'    => 'MyClass',
    'function' => 'MyMethod',
    'filename' => 'Myclass.php',
    'filepath' => 'hooks',
    'params'   => array('beer', 'wine', 'snacks')
);

$hook['pre_controller2'][] = array(
    'class'    => 'MyOtherClass',
    'function' => 'MyOtherMethod',
    'filename' => 'Myotherclass.php',
    'filepath' => 'hooks',
    'params'   => array('red', 'yellow', 'blue')
);


