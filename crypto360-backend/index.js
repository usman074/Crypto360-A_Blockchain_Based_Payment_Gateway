'use strict'
const express = require("express");
const http = require("http");
const AccessControl = require('express-ip-access-control');
const helmet = require('helmet');
const cors = require('cors');
const bodyParser = require("body-parser");
const graceful = require('node-graceful');
const path = require('path');
const fs = require("fs");
const appconf = require('./common/config');
const logger = require("./common/logger");
const morgan = require("morgan");
const accesscontrolop = require('./common/accesscontrol');
const ProcessReq = require('./server/processreq');
const db = require('./db/dbQueries');
const url = require("url");
const _ = require("lodash");
const uuidv3 = require('uuid/v3');
const emailRegex = require('email-regex');
var md5 = require('md5');
var randomstring = require('randomstring');
const config = require('./mailer_config');
const mailer = require('./misc/mailer');
const auth = require('./auth');



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
// JWT Authentication
// ----------------------------------------
function jwt_auth(auth_token)
{
    return new Promise(function(resolve,reject){
        auth.verify(auth_token).then((response)=>{
            resolve(response);
        }).catch((error)=>{
            reject(error);
        });
    });
}

// ----------------------------------------
// Routes
// ----------------------------------------

app.post('/login', function (req, res) {

    try
    {
        const Processreq = new ProcessReq(req.body);
        Processreq.SanatizeLoginRequest( (error,result)=>
        {
            if(error)
            {
                log.error(error);
                res.send({error});
                res.end();
            }
            else{
                ValidateCredentials();
            }
        }
        );
        function ValidateCredentials()
        {
            DB.GetMerchantLoginCredentials(req.body).then ((result)=>
            {

                res.send({response:result,error:null});
                res.end();
                
            }).catch((error)=>{
                var resendEmail = config.BASE_URL+'/resendEmail/'+req.body.email;
                if(error == "You need to verify your email first to access your account. Please check your email")
                {
                    res.send({error:error,resendLink:resendEmail});
                    res.end();
                }
                else{
                    res.send({error});
                    res.end();
                }
                
            });
        }
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
})

app.get('/dashboard',function(req, res){
    
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{

            var d = new Date(Date.now() - 2592000000).toLocaleDateString();
            var conf = (appconf.testnet) ? appconf.insight.testnet.confirmations : appconf.insight.livenet.confirmations;
            DB.GetRevenueDetails(response,conf,d).then((PaymentDetails)=>{
                res.send({response:PaymentDetails,error:null});
                res.end();
            }).catch((error)=>{
                res.send({error});
                res.end();
            });

        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error});
        res.end();
    }

});


app.get('/payments/all',function(req, res)
{
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            DB.GetAllPaymentDetails(response).then((PaymentDetails)=>{
                res.send({response:PaymentDetails,error:null});
                res.end();
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
})

app.get('/payments/paid',function(req,res)
{
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            var conf = (appconf.testnet) ? appconf.insight.testnet.confirmations : appconf.insight.livenet.confirmations;
            DB.GetPaidPaymentDetails(response,conf).then((PaymentDetails)=>{
                res.send({response:PaymentDetails,error:null})
                res.end();
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
            
        
       
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
})

app.get('/payments/unresolved',function(req,res)
{
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            var conf = (appconf.testnet) ? appconf.insight.testnet.confirmations : appconf.insight.livenet.confirmations;
            DB.GetUnresolvedPaymentDetails(response,conf).then((PaymentDetails)=>{
                res.send({response:PaymentDetails,error:null})
                res.end();
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
})

app.get('/settings/profile', function(req,res)
{
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            DB.GetMerchantProfile(response).then((ProfileDetails)=>{
                var i =0;
                res.send({response:ProfileDetails[i],error:null})
                res.end();
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
})

app.get('/settings/CoinSettings', function(req,res)
{
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            DB.GetMerchantXPUBKEY(response).then((xpubkey)=>{
                res.send({response:xpubkey,error:null})
                res.end();
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
});

app.post('/settings/CoinSettings/addXpub', function(req,res)
{
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            const Processreq = new ProcessReq(req.body);
            Processreq.SanatizeAddXpubRequest( (error,result)=>
            {
                if(error)
                {
                    log.error(error);
                    res.send({error});
                    res.end();
                }
                else{
                    AddXpubKey();
                }
            });
            function AddXpubKey()
            {
                DB.GetMerchantXPUBKEY(response).then((xpubkey)=>{
                    if(xpubkey == "No key found")
                    {
                        DB.AddXpubKey(req.body,response).then((result)=>{
                            res.send({response:result,error:null});
                            res.end();

                        }).catch((error)=>{
                            res.send({error})
                            res.end();
                        });
                    }
                    else
                    {
                        DB.UpdateXpubKey(req.body,response).then((result)=>{
                            res.send({response:result,error:null});
                            res.end();

                        }).catch((error)=>{
                            res.send({error})
                            res.end();
                        });
                    }
                }).catch((error)=>{
                    res.send({error});
                    res.end();
                });
            }
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
});

app.post('/settings/profile/update',function(req,res)
{
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            const Processreq = new ProcessReq(req.body);
            Processreq.SanatizeProfileUpdateRequest( (error,result)=>
            {
                if(error)
                {
                    log.error(error);
                    res.send({error});
                    res.end();
                }
                else{
                    updateProfile();
                }
            }
            );
            function updateProfile()
            {
                DB.UpdateMerchantProfile(req.body,response).then((ProfileUpdated)=>{
                    res.send({response:ProfileUpdated,error:null});
                    res.end();
                    
                }).catch((error)=>{
                    res.send({error});
                    res.end();
                });
            }
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
        

    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
})

app.post('/settings/security',function(req,res)
{
    try{
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            const Processreq = new ProcessReq(req.body);
            Processreq.SanatizePasswordUpdateRequest( (error,result)=>
            {
                if(error)
                {
                    log.error(error);
                    res.send({error});
                    res.end();
                }
                else{
                    updatePassword();
                }
            }
            );
            function updatePassword()
            {
                DB.GetMerchantOldPassword(response).then((pass)=>{
                    req.body.pass = pass;
                    DB.UpdateMerchantPassword(req.body,response).then((PasswordUpdated)=>{
                            res.send({response:PasswordUpdated,error:null});
                            res.end();
                }).catch((error)=>{
                    res.send({error});
                    res.end();
                });
                    
                }).catch((error)=>{
                    res.send({error});
                    res.end();
                });
            }
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
});

app.post('/signup',function(req,res)
{
    try{
        const Processreq = new ProcessReq(req.body);
        Processreq.SanatizeSignupRequest( (error,result)=>
        {
            if(error)
            {
                log.error(error);
                res.send({error});
                res.end();
            }
            else{
                var email =emailRegex({exact: true}).test(req.body.email);
                if(email == true)
                {
                    DB.GetMerchantEmail(req.body.email).then((email_res)=>
                    {
                        if(_.isEmpty(email_res))
                        {
                            var merchantId = uuidv3(req.body.email, uuidv3.DNS);
                            merchantId=merchantId.replace(/-/g,"");
                            signup(merchantId);
                        }
                        else{
                            res.send({error:"Email Already Registered"});
                            res.end();
                        }
                    }).catch((error)=>{
                        res.send({error});
                        res.end();
                    });
                    
                }
                else{
                    res.send({error:"Email is not correct"});
                    res.end();
                }
            }
        }
        );
        function signup(merchantId)
        {
            req.body.password = md5(req.body.password);
            req.body.merchantId = merchantId;
            var auth_token = randomstring.generate();
            req.body.authToken = auth_token;
            
            DB.AddMerchantProfile(req.body).then((added)=>{
                    
                        const html = 'Hi there, <br/> Thankyou for registering! <br/><br/> Please verify your email by clicking on the provided link <br/> link <a href ="'+config.BASE_URL+'/verify/'+merchantId+'/'+auth_token+'">'+config.BASE_URL+'/verify/'+merchantId+'/'+auth_token+'</a>';
                        mailer.sendEmail("admin@crypto360.com",req.body.email,"Please Verify Your Email Address",html).then((info)=>{
                            res.send({response:added+" Email for verification sent successfully",error:null});
                            res.end();
                        }).catch((err)=>{
                            res.send({response:"Merchant Added Successfully",error:err});
                            res.end();
                        });
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
            }
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
});

app.get("/verify/:merchantId/:AuthToken",(req, res)=>{
    DB.GetAccountVerificationFlagValue(req.params).then(()=>{
        DB.VerifyMerchantAccount(req.params).then((verified)=>{
            res.writeHead(302, {'Location': 'http://localhost/crypto360/index.php?account=verified'});
            res.end();
    
        }).catch((err)=>{
            res.send(err);
            res.end();
        });
    }).catch(()=>{
        res.writeHead(302, {'Location': 'http://localhost/crypto360/index.php?link=expired'});
        res.end();
    });
})

app.get("/resendEmail/:email",(req, res)=>{

    DB.GetResendEmailDetails(req.params).then(([merchantId,auth_token])=>{
        const html = 'Hi there, <br/> Thankyou for registering! <br/><br/> Please verify your email by clicking on the provided link <br/> link <a href ="'+config.BASE_URL+'/verify/'+merchantId+'/'+auth_token+'">'+config.BASE_URL+'/verify/'+merchantId+'/'+auth_token+'</a>';
        mailer.sendEmail("admin@crypto360.com",req.params.email,"Please Verify Your Email Address",html).then((info)=>{
        res.writeHead(302, {'Location': 'http://localhost/crypto360/index.php?resendEmail=sent'});
        res.end();
        }).catch((err)=>{
            res.send(err);
            res.end();
        });
    })
    
});

app.get("/api/apiadd",(req,res)=>{
    try
    {
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            var rand_string = randomstring.generate();
            var temp = response+rand_string;
            var api_key = uuidv3(temp, uuidv3.DNS);
            api_key=api_key.replace(/-/g,"");
            DB.GetApiKeyLimit(response).then((value)=>{
                if(value == 0)
                {
                    DB.AddApiKey(response,api_key).then((api_key_added)=>{
                        DB.UpdateApiKeyLimit(response,1).then(()=>{
                            res.send({response:api_key,error:null});
                            res.end();
                        }).catch((error)=>{
                            res.send({error});
                            res.end();
                        });
                    }).catch((error)=>{
                        res.send({error});
                        res.end();
                    });
                }
                else if(value == 1)
                {
                    res.send({error:"You have already generated Api key"});
                    res.end();
                }
            }).catch((error)=>{
                    res.send({error});
                    res.end();
                });
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error});
        res.end();
    }
    

    
});

app.get("/api",(req, res)=>{
    try
    {
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            DB.GetApiKey(response).then((api_key)=>{
                res.send({response:api_key,error:null});
                res.end();
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error});
        res.end();
    }

  
});

app.get("/api/delapi",(req,res)=>{
    try
    {
        var token = req.headers["x-access-token"];
        jwt_auth(token).then((response)=>{
            DB.DelApiKey(response).then((api_key_deleted)=>{
                DB.UpdateApiKeyLimit(response,0).then((updated)=>{
                    res.send({response:api_key_deleted,error:null});
                    res.end();
                }).catch((error)=>{
                    res.send({error});
                    res.end();
                });
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }).catch((error)=>{
            res.send({error:"Authentication Failed"});
        });
    }
    catch(error)
    {
        res.send({error});
        res.end();
    }

});

app.post("/forgetPass",(req,res)=>{
    try{
        const Processreq = new ProcessReq(req.body);
        Processreq.SanatizeForgetPassRequest( (error,result)=>
        {
            if(error)
            {
                log.error(error);
                res.send({error});
                res.end();
            }
            else{
                ForgetPassDetails();
            }
        });
        function ForgetPassDetails(){
            DB.ForgetPassDetails(req.body).then(([mId,pass])=>{

                var resetId = mId+pass;
                var resetId = uuidv3(resetId, uuidv3.DNS);
                resetId=resetId.replace(/-/g,"");
                const html = 'Hi there, <br/><br/> Please click on the provided link to reset your password <br/><a href ="localhost/crypto360/reset.php?mId='+mId+'&reset='+resetId+'">Click here to reset your password.</a><br><br>If you have not requested the password reset link then ignore this email.';
                mailer.sendEmail("admin@crypto360.com",req.body.email,"Please click on link to reset password",html).then((info)=>{
                    res.send({response:"Password reset link sent successfully. please check your email.",error:null});
                    res.end();
                })
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }
        
    }
    catch(error)
    {
        res.send({error});
        res.end();
    }
    
});

app.get("/resetPass/:merchantId/:resetId",(req, res)=>{
    
    DB.VerifyResetDetails(req.params).then((data)=>{
        var data = uuidv3(data, uuidv3.DNS);
        data=data.replace(/-/g,"");
        if(req.params.resetId==data)
        {
            res.send({response:"Link Validated",error:null})
            res.end();
        }
        else{
            res.send({error:"Invalid Link"});
            res.end();
        }
    }).catch((error)=>{
        res.send({error});
        res.end();
    });
});

app.post('/resetPass/:merchantId',function(req,res)
{
    try{
        console.log(req.body);
        const Processreq = new ProcessReq(req.body);
        Processreq.SanatizePasswordResetRequest( (error,result)=>
        {
            if(error)
            {
                log.error(error);
                res.send({error});
                res.end();
            }
            else{
                log.info(result)
                updatePassword();
            }
        }
        );
        function updatePassword()
        {
            DB.ResetMerchantPassword(req.body,req.params.merchantId).then((PasswordUpdated)=>{
                res.send({response:PasswordUpdated,error:null});
                res.end();
            }).catch((error)=>{
                res.send({error});
                res.end();
            });
        }
    }
    catch(error)
    {
        res.send({error})
        res.end();
    }
    
    
});

app.post("/contactUs",(req, res)=>{
    try
    {
        const Processreq = new ProcessReq(req.body);
        Processreq.SanatizeContactusRequest( (error,result)=>
        {
            if(error)
            {
                log.error(error);
                res.send({error});
                res.end();
            }
            else{
                sendMail();
            }
        });
        function sendMail()
        {
            var email =emailRegex({exact: true}).test(req.body.email);
            if(email == true)
            {
                mailer.sendEmail(req.body.email,"usman.iqbal6867@gmail.com",req.body.subject,req.body.message).then((info)=>{
                    console.log("emailed")
                res.send({response:"Email has been sent",error:null});
                res.end();
                }).catch((error)=>{
                    res.send({error});
                    res.end();
                });      
                        
            }
            else{
                res.send({error:"Invalid Email"});
                res.end();
            }
        }
        
    }
    catch(error)
    {
        res.send({error});
        res.end();
    }  
});

app.get("/uuid",async (req, res)=>{
    try{
        var x =emailRegex({exact: true}).test('sindresorhus@gmail.com');
        console.log("regex",x);
        var temp = uuidv3('Usman', uuidv3.DNS);
        temp=temp.replace(/-/g,"");
        console.log(temp);
        res.send(temp);
        res.end();
    }
    catch(e)
    {
        res.send("error")
        res.end();
    }
    

})


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
const PORT = "7270";
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
