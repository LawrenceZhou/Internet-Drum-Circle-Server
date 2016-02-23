<?php
//This page is used to check if the newly registered userid has existed.
//If so, it will alert the waring message; if not, it will add a new user into database.
    class MyDB extends SQLite3
    {
        function __construct()
        {
            $this->open('/Users/hamasakiyijun/Sites/user_admin.db');//this should be modified to the excact path of the database
        }
    }

    $db = new MyDB();
    if(!$db){
         echo $db->lastErrorMsg();
    }

    $userid = $_POST['userid'];
    $result = $db->query("select count(*) as num from list where name = '$userid'");

    $num = $result->fetchArray();
    $row = $num['num'];

    if($row != 0)
    {
       $message = "User id has existed! ";
    }else
    {  
        $pwd = $_POST['pwd1'];
        $email = $_POST['email'];
        if($db->exec("insert into list(name, password, type, status, enabled, email) values('$userid', '$pwd', 'user', 'waiting', '---', '$email')"))
        {
            $message = "Registeration succeeded! ";
        } 
        else
        {
            $message = $db->lastErrorMsg();
        }
    }
     echo "<html>";
        echo "<body>";
        echo "<script language=\"javascript\"> alert('$message');</script>";        
        echo "<script language=\"javascript\">";
        echo "window.location= \"register.html\";";
        echo "</script>";
        echo "</body>";
        echo "</html>";
?>