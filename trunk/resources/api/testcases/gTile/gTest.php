<?php
# Testing the Gtile Script

$image = 'NLU0062116.jpg';

$cmd = sprintf("./googletilecutter-0.11.sh -r tiles/tile_ -z 1 -o 1 -t 0,0 %s",$image);
print '<br> Cmd : ' . $cmd;
$res = shell_exec($cmd);
print '<br>';
var_dump($res);
print '<br>';
var_dump($op);