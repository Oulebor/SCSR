var SerialPort = require("serialport");
var port = new SerialPort("/dev/ttyACM0", {
    baudRate : 115200
});



var WebSocketServer = require('ws').Server;
var SERVER_PORT = 1337;
var wss = new WebSocketServer({port: SERVER_PORT});
var connections = [];


wss.on('connection', function connection(client, req) {

    const ip = req.connection.remoteAddress;
    console.log("New Connection: " + ip);
    connections.push(client);

    client.on('message', function incoming(data) {
        console.log(ip + ": " + data);
        port.write(data);

    });

    client.on('close', function() {
        console.log("connection closed");
        var position = connections.indexOf(client);
        connections.splice(position,1);
    });

});



function broadcast(data) {
    //console.log('broadcast() is invoked');
    for(myConnection in connections) {
        connections[myConnection].send(JSON.stringify(data));
        //connections[myConnection].send(data);
    }
}




port.on('open', function() {
    console.log("Opening serial connection to arduino...");
    /*port.write('Connecting to serial port ', function(err) {
        if(err) {
            return console.log('Error on  write: ', err.message);
        }
        console.log('message written');
    });*/

});

port.on('error', function(err) {
    console.log('Error: ', err.message);
});


/*port.on('data', function(data){
    console.log('Serial Message: ' + data);
    broadcast(data);
});*/

// Read data that is available but keep the stream from entering "flowing mode"
port.on('readable', function () {
    var data = port.read();
    console.log('Serial Message: ' + data);
    broadcast(data);
});