<?php
    require_once 'nusoap/nusoap.php';
    error_reporting(0);

    $ns = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".basename(__FILE__);
    $server = new NuSOAP_Server();
    $server->debug_flag = false;
    $server->soap_defencoding = 'utf-8';
    $server->decode_utf8 = false;
    $server->configureWSDL('FanavardWSDL', $ns);
    $server->wsdl->schemaTargetNamespace = $ns;

    $server->wsdl->addComplexType(
        'UserInfo',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'Id' => array(
                'name' => 'id',
                'type' => 'xsd:int'
            ),
            'User' => array(
                'name' => 'User',
                'type' => 'xsd:string'
            ),
            'Pass' => array(
                'name' => 'Pass',
                'type' => 'xsd:string'
            )
        )
    );
    
    $server->wsdl->addComplexType(
        'addUser',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'userID' => array(
                'name' => 'userID',
                'type' => 'xsd:int'
            ),
            'Message' => array(
                'name' => 'Message',
                'type' => 'xsd:string'
            )
        )
    );

    $server->wsdl->addComplexType(
        'Users',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(
            array(
                'ref'            => 'SOAP-ENC:arrayType',
                'wsdl:arrayType' => 'tns:UserInfo[]'
            )
        ),
        'tns:UserInfo'
    );


  $server->register(
        'GetUsersInfo',                              // Method Name
        array(),           			    // Input Parameters
        array('return' => 'tns:Users'),          // Output Parameters
        $ns,                                        // Namespace
        $ns . '#GetUsersInfo',                       // SOAPAction
        'rpc',                                      // Style
        'encoded',                                  // Use
        'Get Users Information'                    // Documentation
    );


    $server->register(
        'GetUserInfo',                              // Method Name
        array('UserId' => 'xsd:int'),               // Input Parameters
        array('return' => 'tns:UserInfo'),          // Output Parameters
        $ns,                                        // Namespace
        $ns . '#GetUserInfo',                       // SOAPAction
        'rpc',                                      // Style
        'encoded',                                  // Use
        'Get specific user info'                    // Documentation
    );

    $server->register(
        'AddUser',                              // Method Name
        array('UserId' => 'xsd:int' , 'userName' => 'xsd:string' , 'userPass' => 'xsd:string' ),               // Input Parameters
        array('return' => 'tns:addUser'),          // Output Parameters
        $ns,                                        // Namespace
        $ns . '#AddUser',                       // SOAPAction
        'rpc',                                      // Style
        'encoded',                                  // Use
        'Add user to db'                    // Documentation
    );



    function getDB(){
        require_once './classes/database.php';
        $dbObj = new database('conn1');
        return $dbObj;
    }

    function GetUserInfo($userId) {

        $dbObj = getDB();
        $user = $dbObj->read('users' , array() , array('id' => $userId) , '=' , '' , 'LIMIT 1' , 's');

        $result['Id'] = $user->id;
        $result['User'] = $user->user;
        $result['Pass'] = $user->pass;

        return $result;
    }
    
    
    function GetUsersInfo() {
        $dbObj = getDB();
        $users = $dbObj->read('users' , array() , array() , '' , '' , 'ORDER BY id DESC' , 'm');
	
	$result = array();
	foreach($users as $u){
		$result[] = array(
			'Id' => $u['id'] ,
			'User' => $u['user'] ,
			'Pass' => $u['pass'] 
		); 
	}
        return $result;
    }
    
    
    function AddUser($userId , $userName ,  $userPass) {

        $dbObj = getDB();
        $addUser = $dbObj->create('users' , array('id' => $userId , 'user' => $userName , 'pass' => $userPass) );
        
        if($addUser !== false){
            $result['userID'] = $userId;
            $result['Message'] = "ثبت با موفقیت انجام شد .";
        }else{
            $result['userID'] = $userId;
            $result['Message'] = "خطا در ثبت کاربر جدید .";
        }

        return $result;
    }

    $server->service(file_get_contents("php://input"));
    exit();
?>