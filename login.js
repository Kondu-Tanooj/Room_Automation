function adminlogin(){
    var us1 = document.getElementById("adminusername").value;
    var psw1 = document.getElementById("adminpass").value;

    var uname1=new RegExp("^[a-zA-Z][a-zA-Z0-9]*$");
    var un1 = uname1.exec(us);

    if((psw1.length>6) && (un1)){
        return true;
    }
    else{
        var msg1="";
        if(!un1){msg1 += "username is not valid\n";document.getElementById("adminusername").focus();}
        if(psw1.length<=6){msg1 += "Password must be greater than 6 characters\n";document.getElementById("adminpass").focus();}
        alert(msg1);
        return false;
    }
}