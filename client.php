<?php
//This page will receive the username and password from clients and then check if they match by
//query the database. If so, it will send back the ip, port and a session code of the server; if not,
//it will send back error message.
class MyDB extends SQLite3
    {
        function __construct()
        {
            $this->open('/Users/hamasakiyijun/Sites/user_admin.db');
        }
    }

    $db = new MyDB();
    if(!$db){
         echo $db->lastErrorMsg();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $db->query("select * from list where name = '$username'");

	$num = count($result);
   

    if($num == 1)
    {
        $row = $result->fetchArray();
    	if( $row['password'] == $password)
    	{
    		$result2 = $db->query("SELECT * FROM serverlocation ORDER BY id DESC LIMIT 1;");
    		$serverlocation = $result2->fetchArray();
            $ip = $serverlocation['ip'];
    		$port = $serverlocation['port'];
    		$sessionCode = $serverlocation['colomn'];

    		echo $ip."#".$port."#".$sessionCode;
    	}
    	else
    	{
            echo " "."#"." "."#"." ";
    	}
    }else
    {
       echo " "."#"." "."#"." ";

    }

?>