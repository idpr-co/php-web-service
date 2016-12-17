<?php
header('Content-Type: application/json');

require_once './classes/database.php';


try {
	$con = new DataBase('conn1');
} catch(Exseption $e) {
	die('<h1>' . $e->getMessage() . '</h1>');
}




if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $publicKey = isset($_POST['publicKey']) ? base64_decode($_POST['publicKey']) : "";
    $publicKey = substr($publicKey , 0 , strpos($publicKey , "_") );

    if($publicKey == "YOUR_PUBLIC_KEY" ) {

	
	// get post parametres
	$action = !empty($_POST['action']) ? $_POST['action'] : "";



	if($action == "yourAction"){
		
		// Action codes here
			
	} else {
		$errorArray= array(
			'errorCode'=> -102 , 
			'message'=>'No Action' 
		);
		echo json_encode($errorArray);
	}

   }  
} else {
	$errorArray= array(
		'errorCode'=> -101 , 
		'message'=>'Only POST requests allowed' 
	);
	echo json_encode($errorArray);
}

?>