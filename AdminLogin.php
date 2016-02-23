<html>

<body>

<form action="AdminPort.php" method="post">
<center>
            <table bgcolor="ADFF2F" border="4">
                <tr>
                    <td colspan="2"><center><h1><i><b>Administrator</b></i></h1></center></td>
                </tr>

                <tr>
                    <td><center><h1><b>AID:</b></h1></center></td>
                    <td><center><form name="login"><input name="aid" type="text" value = "<?php echo $_COOKIE['name'];?>" rows="1" size="20" maxlength="30"></center></td>
                </tr>

                <tr>
                    <td><h1><b>Password:</b></h1></td>
                    <td><input name="password" type="password" value = "<?php echo $_COOKIE['random_cookie'];?>" rows="1" size="20" maxlength="30"></td>
                </tr>

                <tr>
                    <td colspan="2"><center><input type="submit" value="Log In" ></center></td>
                </tr>

            </table>
        </center> 
</form>

</body>
</html>