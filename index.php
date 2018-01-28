<?php

use vbpupil\InputValidation;

include 'vendor/autoload.php';

$data = [
    'telephone' => '01604 464237',
    'email' => 'haines@hotmail.com',
    'phone' => '0790 1654273',
    'postcode' => 'nn5 7jy'
];


InputValidation::validate($data);