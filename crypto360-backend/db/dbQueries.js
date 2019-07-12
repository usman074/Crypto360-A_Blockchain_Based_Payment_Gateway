/**
 * dbQueries will perform CRUD operations
*/

// ===========================================
// Load Modules
// ===========================================
const log = require('../common/logger').create("db");
const mysql = require('mysql');
var md5 = require('md5');
const _ = require("lodash");
const auth = require('../auth');

var con = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "1234",
  database: "Crypto360"
});



function DB()
{
    //disconnecting mysql connection
    this.disconnect = function()
    {
        con.end(function(err) {
            // The connection is terminated now
            if (err)
            {
                log.error(err);
            }
            
            log.info("My Sql Successfully closed");
        });
    }

    //TODO: It will get the email and password of merchant
    this.GetMerchantLoginCredentials = function(request)
    {
            return new Promise(function(resolve, reject) {
                
                try{
                    var i = 0;
                    con.query("SELECT * FROM merchant_profile WHERE email ='"+request.email+"'", function (err, result) {
                        if (err) {
                            return reject(err);
                        }
                        else{
                            
                                if(_.isEmpty(result))
                                {
                                    reject("Email doesn't register with our system")
                                }
                                else{
                                    if(result[i].verified == 0)
                                    {
                                        reject("You need to verify your email first to access your account. Please check your email")
                                    }
                                    else{
                                        const email = result[i].email;
                                        const pass = result[i].password;
                                        request.password = md5(request.password);

                                        if(request.email == email && request.password == pass)
                                        {
                                            log.debug("Login Succesful");
                                            var jwt_token = auth.sign(result[i].merchant_id);
                                            result = JSON.stringify({"merchant_id":result[i].merchant_id,"firstName":result[i].firstName,"token":jwt_token});
                                            resolve(result);
                                        }
                                        else
                                        {
                                            log.error("Email or Password is incorrect");
                                            reject("Email or Password is incorrect");
                                        }
                                    }
                                }
                            
                        }
                        
                    });
                }
                catch(e){
                    log.error("Cannot get merchant details")
                    console.log(e);
                    return reject(e);
                }
        
                
                
            });
            
    }

     //TODO: It will get the records of all payments
     this.GetAllPaymentDetails = function(merchant_id)
     {
             return new Promise(function(resolve, reject) {
                 
                 try{
                     var i = 0;
                     con.query("SELECT * FROM Payment_Details WHERE merchant_id ='"+merchant_id+"'", function (err, result) {
                         if (err) {
                             log.error(err);
                            reject(err);
                         }
                         else{
                                if(_.isEmpty(result)){
                                    resolve("No Payment Records found")
                                }else{
                                    resolve(result);
                                }
                                
                             
                         }
                         
                     });
                 }
                 catch(e){
                     log.error("Cannot get payment details")
                     console.log(e);
                    reject(e);
                 }
         
                 
                 
             });
             
     }

     //TODO: It will get the records of all paid payments
     this.GetPaidPaymentDetails = function(merchant_id,conf)
     {
             return new Promise(function(resolve, reject) {
                 
                 try{
                     var i = 0;
                     con.query("SELECT * FROM Payment_Details WHERE merchant_id ='"+merchant_id+"' AND confirmations >= '"+conf+"'", function (err, result) {
                         if (err) {
                             log.error(err);
                            reject(err);
                         }
                         else{
                                if(_.isEmpty(result)){
                                    resolve("No Paid Payment Records found")
                                }else{
                                    resolve(result);
                                }
                             
                         }
                         
                     });
                 }
                 catch(e){
                     log.error("Cannot get payment details")
                     console.log(e);
                    reject(e);
                 }
         
                 
                 
             });
             
     }

     //TODO: It will get the records of all unresolved payments
     this.GetUnresolvedPaymentDetails = function(merchant_id,conf)
     {
             return new Promise(function(resolve, reject) {
                 
                 try{
                     var i = 0;
                     con.query("SELECT * FROM Payment_Details WHERE merchant_id ='"+merchant_id+"' AND confirmations < '"+conf+"'", function (err, result) {
                         if (err) {
                             log.error(err);
                             return reject(err);
                         }
                         else{
                                if(_.isEmpty(result)){
                                    resolve("No Unresolved Payment Records found")
                                }else{
                                    resolve(result);
                                }
                             
                         }
                         
                     });
                 }
                 catch(e){
                     log.error("Cannot get payment details")
                     console.log(e);
                     return reject(e);
                 }
         
                 
                 
             });
             
     }


     //TODO: It will get the records of all payments
     this.GetMerchantProfile = function(merchant_id)
     {
             return new Promise(function(resolve, reject) {
                 
                 try{
                     con.query("SELECT firstName, lastName, organizationName, merchant_id, email FROM merchant_profile WHERE merchant_id ='"+merchant_id+"'", function (err, result) {
                         if (err) {
                            log.error(err);
                            reject(err);
                         }
                         else{
                                
                            resolve(result);
                                
                         }
                         
                     });
                 }
                 catch(e){
                    log.error("Cannot get merchant profile details")
                    reject(e);
                 }
         
                 
                 
             });
             
     }

     //TODO: It will get the XPUBKEY of merchant
    this.GetMerchantXPUBKEY = function(merchant_id)
    {
        return new Promise(function(resolve, reject) {
            try{
                var i =0;
                con.query("SELECT xpubkey FROM merchant_bitcoin_detail WHERE merchant_id ='"+merchant_id+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                        if(_.isEmpty(result))
                        {
                            resolve("No key found");
                        }
                        else{
                            const xpubkey = result[i].xpubkey;
                            resolve(xpubkey);
                        }
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot get xpubkey")
                reject(e);
            }
    
            
            
        });
    }

    //it will insert the record of newly registered merchant
    this.AddXpubKey = function(request,merchantId)
    {   
        return new Promise(function(resolve, reject) {
            try{
                con.query("INSERT INTO merchant_bitcoin_detail (merchant_id, Bitcoin, xpubkey) VALUES ('"+merchantId+"', '"+1+"', '"+request.xpubkey+"')", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                            log.debug("XpubKey added succesfully")
                            resolve("XpubKey Added Successfully.");
                        
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot add XpubKey")
                console.log(e);
                reject(e);
            }
            
            
        });

        
    }
    //TODO: It will update the index value of merchant
    this.UpdateXpubKey = function(request,merchantId)
    {
    return new Promise(function(resolve, reject) {
        try{
            con.query("UPDATE merchant_bitcoin_detail SET xpubkey = '"+request.xpubkey+"' WHERE merchant_id ='"+merchantId+"'", function (err, result) {
                if (err) {
                    reject(err);
                }
                else{
                        log.debug("XpubKey updated succesfully")
                        resolve("XpubKey Updated Successfully");
                    
                }
                
            });
        }
        catch(e){
            log.error("Cannot update XpubKey")
            reject(e);
        }

        
        
    });
    
    }
     

    //TODO: It will get the records of all payments
    this.GetMerchantOldPassword = function(merchant_id)
    {
            return new Promise(function(resolve, reject) {
                
                try{
                    var pass=null;
                    var i =0;
                    con.query("SELECT password FROM merchant_profile WHERE merchant_id ='"+merchant_id+"'", function (err, result) {
                        if (err) {
                            log.error(err);
                            reject(err);
                        }
                        else{
                                var data = [];
                                data = result;
                                pass=data[i].password
                                resolve(pass);                             
                            
                        }
                        
                    });
                }
                catch(e){
                    log.error("Cannot get old password")
                    console.log(e);
                    reject(e);
                }
        
                
                
            });
            
    }

    //TODO: It will update the password of merchant
    this.UpdateMerchantPassword = function(request,merchantId)
    {
        
        return new Promise(function(resolve, reject) {
            try{
                
                request.oldpassword = md5(request.oldpassword);

                request.newpassword = md5(request.newpassword);
                
                if(request.oldpassword == request.pass)
                {
                    con.query("UPDATE merchant_profile SET password = '"+request.newpassword+"' WHERE merchant_id ='"+merchantId+"'", function (err, result) {
                        if (err) {
                            reject(err);
                        }
                        else{
                                
                                log.debug("Password updated succesfully")
                                resolve("Password Updated Successfully");
                            
                        }
                        
                    });
                }
                else{
                    log.debug("Old Password mismatch")
                    reject("Old Password mismatch");
                }
            }
            catch(e){
                log.error("Cannot update password");
                return reject(e);
            }
    
            
            
        });
        
    }


    //it will get all registered email addresses
    this.GetMerchantEmail = function(email)
    {   
        return new Promise(function(resolve, reject) {
            try{

                con.query("SELECT email from merchant_profile WHERE email = '"+email+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                        resolve(result);
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot get email addresses")
                console.log(e);
                reject(e);
            }
    
            
            
        });

        
    }


    //it will insert the record of newly registered merchant
    this.AddMerchantProfile = function(request)
    {   
        return new Promise(function(resolve, reject) {
            try{

                con.query("INSERT INTO merchant_profile (merchant_id, firstName, lastName, email, password, organizationName, authorization_token) VALUES ('"+request.merchantId+"', '"+request.firstname+"', '"+request.lastname+"', '"+request.email+"', '"+request.password+"', '"+request.organizationName+"', '"+request.authToken+"')", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                            log.debug("Merchant Details added succesfully")
                            resolve("Merchant Added Successfully.");
                        
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot add Merchant Details")
                console.log(e);
                reject(e);
            }
    
            
            
        });

        
    }
 //TODO: It will update the index value of merchant
 this.UpdateMerchantProfile = function(request,merchantId)
 {
     return new Promise(function(resolve, reject) {
         try{
             con.query("UPDATE merchant_profile SET firstName = '"+request.firstname+"', lastName = '"+request.lastname+"', organizationName = '"+request.orgName+"' WHERE merchant_id ='"+merchantId+"'", function (err, result) {
                 if (err) {
                     reject(err);
                 }
                 else{
                         log.debug("Profile updated succesfully")
                         resolve("Profile Updated Successfully");
                     
                 }
                 
             });
         }
         catch(e){
             log.error("Cannot update profile")
             reject(e);
         }
 
         
         
     });
     
 }
    //TODO: It will verify merchant account
    this.VerifyMerchantAccount = function(request)
    {
        return new Promise(function(resolve, reject) {
            try{
                con.query("UPDATE merchant_profile SET verified = '1' WHERE merchant_id ='"+request.merchantId+"' AND authorization_token = '"+request.AuthToken+"'", function (err, result) {
                    if (err) {
                        return reject(err);
                    }
                    else{
                            resolve("Merchant verified Successfully.");
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot verify profile")
                console.log(e);
                return reject(e);
            }
    
            
            
        });
        
    }

    //TODO: It will get the records of all payments
    this.GetResendEmailDetails = function(request)
    {
            return new Promise(function(resolve, reject) {
                
                try{
                    var i =0;
                    con.query("SELECT merchant_id, authorization_token FROM merchant_profile WHERE email ='"+request.email+"'", function (err, result) {
                        if (err) {
                            log.error(err);
                            return reject(err);
                        }
                        else{
                                if(_.isEmpty(result)){
                                    reject("Invalid Email");
                                }else{
                                    var data = [];
                                    data = result;
                                    var merchantId=data[i].merchant_id;
                                    var auth_token = data[i].authorization_token;
                                    resolve([merchantId,auth_token]);
                                }
                                
                            
                        }
                        
                    });
                }
                catch(e){
                    log.error("Error sending verification email")
                    console.log(e);
                    return reject(e);
                }
        
                
                
            });
            
    }

     //it will insert the api key of registered merchant
     this.AddApiKey = function(merchantId,api_key)
     {   
         return new Promise(function(resolve, reject) {
             try{
 
                 con.query("INSERT INTO Api_Keys (merchant_id, api_key) VALUES ('"+merchantId+"', '"+api_key+"')", function (err, result) {
                     if (err) {
                        reject(err);
                     }
                     else{
                            log.debug("Api Key added succesfully")
                            resolve("Api Key generated Successfully.");
                         
                     }
                     
                 });
             }
             catch(e){
                log.error("Cannot add Api key")
                console.log(e);
                reject(e);
             }
     
             
             
         });
 
         
     }

    //it will get all api key of registered merchant
    this.GetApiKey = function(merchantId)
    {   
        return new Promise(function(resolve, reject) {
            try{
                var i=0;
                con.query("SELECT api_key from Api_Keys WHERE merchant_id = '"+merchantId+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                            if(_.isEmpty(result)){
                                resolve("You have not generated any Api key.");
                            }else{
                                
                                var key = [];
                                key = result;
                                key=key[i].api_key
                                resolve(key);
                            }
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot get Api key")
                console.log(e);
                reject(e);
            }
    
            
            
        });

        
    }

    //it will verify if api is already generated or not
    this.GetApiKeyLimit = function(merchantId)
    {   
        return new Promise(function(resolve, reject) {
            try{
                var i=0;
                con.query("SELECT no_of_api from merchant_profile WHERE merchant_id = '"+merchantId+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                                
                                var val = [];
                                val = result;
                                val=val[i].no_of_api+"";
                                resolve(val);
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot validate Api key is generated already or not")
                console.log(e);
                reject(e);
            }
    
            
            
        });

        
    }

    //TODO: It will update the no_of_api value
    this.UpdateApiKeyLimit = function(merchantId,limit)
    {
        return new Promise(function(resolve, reject) {
            try{
                con.query("UPDATE merchant_profile SET no_of_api = '"+limit+"' WHERE merchant_id ='"+merchantId+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                                log.debug("No Of Api updated succesfully")
                                resolve();
                        
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot update No of Api")
                console.log(e);
                reject(e);
            }
    
            
            
        });
        
    }

    //it will delete the api key of registered merchant
    this.DelApiKey = function(merchantId)
    {   
        return new Promise(function(resolve, reject) {
            try{

                con.query("DELETE FROM Api_Keys WHERE merchant_id = '"+merchantId+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                            if(result.affectedRows == 0){
                                reject("No Api key to be deleted.");
                            }else{
                                
                                log.debug("Api Key deleted succesfully")
                                resolve("Api Key deleted Successfully.");
                            }
                        
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot del Api key")
                reject(e);
            }
    
            
            
        });

        
    }

    

       //TODO: It will get the email and password of merchant to make a reset password link
       this.ForgetPassDetails = function(request)
       {
               return new Promise(function(resolve, reject) {
                   
                   try{
                       var i = 0;
                       con.query("SELECT * FROM merchant_profile WHERE email ='"+request.email+"'", function (err, result) {
                           if (err) {
                               return reject(err);
                           }
                           else{
                                   if(_.isEmpty(result))
                                   {
                                       reject("Email doesn't register with our system")
                                   }
                                   else{
                                           const mId = result[i].merchant_id;
                                           const pass = result[i].password;
                                           resolve([mId,pass]);
                                        }
                                       
                                   }
                               
                           });
                   }
                   catch(e){
                       log.error("Cannot get merchant details")
                       console.log(e);
                       return reject(e);
                   }
           
                   
                   
               });
               
       }

    //TODO: It will get the merchant_id and password of merchant to verify password reset link.
    this.VerifyResetDetails = function(request)
    {
            return new Promise(function(resolve, reject) {
                
                try{
                    var i = 0;
                    con.query("SELECT * FROM merchant_profile WHERE merchant_id ='"+request.merchantId+"'", function (err, result) {
                        if (err) {
                            return reject(err);
                        }
                        else{
                                if(_.isEmpty(result))
                                {
                                    reject("Invalid Link")
                                }
                                else{
                                        const mId = result[i].merchant_id;
                                        const pass = result[i].password;
                                        var temp = mId+pass;
                                        resolve(temp);
                                     }
                                    
                                }
                            
                        });
                }
                catch(e){
                    log.error("Cannot get merchant details")
                    console.log(e);
                    return reject(e);
                }
        
                
                
            });
            
    }

    //TODO: It will reset the password of merchant
    this.ResetMerchantPassword = function(request,merchantId)
    {
        
        return new Promise(function(resolve, reject) {
            try{

                request.newpassword = md5(request.newpassword);
                
                con.query("UPDATE merchant_profile SET password = '"+request.newpassword+"' WHERE merchant_id ='"+merchantId+"'", function (err, result) {
                    if (err) {
                        return reject(err);
                    }
                    else{                                
                            log.debug("Password updated succesfully")
                            resolve("Password Updated Successfully");                
                    }
                        
                });
                
            }
            catch(e){
                log.error("Cannot update password")
                console.log(e);
                return reject(e);
            }
    
            
            
        });
        
    }

     //TODO: It will get the records of all paid payments (last 30 days)
     this.GetRevenueDetails = function(merchant_id,conf,d)
     {
             return new Promise(function(resolve, reject) {
                 
                 try{
                     var i = 0;
                     con.query("SELECT amount, Date FROM Payment_Details WHERE merchant_id ='"+merchant_id+"' AND confirmations >= '"+conf+"' AND Date >= '"+d+"'", function (err, result) {
                         if (err) {
                             log.error(err);
                             reject(err);
                         }
                         else{
                                if(_.isEmpty(result)){
                                    resolve("No Payment Records found")
                                }else{
                                    resolve(result);
                                }
                             
                         }
                         
                     });
                 }
                 catch(e){
                     log.error("Cannot get payment details")
                     console.log(e);
                     reject(e);
                 }
         
                 
                 
             });
             
     }

     //TODO: It will get account verification flag value
    this.GetAccountVerificationFlagValue = function(request)
    {
            return new Promise(function(resolve, reject) {
                
                try{
                    var i = 0;
                    con.query("SELECT * FROM merchant_profile WHERE merchant_id ='"+request.merchantId+"'", function (err, result) {
                        if (err) {
                            return reject(err);
                        }
                        else
                        {
                            
                            if(result[i].verified == 1)
                            {
                                reject("Link is expired or Already used for verification");
                            }
                            else
                            {
                                resolve("unverified");
                            }
                        }
                        
                    });
                }
                catch(e){
                    log.error("Cannot get merchant details")
                    console.log(e);
                    return reject(e);
                }
        
                
                
            });
            
    }


}
    
    


module.exports = DB;