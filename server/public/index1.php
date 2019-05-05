<?php

/**
 * RFC添加4种的标量类型声明
 * 要不返回E_RECOVERABLE_ERROR
 */
 declare(strict_types = 1);
function test(int $num): int
{
    return $num;
}
echo test(1).'<br/>';
echo test(false);
// $foo = substr(52, 1);
// var_dump($foo);

?>