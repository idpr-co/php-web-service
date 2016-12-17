<?php
    require_once 'nusoap/nusoap.php';
?>
<!doctype html>
<html>
<head>
<title>تست وب سرویس</title>
<meta charset="UTF-8"/>
</head>
<body>
<?php
    $client = new NuSOAP_Client("http://idprco.ir/1/WS.php?wsdl", 'wsdl');
    $client->soap_defencoding = 'UTF-8';
    $client->decode_utf8 = false;
    echo '<pre>' . PHP_EOL;
    $parameters = array('UserId' => 1);


        
$result = $client->call('GetUserInfo', $parameters);
    // Check for a fault
if ($client->fault) {
    echo '<h2>Fault</h2><pre>';
     print_r(var_dump($result));
    echo '</pre>';
} else {
    // Check for errors
    $err = $client->getError();
    if ($err) {
        // Display the error
        echo '<h2>Error</h2><pre>' . $err . '</pre>';
    } else {
        // Display the result
        echo '<h2>Result</h2><pre>';
        print_r($result);
        echo '</pre>';
    }
}

$result = $client->call('GetUsersInfo');
    // Check for a fault
if ($client->fault) {
    echo '<h2>Fault</h2><pre>';
     print_r(var_dump($result));
    echo '</pre>';
} else {
    // Check for errors
    $err = $client->getError();
    if ($err) {
        // Display the error
        echo '<h2>Error</h2><pre>' . $err . '</pre>';
    } else {
        // Display the result
        echo '<h2>Result</h2><pre>';
        print_r($result);
        echo '</pre>';
    }
}


    $parameters = array('userName' => 'wsdl' , 'userPass' => '2445');
    $result = $client->call('AddUser', $parameters);
    // Check for a fault
if ($client->fault) {
    echo '<h2>Fault</h2><pre>';
     print_r(var_dump($result));
    echo '</pre>';
} else {
    // Check for errors
    $err = $client->getError();
    if ($err) {
        // Display the error
        echo '<h2>Error</h2><pre>' . $err . '</pre>';
    } else {
        // Display the result
        echo '<h2>Result</h2><pre>';
        print_r($result);
        echo '</pre>';
    }
}


    
    
    echo '</pre>' . PHP_EOL;

?>

</body>
</html>