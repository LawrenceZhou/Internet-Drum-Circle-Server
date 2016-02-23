<?php
    function create_cookie($cookie_length)  //create a 6 units random password
    {  
    $randomseq = '';  
    for ($i = 0; $i < $cookie_length; $i++)  
    {  
    $randomseq .= chr(mt_rand(33, 126));  
    }  
    return $randomseq;  
    }
    function login_success($name, $password)
    {
        echo "<html>";
        echo "<body>";
        echo "Log in successfully! Welcome ".$name."!";
        echo "</body>";

        echo "<form name=\"admin\" method=\"post\" action=admin.php>";
        echo "<input name=\"aid\" type=\"hidden\" value=\"".$name."\">";
        echo "<input name=\"pwd\" type=\"hidden\" value=\"".$password."\">";
        echo "</form>";
        echo "<br><a href=\"#1\" onclick=\"admin.submit()\">Admin Page</a></br>";

        echo "<br><a href=\"AdminLogOut.php/\">Log Out</a></br>";
        echo "<br><a href=\"AdminChangePassword.php\">Change Password</a></br>";
        echo "</html>";  
    }

    class MyDB extends SQLite3
    {
        function __construct()
        {
            //!!!change this to the path of the databasde on the machine running in CMU!!!
            $this->open('user_admin.db');
        }
    } 
    //no limit in execution time
    set_time_limit(0);
    
    $db = new MyDB();
    if(!$db){
         echo $db->lastErrorMsg();
    } 
    //fetch name and password written before
    $name = $_POST["aid"];
    $password = $_POST["password"];
    
    $result = $db->query("select count(*) as num from list where name = '$name'");
  
    $num = $result->fetchArray();
    $row = $num['num'];
    //no username in database
    if($row != 1)
    {
        //alert and go back to log in interface
        echo "<html>";
        echo "<body>";
        $message = "uid is invalid!";
        echo "<script language=\"javascript\"> alert('$message');</script>";
    
        echo "<script language=\"javascript\">";
        echo "window.location= \"AdminLogin.php\";";
        echo "</script>";
        echo "</body>";
        echo "</html>";
    }
    else
    {
        $result2 = $db->query("select * from list where name = '$name'");
        $result3 = $db->query("select count(*) as num from cookie where name = '$name'");
        $num3 = $result3->fetchArray();
        $row3 = $num3['num'];
        //no cookie set before
        if($row3 != 1)
        {
            //password input is correct
            if($password == $result2->fetchArray()['password'])
            {
                //set cookies, name and password expire 1 hr after
                setcookie("name", $name, time()+3600);  
                $randompwd = create_cookie(6);
                setcookie("random_cookie", $randompwd, time()+3600);
                $db->exec("insert into cookie(name, random_cookie, type) values('$name', '$randompwd', 'user')");
                login_success($name, $password);
                   
            }
            //password input is wrong
            else 
            {
                //alert and go back to log in interface
                echo "<html>";
                echo "<body>";
                $message = "password is wrong!";
                echo "<script language=\"javascript\"> alert('$message');</script>";

                echo "<script language=\"javascript\">";
                echo "window.location= \"AdminLogin.php\";";
                echo "</script>";
                echo "</body>";
                echo "</html>";
            }
        }
        //there's cookie set before
        else
        {
            $result4 = $db->query("select * from cookie where name = '$name'");
            //cookie matches cookie in database
            if($password == $result4->fetchArray()['random_cookie'])
            {
                login_success($name, $password);  
            }
            //password matches
            else if($password == $result2->fetchArray()['password'])
            {
                //set cookie
                setcookie("name", $name, time()+3600);
                $randompwd = create_cookie(6);
                setcookie("random_cookie", $randompwd, time()+3600);
                $db->exec("update cookie set random_cookie = '$randompwd' where name = '$name'");
                login_success($name, $password);

            }
            //neither cookie nor password matches
            else
            {
                echo "<html>";
                echo "<body>";
                $message = "password is wrong!";
                echo "<script language=\"javascript\"> alert('$message');</script>";
        
                echo "<script language=\"javascript\">";
                echo "window.location= \"AdminLogin.php\";";
                echo "</script>";
                echo "</body>";
                echo "</html>";
            }
        }            
    }

    $db->close();
?>