<?php
$str = '(test=oke,id="18", srs="ICC Womens World Cup Qualifier, 2010", mchDesc="BANW vs PMGW", mnum=10, length=100, coba=cobaya, apa="apa aja")';

$re1 = '/(\w+)\=\"([a-zA-Z0-9 ,.\/&%?=]+)\"/m';
preg_match_all($re1, $str, $matches);
$c1 = array_combine($matches[1], $matches[2]);

$re2 = '/(\w+)\=([a-zA-Z0-9.\/&%?=]+)/m';
preg_match_all($re2, $str, $matches);
$c2 = array_combine($matches[1], $matches[2]);

$c = array_merge($c1, $c2);
print_r($c);