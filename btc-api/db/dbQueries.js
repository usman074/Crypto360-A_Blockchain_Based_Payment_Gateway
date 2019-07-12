/**
 * dbQueries will perform CRUD operations
*/

// ===========================================
// Load Modules
// ===========================================
const log = require('../common/logger').create("db");
const mysql = require('mysql');

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
    //TODO: It will get the index value of merchant
    this.GetMerchantIndexVal = function(merchant_id)
    {
            return new Promise(function(resolve, reject) {
                try{
                    var i = 0;
                    con.query("SELECT indexValue FROM merchant_bitcoin_detail WHERE merchant_id ='"+merchant_id+"'", function (err, result) {
                        if (err) {
                            reject(err);
                        }
                        else{
                            const ind = result[i].indexValue;
                            log.debug("Merchant index is ", ind)
                            resolve(ind);
                        }
                        
                    });
                }
                catch(e){
                    log.error("Cannot get merchant index val")
                    reject(e);
                }
        
                
                
            });
            
    }

     //TODO: It will validate the api call
     this.validate_api_call = function(request)
     {
             return new Promise(function(resolve, reject) {
                 try{
                     
                     var i = 0;
                     con.query("SELECT api_key FROM Api_Keys WHERE merchant_id ='"+request.merchantId+"'", function (err, result) {
                         if (err) {
                             reject(err);
                         }
                         else{
                             const apiKey = result[i].api_key;
                             if(request.api_key == apiKey)
                             {
                                 resolve();
                             }
                             else{
                                 reject("Api Key or Merchant Id is Invalid");
                             }
                         }
                         
                     });
                 }
                 catch(e){
                     log.error("Cannot get merchant api key")
                     reject(e);
                 }
         
                 
                 
             });
             
     }

    //TODO: It will add the new payment record
    this.AddPaymentRecord = function(request,address,paymentId)
    {
        return new Promise(function(resolve, reject) {
            try{
                var d = new Date(Date.now());
                con.query("INSERT INTO Payment_Details (payment_id, merchant_id, address, tx_timestamp, invoice_id, amount, tx_id, Unused_Addresses, confirmations, Date, Coin, user_email, callback_url) VALUES ('"+paymentId+"', '"+request.merchantId+"', '"+address+"', '"+null+"', '"+request.invoiceId+"', '"+request.amount+"', '"+null+"', '"+true+"', '"+0+"', '"+d.toLocaleDateString()+"','BTC','"+request.user_email+"', '"+request.callback_url+"')", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                        log.info("Add payment record successfully")
                        resolve();
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot add payment record")
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
                        const xpubkey = result[i].xpubkey;
                        log.debug("Merchant XPUBKEY is ",xpubkey)
                        resolve(xpubkey);
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot get xpubkey")
                reject(e);
            }
    
            
            
        });
    }

    //TODO: It will update the index value of merchant
    this.UpdateMerchantIndex = function(ind,merchant_id)
    {
        return new Promise(function(resolve, reject) {
            try{
                con.query("UPDATE merchant_bitcoin_detail SET indexValue = '"+ind+"' WHERE merchant_id ='"+merchant_id+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                        log.debug("Index updated succesfully")
                        resolve();
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot get xpubkey")
                reject(e);
            }
    
            
            
        });
        
    }

    //TODO: It will get the unused addresses
    this.GetUnusedPaymentaddresses= function () {

        return new Promise(function(resolve, reject) {
            try{
                var UnUsedAddList = new Array();
                con.query("SELECT * FROM Payment_Details WHERE Unused_Addresses ='true'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                        for(var i=0; i < result.length; i++)
                        {
                            UnUsedAddList[i] = result[i].address;
                        }
                        log.debug("Read unused addresses successfully")
                       resolve(UnUsedAddList);
                    }
                    
                });
            }
            catch (error) {
                log.error("Cannot get unused payment addresses")
                console.log(error)
                reject(error);
            }
    
            
            
        });
    }

    //TODO: It will get the payment record against to: address
    this.GetRecordByAddress = function(address,callback){

        return new Promise(function(resolve, reject) {
            try{

                con.query("SELECT * FROM Payment_Details WHERE address ='"+address+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                        resolve(result);
                    }
                    
                });
            }
            catch (error) {
                log.error("Cannot get merchant record")
                reject(error);
            }
    
            
            
        });
    }

    this.UpdatePaymentRecord = function(request,callback)
    {   
        return new Promise(function(resolve, reject) {
            try{

                con.query("UPDATE Payment_Details SET tx_timestamp = '"+request.tx_timestamp+"', tx_id = '"+request.tx_id+"', Unused_Addresses = '"+request.Unused_Addresses+"', confirmations = '"+request.confirmations+"' WHERE address ='"+request.address+"'", function (err, result) {
                    if (err) {
                        reject(err);
                    }
                    else{
                        log.debug("Payment Details updated succesfully")
                        resolve();
                    }
                    
                });
            }
            catch(e){
                log.error("Cannot update Payment Details")
                console.log(e);
                reject(e);
            }
    
            
            
        });

        
    }
}
    
    


module.exports = DB;