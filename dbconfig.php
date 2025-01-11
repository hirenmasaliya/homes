<?php

require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount('society-management-14-firebase-adminsdk-jwkat-a65b001b78.json')
    ->withDatabaseUri('https://society-management-14-default-rtdb.firebaseio.com')
;

$database = $factory->createDatabase();

$storage = $factory->createStorage();



?>