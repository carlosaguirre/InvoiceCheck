<?php
function F1()
{
    echo "<br />";
    echo "in F1 now";
    echo "<pre>".print_r(debug_backtrace(2),true)."</pre>";
}

class DebugOptionsTest
{
    function F2()
    {
        echo "<br />";
        echo "in F2 now";
        F1();
    }

}

echo "<hr />calling F1";
F1();

$c=new DebugOptionsTest();
echo "<hr /><hr /><hr />calling F2";
$c->F2("testValue");
