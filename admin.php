<?php
//a class to open the sqlite database
class MyDB extends SQLite3{
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

$name = $_POST["aid"];
$password = $_POST["pwd"];

$result = $db->query("select count(*) as num from list where name = '$name'");
$num = $result->fetchArray();
$row = $num['num'];
//no username in database
if($row != 1|| $name ==""){
    //alert and go back to log in interface
    $message="uid is invalid!";
    echo "<script language=\"javascript\"> alert('$message');";
    echo "window.location= \"AdminLogin.php\";";
    echo "</script>";
}
else{
    $result2 = $db->query("select * from list where name = '$name'");
    $result3 = $db->query("select count(*) as num from cookie where name = '$name'");
    $num3 = $result3->fetchArray();
    $row3 = $num3['num'];
    //no cookie set before
    if($row3 != 1){
        //password input is correct
        if($password == $result2->fetchArray()['password']){
            display();         
        }
        //password input is wrong
        else {
            //alert and go back to log in interface
            $message="password is wrong!";
            echo "<script language=\"javascript\"> alert('$message');";
            echo "window.location= \"AdminLogin.php\";";
            echo "</script>";
        }
    }
    //there's cookie set before
    else{
        $result4 = $db->query("select * from cookie where name = '$name'");
        //cookie matches cookie in database
        if($password == $result4->fetchArray()['random_cookie']){
            display(); 
        }
        //password matches
        else if($password == $result2->fetchArray()['password']){
             display();

        }
        //neither cookie nor password matches
        else{
            $message="password is wrong!";
            echo "<script language=\"javascript\"> alert('$message');";
            echo "window.location= \"AdminLogin.php\";";
            echo "</script>";
        }
    }            
}

//if the button 'accept selected' is clicked, it will change the status of the
//selected users to 'member' in database, and load into admin.php finally.
if(isset($_POST["AcceptSelected"])){
    $results = $db->query("SELECT * FROM list");
    while ($row = $results->fetchArray()) {
        if(isset($_POST[$row['id']]) && $row['status']=='waiting'){
            $id = $row['id'];
            $db->exec("UPDATE list SET status='member' WHERE id ='$id'");
        }
    }

    $message = "All the selected have been accepted!";
    echo "<form name=\"accept\" method=\"post\" action=admin.php>";
    echo "<input name=\"aid\" type=\"hidden\" value=\"".$name."\">";
    echo "<input name=\"pwd\" type=\"hidden\" value=\"".$password."\">";
    echo "</form>";
    echo "<script type=\"text/javascript\"> alert('$message');";
    echo "document.accept.submit()";
    echo "</script>";
}
//if the button 'enable selected' is clicked, it will enale the
//selected users in database, and load into admin.php finally.
else if(isset($_POST["EnableSelected"])){
    $results = $db->query("SELECT * FROM list");
    while ($row = $results->fetchArray()) {
        if(isset($_POST[$row['id']]) && $row['enabled']=='---'){
            $id = $row['id'];
            $db->exec("UPDATE list SET enabled = 'Yes' WHERE id ='$id'");
        }
    }
    $message = "All the selected have been enabled!";
    echo "<form name=\"enable\" method=\"post\" action=admin.php>";
    echo "<input name=\"aid\" type=\"hidden\" value=\"".$name."\">";
    echo "<input name=\"pwd\" type=\"hidden\" value=\"".$password."\">";
    echo "</form>";
    echo "<script type=\"text/javascript\"> alert('$message');";
    echo "document.enable.submit()";
    echo "</script>";
}
//if the button 'disable selected' is clicked, it will disable the
//selected users in database, and load into admin.php finally.
else if(isset($_POST["DisableSelected"])){
    $results = $db->query("SELECT * FROM list");
    while ($row = $results->fetchArray()) {
        if(isset($_POST[$row['id']]) && $row['enabled']=='Yes'){
            $id = $row['id'];
            $db->exec("UPDATE list SET enabled = '---' WHERE id ='$id'");
        }
    }

    $message = "All the selected have been disabled!";
    echo "<form name=\"disable\" method=\"post\" action=admin.php>";
    echo "<input name=\"aid\" type=\"hidden\" value=\"".$name."\">";
    echo "<input name=\"pwd\" type=\"hidden\" value=\"".$password."\">";
    echo "</form>";
    echo "<script type=\"text/javascript\"> alert('$message');";
    echo "document.disable.submit()";
    echo "</script>";
}

function display(){
    $db = new MyDB();
    if(!$db){
        echo $db->lastErrorMsg();
    } 
    $name = $_POST["aid"];
    $password = $_POST["pwd"];
    echo " 
    <script language=\"JavaScript\">
    //dic is an array,used to map email address to user id
    var dic = [];
    //if 'select all'is checked, it will call the CheckAll(), to check the checkboxes of all the users
    function CheckAll(source, status) {
        for(var i=0, n=source.elements.length;i<n;i++) {
            if (source.elements[i].className == \"member\" || source.elements[i].className == \"waiting\" ) {
                source.elements[i].checked = status;
              }
        }
    }
    //if 'select accepted'is checked, it will call the CheckAccepted(),
    //to check the checkboxes of all the accepted users
    function CheckAccepted(source, status) {
        for(var i=0, n=source.elements.length;i<n;i++) {
            if (source.elements[i].className == \"member\" ) {
                source.elements[i].checked = status;
              }
        }
    }
    //if 'email selected' is clicked, it will call the CreateList(), 
    //to show the email address of the selected users in a textarea
    function CreateList(source) {
        var content = document.getElementById(\"emaillist\");
        var text = '';
        for(var i = 0, n = source.elements.length; i < n; i++) {
            if (source.elements[i].checked){
                for (var k = 0; k < dic.length;k++) {
                    if (dic[k][0] == source.elements[i].name) {
                        text += dic[k][1] + '\\n';
                    }
                }
            }
        }
        content.value = text;
        
    }
    </script>
    <html>
       <head>Administration Page</head>
       <body>
    
       <form name=\"regForm\" id =\"regForm\" method=\"post\">
       <input name=\"aid\" type=\"hidden\" value=\"".$name."\">
       <input name=\"pwd\" type=\"hidden\" value=\"".$password."\">
       <table>
       <tr><th></th><th>ID</th><th>name</th><th>status</th><th>enabled</th><th>email address</th></tr>";
    $k = 0;
    $results = $db->query("SELECT * FROM list");
    //show all the users' info
    while ($row = $results->fetchArray()) {
        //if the user hasn't been accepted, it will show as red
        if($row['status'] == 'waiting'){
            echo "<tr><td bgcolor=\"red\"><input type=\"checkbox\""." name =".$row['id']." class = \"waiting\"/></td>";
            echo "<td bgcolor=\"red\">".$row['id']."</td>";
            echo "<td bgcolor=\"red\">".$row['name']."</td>";
            echo "<td bgcolor=\"red\">".$row['status']."</td>";
            echo "<td bgcolor=\"red\">".$row['enabled']."</td>";
            echo "<td bgcolor=\"red\">".$row['email']."</td></tr>";

            echo "<script language = \"JavaScript\">dic.push([".$row['id'].",\"".$row['email']."\"]);</script>";
        }
        else{
            //if the user hasn't been ensbled, it will show as yellow
            if($row['enabled'] == '---'){
                echo "<tr><td bgcolor=\"yellow\"><input type=\"checkbox\""." name =".$row['id']." class = \"member\"/></td>";
                echo "<td bgcolor=\"yellow\">".$row['id']."</td>";
                echo "<td bgcolor=\"yellow\">".$row['name']."</td>";
                echo "<td bgcolor=\"yellow\">".$row['status']."</td>";
                echo "<td bgcolor=\"yellow\">".$row['enabled']."</td>";
                echo "<td bgcolor=\"yellow\">".$row['email']."</td></tr>"; 

                echo "<script language = \"JavaScript\">dic.push([".$row['id'].",\"".$row['email']."\"]);</script>";
            }
            else{
                echo "<tr><td><input type=\"checkbox\""." name =".$row['id']." class = \"member\"/></td>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['name']."</td>";
                echo "<td>".$row['status']."</td>";
                echo "<td>".$row['enabled']."</td>";
                echo "<td>".$row['email']."</td></tr>";

                echo "<script language = \"JavaScript\">dic.push([".$row['id'].",\"".$row['email']."\"]);</script>";    
            }      
        }
        
        $k++;
        
    }

    echo "</table>
    
        <table>
        <tr><td><input type=\"checkbox\" onClick=\"CheckAll(document.getElementById('regForm'), this.checked)\" class = \"control\"/>Select All</td></tr>
        <tr><td><input type=\"checkbox\" onClick=\"CheckAccepted(document.getElementById('regForm'), this.checked)\" class = \"control\"/>Select Accepted</td></tr>
        </table>
    
        <table>
        <tr><td><input type=\"submit\" value=\"accept selected\" name = \"AcceptSelected\"></td>
        <td><input type=\"submit\" value=\"enable selected\" name = \"EnableSelected\"></td>
        <td><input type=\"submit\" value=\"disable selected\" name = \"DisableSelected\"></td></tr>
        <tr><td><input type=\"button\" onClick=\"CreateList(document.getElementById('regForm'))\" value=\"email selected\" id = \"EmailSelected\" name = \"EmailSelected\"></td></tr>
        </table>
    </form>
        email list:<br>
        <textarea rows=\"100\" cols=\"30\" id=\"emaillist\" readonly>";
    $results = $db->query("SELECT * FROM list");
    while ($row = $results->fetchArray()) {
        echo ''.$row['email']."\n";
    }

    echo "</textarea><br>
    
    </body>
    </html>";
}
?>
