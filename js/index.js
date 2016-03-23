


var vConstants = { //contains constant variables
    clientVersion: '3.0.4',     
    timeoutTime: 8000
};

function jMessage(_action,_data) {
    this.action = _action;
    this.data = _data;
}
function jServerRequest (_url,_message,_complete) {
    this.url = _url;
    this.message = _message;
    this.complete = _complete;
}

/**
* handles errors
*/
var jError= {
    show: function(_err,_errmsg) {
        var buffer="<h1><font color=red>"+_err+"</font></h1> <b>"+_errmsg+"</b><p/>";
        $("#errormessage").html(buffer);
        $("#errorscreen").show();
        console.log(_err);
    },
    clear: function() {
        $("#errorscreen").hide();
    }
};

/**
 * handles messages send from the server
 * @type type
 */
var jMessageHandler = {
    handle: function(messages) {
        jQuery.each(messages, function(index,message){                                        
            try {                                
                //check which action to perform
                switch (message.a) {
                    case 'loginstatus':                                      
                        if (message.d.status==true) {                            
                            window.location = 'map.php';
                        }else{
                            jLoginBox.showLogin('login failed')
                        }
                        break;                    
                }                
            }
            catch (e){
                console.log(e);
            }
        });
    }
};

var jServer = {    
    serverRequests : new Array(),
    callsDone: true,
    clockOffset: 0,
    /**
     * sends a message to the server and calls the message handler on success;
     * waits for the previous ajax request to finish or timeout before sending the next
     * @param {string} _url ajax/url
     * @param {array} _message array containing jMessages
     * @param {callback} _complete function that gets called once completed     
     */
    sendRequest: function(_url,_message,_complete) {
        jServer.serverRequests.push(new jServerRequest(_url,_message,_complete));
        if (jServer.callsDone) {
            jServer.ajaxRequest();
        }
    },        
    ajaxRequest: function() {
        jServer.callsDone = false;
        var request = jServer.serverRequests.shift();
        if (typeof (request)==='undefined') {
            jServer.callsDone = true;            
        }else {
            var url = './server/ajax/'+request.url;
            //encode variables and array
            var _messageJSON = JSON.stringify(request.message);
            $.ajax({
                timeout:vConstants.timeoutTime,
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {vers:vConstants.clientVersion,m:_messageJSON},
                success: function(json) {
                    jError.clear();                    
                    jMessageHandler.handle(json.m);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    jError.show(errorThrown);                
                },
                complete: function() {
                    if (typeof (request.complete)=== 'function') {
                        request.complete();
                    }                    
                    jServer.ajaxRequest();
                }
            });
        }
        
    },
    syncClock: function (_serverTime) {
    var clientTime = Math.round(new Date().getTime() / 1000);
    jServer.clockOffset = parseInt(_serverTime) - clientTime;
    console.log('set clock offset to '+jServer.clockOffset+'s');
}
};

var jUI = {
    sendLogin:function() {
        var username=$("#username").val();
        var password=$("#password").val();
        var rememberme = false;
        if ($('#remember').is(':checked')) {
            rememberme = true;
        }
        var messageArray = new Array(new jMessage('Login', {password:password,username:username,remember:rememberme}));
        jServer.sendRequest('request.php',messageArray);        
    },
    redirectRegister: function() {        
        $('#hiddenform').submit();
    }
};

var jLoginBox  = {
    showLogin: function(_error) {
        this.hide();
        var output='';
        output+='<h1>Login</h1>';
        output+='<table class="login">';
        if (typeof (_error)!='undefined') {
            output+='<tr><td colspan=2><i><font color=red>'+_error+'</font></i></td></tr>';
        }
        output+='<tr><td><b>Username</b></td><td><input id="username" type="text"></td></tr>';
        output+='<tr><td><b>Password</b></td><td><input id="password" type="password"></td></tr>';
        output+='<tr><td><input type="submit" value="login" onclick="jUI.sendLogin()"></td><td><input type="checkbox" id="remember">remember me</td></tr>';        
        output+='<tr><td colspan=2 style="text-align: center"><a class="link" onClick="jLoginBox.showRegister()">register</a></td></tr>';
        output+='</table>';
        $('#loginbox').html(output);
        $('#loginbox').show();
        $('#username').focus();
        $('#password').keypress(function(e){ //enter submit for system adder
            if (e.which == 13) {
                e.preventDefault();
                jUI.sendLogin();
            }
        });
    },    
    hide: function() {
        $('.loginbox').hide();
    },
    showRegister: function(_error) {
        var regusername = typeof ($('#regusername').val())!='undefined' ? $('#regusername').val():'';
        this.hide();
        var output='';
        output+= '<h2>Register</h2>';
        output+= '<table class="login">';
        output+= '<tr><td><b>Username</b></td><td><input type="text" id="regusername" value="'+regusername+'"></td></tr>';
        output+= '<tr><td><b>Password</b></td><td><input type="password" id="regpassword"></td></tr>';
        output+= '<tr><td><b>Password Repeat</b></td><td><input type="password" id="regrepeat"></td></tr>';
        if (typeof (_error)!='undefined') {
            output+='<tr><td colspan=2><i><font color=red>'+_error+'</font></i></td></tr>';
        }
        output+= '<tr><td><input type="submit" value="back" onClick="jLoginBox.showLogin()"></td><td><input type="submit" value="next" onClick="jLoginBox.showRegisterTwo()"></td></tr>';
        output+= '</table>';
        $('#registerbox').html(output);
        $('#registerbox').show();
        $('#regusername').focus();
    },
    showRegisterTwo: function() {
        //check if password repeat is correct
        
        if ($('#regusername').val().length<4) {
            jLoginBox.showRegister('username to short (4 characters minimum)');
            return false;
        }else
        if ($('#regpassword').val().length<6) {
            jLoginBox.showRegister('password to short (6 characters minimum)');
            return false;
        }else
        if ($('#regpassword').val() != $('#regrepeat').val()) {
            jLoginBox.showRegister('password repetition incorrect');
            return false;
        }
    
        var regusername = $('#regusername').val();
        var regpassword = $('#regpassword').val();
        this.hide();
        var output = '';
        output+= '<form id="hiddenform" method="POST" action="redirectRegister.php">';
        output+= '<input type=hidden name="regusername" value="'+regusername+'"><input type=hidden name="regpassword" value="'+regpassword+'">';
        output+= '</form>';
        output+= '<h2>Register</h2>';
        output+= '<table class="login">';
        output+='<tr><td><b>To verify your identity you now need to login via the EVE-SSO.</b></td></tr>';        
        output+='<tr><td><center><a onClick="jUI.redirectRegister()" class="link"><img src="img/EVE_SSO_Login_Buttons_Small_White.png"></a></td></tr>';
        $('#registerbox').html(output);
        $('#registerbox').show();
        $('#regusername').focus();        
    },
    showRegisterInactive: function() {
        var output = '';
        output+= '<h2>Register</h2>';
        output+= '<h3><font color=lightgreen>registration successfull!</font></h3><b> Your account needs to be activated by an admin before you can <a class=link onClick="jLoginBox.showLogin()">login</a>.';
        $('#registerbox').html(output);
        $('#registerbox').show();
    },
    showRegisterSuccess: function() {
        var output = '';
        output+= '<h2>Register</h2>';
        output+= '<h3><font color=lightgreen>registration successfull!</font></h3><b> You can now <a class=link onClick="jLoginBox.showLogin()">login</a>!';
        $('#registerbox').html(output);
        $('#registerbox').show();
    },
    showAddSuccess: function() {
        var output = '';
        output+= '<h2>Add character</h2>';
        output+= '<h3><font color=lightgreen>character added successfully!</font></h3><b> Please <a class=link onClick="jLoginBox.showLogin()">login</a> again!';
        $('#registerbox').html(output);
        $('#registerbox').show();
    },
    showRegisterExists: function() {
        var output = '';
        output+= '<h2>Register</h2>';
        output+= '<h3><font color=red>username already exists!</font></h3><b> Please <a class=link onClick="jLoginBox.showRegister()">try again</a> with a different username.';
        $('#registerbox').html(output);
        $('#registerbox').show();
    },
    showRegisterFailed: function() {
        var output = '';
        output+= '<h2>Register</h2>';
        output+= '<h3><font color=red>registration failed! (EVE-API down?)</font></h3><b> Please <a class=link onClick="jLoginBox.showRegister()">try again</a> later.';
        $('#registerbox').html(output);
        $('#registerbox').show();
    },    
}

function Init(_state) {
    $( document ).ready(function() {
        try {
        CCPEVE.requestTrust('http://46.101.250.243/jsig3/');
         }
         catch(e){
        
         }
        var w= $(window).width()-20;
        var h= $(window).height()-160;            
        $("#topbar").css({width:w-10});
        if (_state==='addsuccess') {
            jLoginBox.showAddSuccess();
        }else
        if (_state==='regsuccess') {
            jLoginBox.showRegisterSuccess();
        }else
        if(_state==='regexists') {
            jLoginBox.showRegisterExists();
        }else
        if(_state==='regfail') {
            jLoginBox.showRegisterFailed();
        }else{
            jLoginBox.showLogin();
        }
        
        
    });
}

$(window).resize(function() {
    var w= $(window).width()-20;
    var h= $(window).height()-160;
    $("#topbar").css({width:w-10});
});