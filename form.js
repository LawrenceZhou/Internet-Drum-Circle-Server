function ckUserid() {  
    var userid = document.forms["regForm"].userid.value;  
    var useridResult = document.getElementById("useridResult");  
    if(userid.length<6 || userid.length>12) {  
        useridResult.innerHTML = "<font color='red'>Userid must be between 6 and 12 characters long! </font>";  
        return false;  
    }else{  
        useridResult.innerHTML = "<font color='green'>Userid is valid. </font>";  
        return true;  
    }  
}  
  
function ckEmail() {  
    var email = document.forms["regForm"].email.value;  
    var emailResult = document.getElementById("emailResult");
     if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email))  
    {  
        emailResult.innerHTML = "<font color='green'>Email is valid. </font>";  
        return true;  
    } 
    emailResult.innerHTML = "<font color='red'>Email is invalid! </font>";  
    return false;
}  

function ckPwd1() {  
    var pwd1 = document.forms["regForm"].pwd1.value;  
    var pwd1Result = document.getElementById("pwd1Result");  
    if(pwd1.length<6 || pwd1.length>12) {  
        pwd1Result.innerHTML = "<font color='red'>Password must be between 6 and 12 characters long! </font>";  
        return false;  
    }else{  
        pwd1Result.innerHTML = "<font color='green'>Password is valid. </font>";  
        return true;  
    }  
}  
  
function ckPwd2() {  
    var pwd1 = document.forms["regForm"].pwd1.value;  
    var pwd2 = document.forms["regForm"].pwd2.value;  
    var pwd2Result = document.getElementById("pwd2Result");  
    if(pwd2.length<6 || pwd2.length>12) {  
        pwd2Result.innerHTML = "<font color='red'>Password must be between 6 and 12 characters long! </font>";  
        return false;  
    }else if(pwd1 != pwd2) {  
        pwd2Result.innerHTML = "<font color='red'>Passwords must match! </font>";  
        return false;  
    }else{  
        pwd2Result.innerHTML = "<font color='green'>Passwords match. </font>";  
        return true;  
    }  
}  
function ckCaptcha(){
    var correctAnswer = document.getElementById('txtCaptcha').value;
    var submitAnswer = document.getElementById('recaptcha').value;
    var captchaResult = document.getElementById("captchaResult");
    if(submitAnswer.length == 0) {  
        captchaResult.innerHTML = "<font color='red'>Please input the answer! </font>";  
        return false;  
    }else if(submitAnswer != correctAnswer) {  
        captchaResult.innerHTML = "<font color='red'>Answer is not correct! </font>";  
        return false;  
    }else{  
        captchaResult.innerHTML = "<font color='green'>Answer is correct. </font>";  
        return true;  
    }
}
function mySubmit() {  
    if(ckUserid() && ckPwd1() && ckPwd2()) {  
        document.forms["regForm"].submit();  
    }  
}  
  
function myReset() {  
    if(window.confirm("Are you sure to reset the registration? Information input will be erased.")) {  
        document.forms["regForm"].reset();  
        document.getElementById("useridResult").innerHTML="";  
        document.getElementById("pwd1Result").innerHTML="";  
        document.getElementById("pwd2Result").innerHTML="";  
    }  
}  