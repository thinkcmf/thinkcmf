<?php

$arr=[
    'key1'=>'dean',
    'key2'=>'old_cat',
    'key3'=>[
        'key4'=>'tuan'
    ]
];

unset($arr['key1']);
print_r($arr);