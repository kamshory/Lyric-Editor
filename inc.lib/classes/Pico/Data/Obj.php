<?php
namespace Pico\Data;
use Pico\DynamicObject\DynamicObject;

class Obj extends DynamicObject{

}

$a = new Obj();

$a->setParam1("ABC");
$b = $a->getParam1();