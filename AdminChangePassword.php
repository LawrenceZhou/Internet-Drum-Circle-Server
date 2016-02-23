<?php
    class MyDB extends SQLite3
    {
        function __construct()
        {
            //!!!change this to the path of the databasde on the machine running in CMU!!!
            $this->open('user_admin.db');
        }
    }

    $db = new MyDB();
    if(!$db){
         echo $db->lastErrorMsg();
    } 
    $name = $_COOKIE['name'];
    $result = $db->query("select * from list where name = '$name'");
    if(isset($_POST["submit"])){ 
        $oldpassword = $_POST['oldpassword'];
        $newpassword1 = $_POST['newpassword1'];
        $newpassword2 = $_POST['newpassword2'];
        //old password input incorrect
        if($oldpassword != $result->fetchArray()['password'])
        {
            $message = "Old password is not correct!";
            echo "<script language=\"javascript\"> alert('$message');</script>";
        }
        //first new password is blank
        else if($newpassword1 == '')
        {
            $message = "Please input a new password!";
            echo "<script language=\"javascript\"> alert('$message');</script>";
        }
        //new passwords input are different
        else if($newpassword1 != $newpassword2)
        {
            $message = "New passwords input are different!";
            echo "<script language=\"javascript\"> alert('$message');</script>";
        }
        else
        {
            //clear the cookie and go back to log in interface
            $name = $_COOKIE['name']; 
            $db->exec("update list set password = '$newpassword1' where name = '$name'");
            $db->exec("delete from cookie where name = '$name'");
            setcookie("random_cookie", "", time()-3600);
            $message = "Password has been changed!";
            echo "<script language=\"javascript\"> alert('$message');</script>";
            echo "<script language=\"javascript\">";
            echo "window.location= \"AdminLogin.php\";";
            echo "</script>";
        }
    }
?>

<html>
    <?php 
    $name = $_COOKIE['name']; 
    echo "<body>Current Adiministrator: ".$name."</body>";
    ?>
    <form method="post">
        <center>
            <table bgcolor="AD112F" border="4">
                <tr>
                    <td colspan="2"><center><h1><i><b>Change Password</b></i></h1></center></td>
                </tr>
        
                <tr>
                    <td><center><h1><b>Old Password:</b></h1></center></td>
                    <td><input name="oldpassword" type="password" rows="1" size="20" maxlength="30"></td>
                </tr>
        
                <tr>
                    <td><center><h1><b>New Password:</b></h1></center></td>
                    <td><input name="newpassword1" type="password" rows="1" size="20" maxlength="30"></td>
                </tr>
        
                <tr>
                    <td><center><h1><b>Input New Password Again:</b></h1></center></td>
                    <td><input name="newpassword2" type="password" rows="1" size="20" maxlength="30"></td>
                </tr>
        
                <tr>
                    <td colspan="2"><center><input type="submit" name ="submit" value="Change" ></center></td>
                </tr>
                    
        
            </table>
        </center> 
    </form>
</html>