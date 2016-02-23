<?php
class MyDB extends SQLite3
{
    function __construct()
    {
    	//!!!change this to the path of the databasde on the machine running in CMU!!!
        $this->open('Useruser_admin.db');
    }
}

//set execution time unlimited
set_time_limit(0);

$db = new MyDB();
if(!$db){
     echo $db->lastErrorMsg();
} 
//clear the cookie from browser and database
setcookie("random_cookie", "", time()-3600);
$name = $_COOKIE['name'];
$db->exec("delete from cookie where name = '$name'");
$db->close();
//go back to log in interface
header('Refresh: 3;url=AdminLogin.php');
echo "<html>";
echo "<body>";
echo "Log out successfully!";
echo "</body>";
echo "</html>";   
              
?>