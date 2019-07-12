'use strict';

// ===========================================
// Load Modules
// ===========================================
const express = require("express");
const bodyParser = require("body-parser");
const ProcessReq = require('./server/processreq');
const db = require('./db/dbQueries');
const autoload = require('auto-load');
const graceful = require('node-graceful');
const path = require('path');
const AccessControl = require('express-ip-access-control');
const helmet = require('helmet');
const cors = require('cors');
const accesscontrolop = require('./common/accesscontrol');
const appconf = require('./common/config');
const fs = require("fs");
const morgan = require("morgan");
const logger = require("./common/logger");
const http = require('http');
const _ = require('lodash');
const request = require('request');
const uuidv3 = require('uuid/v3');


require('./btc/tracker');

// ----------------------------------------
// Define Express App
// ----------------------------------------

const app = express()
app.use(bodyParser.json());

// ----------------------------------------
// Load DB
// ----------------------------------------

const DB = new db();

// ----------------------------------------
// Security
// ----------------------------------------

app.use(AccessControl(accesscontrolop));
app.use(helmet())

// ----------------------------------------
// CORS
// ----------------------------------------

app.use(cors());

// ===========================================
// Load Log4js
// ===========================================
const logFolder = { logFolder: appconf.logging.path, logLevel: appconf.logging.level };
logger.setup(logFolder);
const log = logger.create('main');

// ===========================================
// Load morgan request logger
// dev => consise and colored output
// tiny => :method :url :status :res[content-length] - :response-time ms
// ===========================================
var accessLogStream = fs.createWriteStream(path.join(__dirname, appconf.logging.path, '/requests.log'), { flags: 'a' });
const format = ':remote-addr - :date[clf] - :method :url :status :res[content-length] - :response-time ms';
app.use(morgan(format, { stream: accessLogStream })); //flag combined will log everything
app.use(morgan(format));




// ----------------------------------------
// Routes
// ----------------------------------------

app.post('/address', async function (req, res) {
    try
    {
        const Processreq = new ProcessReq(req.body);
        Processreq.SanitizeRequest().then((result)=>{
            log.info(result)
            BTC(req.body.currency).then(val => {
                var price= (1/val) * req.body.amount;
                var res = price.toString().substr(0, 10);
                var truncated_price = parseFloat(res, 10);
                req.body.amount = truncated_price;
                DB.validate_api_call(req.body).then(()=>{
                    GenerateAddress();
                }).catch((error)=>{
                    res.send({error});
                });
            });
        }).catch((error)=>{
            res.send({error});
            res.end();
        });

        function GenerateAddress()
        {
            console.log("inside GA")
            Processreq.GenerateAddress().then((address)=>{
                var paymentId = uuidv3(address, uuidv3.DNS);
                paymentId=paymentId.replace(/-/g,"");
                DB.AddPaymentRecord(req.body,address,paymentId).then(()=>
                        {
                            console.log("payment added")
                            var amount= req.body.amount;
                            res.send({address,amount,paymentId,error:null});
                            res.end()
                        }).catch((error)=>{
                            log.error("Error adding Payment");
                            res.send({error});
                            res.end();
                        });
                        
            }).catch((error)=>{
                console.log(error)
                res.send({error});
                res.end();
            });

        }

        function BTC(currency) {

            return new Promise((resolve) => {
        
                // send a request to blockchain
                request('https://blockchain.info/de/ticker', (error, response, body) => {
        
                    // parse the json answer and get the current bitcoin value
                    const data = JSON.parse(body);
                    var value=data[currency].last;
                    resolve(value);
                });
            });
        }
    }
    catch(error)
    {
        res.send({error});
        res.end();
    }
});
// ----------------------------------------
// Error handling
// ----------------------------------------

//NOTE: Don't remove unused varibales its necessary to have
app.use(function (req, res, next) {
    var err = new Error('Not Found')
    err.status = 404
    next(err)
})

app.use(function (err, req, res, next) {
    console.log(err);
    res.status(err.status || 500).send();
})

// ----------------------------------------
// Start HTTP server
// ----------------------------------------

const PORT = appconf.port;
app.set('port', PORT)
var server = http.createServer(app)

server.listen(PORT)
server.on('error', (error) => {
    if (error.syscall !== 'listen') {
        throw error
    }

    // handle specific listen errors with friendly messages
    switch (error.code) {
        case 'EACCES':
            log.fatal('Listening on port ' + PORT + ' requires elevated privileges!')
            return process.exit(0)
        case 'EADDRINUSE':
            log.fatal('Port ' + PORT + ' is already in use!')
            return process.exit(0)
        default:
            throw error
    }
})

server.on('listening', () => {
    log.info("Data server started on port: " + PORT);
})

server.on('close', () => {
    log.info("Express server closed on port: " + PORT);
    process.exit();
});


// ----------------------------------------
// Global application error catch
// ----------------------------------------

process.on("uncaughtException", (err) => {
    log.fatal("uncaughtException: ", err);
})

process.on('unhandledRejection', error => {
    log.fatal('unhandledRejection', error);
});

// ----------------------------------------
// Graceful shutdown
// ----------------------------------------
graceful.on('exit', () => {
    log.info('- SHUTTING DOWN - Terminating active connections.')
    DB.disconnect();
    log.info("My Sql Successfully closed");
    process.exit();

})