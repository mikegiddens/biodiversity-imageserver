<?php

$path = '/var/www/images/specimensheets/nlu/0/6/52/15/NLU0065215';

$image = $path . '.jpg';

$tm10 = $path . '_10.jpg';
$tm13 = $path . '_13.jpg';
$tm15 = $path . '_15.jpg';
$tm20 = $path . '_20.jpg';

$tt10 = $path . '_10';
$tt13 = $path . '_13';
$tt15 = $path . '_15';
$tt20 = $path . '_20';


exec("convert " . $image . " -colorspace Gray -contrast-stretch 10% " . $tm10);
exec(sprintf("tesseract %s %s", $tm10, $tt10));
exec(sprintf("rm %s",$tm10));

exec("convert " . $image . " -colorspace Gray -contrast-stretch 13% " . $tm13);
exec(sprintf("tesseract %s %s", $tm13, $tt13));
exec(sprintf("rm %s",$tm13));

exec("convert " . $image . " -colorspace Gray -contrast-stretch 15% " . $tm15);
exec(sprintf("tesseract %s %s", $tm15, $tt15));
exec(sprintf("rm %s",$tm15));

exec("convert " . $image . " -colorspace Gray -contrast-stretch 20% " . $tm20);
exec(sprintf("tesseract %s %s", $tm20, $tt20));
exec(sprintf("rm %s",$tm20));



echo '<br> http://images.cyberfloralouisiana.com/images/specimensheets/nlu/0/6/52/15/' . str_replace('/var/www/images/specimensheets/nlu/0/6/52/15','',$tt10.'.txt');
echo '<br> http://images.cyberfloralouisiana.com/images/specimensheets/nlu/0/6/52/15/' . str_replace('/var/www/images/specimensheets/nlu/0/6/52/15','',$tt13.'.txt');
echo '<br> http://images.cyberfloralouisiana.com/images/specimensheets/nlu/0/6/52/15/' . str_replace('/var/www/images/specimensheets/nlu/0/6/52/15','',$tt15.'.txt');
echo '<br> http://images.cyberfloralouisiana.com/images/specimensheets/nlu/0/6/52/15/' . str_replace('/var/www/images/specimensheets/nlu/0/6/52/15','',$tt20.'.txt');

?>