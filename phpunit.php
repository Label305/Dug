<?php

require_once __DIR__ . '/vendor/hamcrest/hamcrest-php/hamcrest/Hamcrest.php';

function dd($input) {
    var_dump($input);
    die();
}