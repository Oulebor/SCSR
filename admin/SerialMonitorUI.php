<?php
/**
 * Created by PhpStorm.
 * User: Ethan
 * Date: 3/5/2018
 * Time: 6:50 PM
 */

namespace admin {

    include_once "IAdminUI.php";
    include_once "gpio/GPIO.php";
    include_once "gpio/SerialPort.php";

    use admin\gpio\PinInterface;
    use admin\editor\ShowTables;
    use admin\editor\TableLinks;
    use admin\gpio\GPIO;
    use admin\gpio\SerialPort;

    error_reporting(E_ALL | E_STRICT);
    ini_set("display_errors", 1);

    class SerialMonitorUI extends IAdminUI {

        public function __construct() {
            parent::__construct();
            self::showUI();
        }

        public function showUI() {
            $header     = new Header();
            $footer     = new Footer();
            $header     = $header->returnUI();
            $footer     = $footer->returnUI();

            $ShowTables = new ShowTables();
            $TableLinks = new TableLinks();
            $AdminPanel = new AdminPanelUI();
            $sideNav    = $AdminPanel->returnUI($this->User, $TableLinks, $ShowTables);

            echo <<<HTML

            $header
            
                $sideNav
                
                <div class="main">
                
                    <div class="e-row">
                        <div class="e-col-12 e-col-m-12">
                            
                            <div id="terminal">
                                <div class="terminal-logo">
        <pre>
     _________                __               __
  ___\_    __/__  __________ |__| ____ _____  |  |
_/ __ \|  |/ __ \/  __/     \|  |/    \\__   \ |  |
\  ___/|  |  ___/|  |/  Y Y  \  |   |  \/ __ \|  |____
 \_____|__|\_____|__|\_|__|__/__|___|__(______|______/
        </pre>
                                </div>
                                <div id="log"></div>
                                <section class="flexbox" id="command">
                                    <div id="directory" class="normal"></div> &nbsp;
                                    <div id="cursor"    class="normal">&gt;</div> &nbsp;
                                    <div class="stretch" id="prompt">
                                        <label for="msg"></label>
                                        <input type="text" id="msg" onkeypress="onkey(event)" autofocus>
                                    </div>
                                </section>
                            </div>
                            
                        </div>
                    </div>
                    
                </div>
                <script type="text/javascript">
                var socket;
        
                function init() {
                    var host = "ws://192.168.0.157:1337";
                    try {
                        socket = new WebSocket(host);
                        log('Status ' + socket.readyState + ': Upgrading to WebSocket...');
                        
                        socket.onopen    = function() {
                            log("Status " + this.readyState + ': WebSocket Connected');
                        };
                        
                        socket.onmessage = function(msg) {
                            var message = JSON.parse(msg.data);
                            var data = buffer2str(message.data);
                            
                            log("Received: " + data);
                        };
                        
                        socket.onclose   = function() {
                            log("Disconnected - status "+this.readyState);
                        };
                    }
                    catch(ex){
                        log(ex);
                    }
                    $("msg").focus();
                }
        
                function send(){
                    var txt, msg;
                    txt = $("msg");
                    msg = txt.value;
                    if(!msg) {
                        alert("Message can not be empty");
                        return;
                    }
                    txt.value="";
                    txt.focus();
                    try {
                        socket.send(msg);
                        log('Sent: '+msg);
                    } catch(ex) {
                        log(ex);
                    }
                }
                function quit(){
                    if (socket !== null) {
                        log("Goodbye!");
                        socket.close();
                        socket=null;
                    }
                }
                function reconnect() {
                    quit();
                    init();
                }
        
                // Utilities
                function $(id){
                    return document.getElementById(id);
                }
                function log(msg){
                    $("log").innerHTML+="<br>"+msg;
                }
                function onkey(event){
                    if(event.keyCode === 13){
                        send();
                    }
                }
                
                function buffer2str(buf) {
                    return String.fromCharCode.apply(null, new Uint8Array(buf));
                }
                
                window.addEventListener("load", init, false);
                
            </script>
            
            <!--<script src="buttonGPIO.js"></script>
            <script src="MotorController.js"></script>-->
            <!--<script src="ReadMessages.js"></script>-->
            $footer

HTML;
            unset($header);
            unset($footer);

            unset($ShowTables);
            unset($TableLinks);
            unset($AdminPanel);
            unset($sideNav);

        }

    }
    $worker = new SerialMonitorUI();
}