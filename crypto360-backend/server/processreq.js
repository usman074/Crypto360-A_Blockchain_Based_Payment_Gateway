/**
 * It will do the processing on data pulled from database 
 */
'use strict'
// ===========================================
// Load Modules
// ===========================================

const db = require('.././db/dbQueries');
var log = require('.././common/logger').create('reqprocessor');
const _ = require('lodash');
// ===========================================
// Load DB
// ===========================================
const DB = new db();

var ProcessRequest = function(request){

        this.SanatizeLoginRequest = function(callback)
        {
                let data = _.pick(request, ['email', 'password']);
            
                let len = _.keys(data).length
                if (len !== 2) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback(500)
                }
                else
                {
                    if(_.isString(data.email) && _.isString(data.password))
                    {
                        callback(null,"Data validated");
                    }
                    else{
                        callback(500)
                    }
                }
        }

        this.SanatizeMerchanrID = function(callback)
        {
                let data = _.pick(request, ['merchantId']);
            
                let len = _.keys(data).length
                if (len !== 1) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback(500)
                }
                else
                {
                    if(_.isString(data.merchantId))
                    {
                        callback(null,"Data validated");
                    }
                    else{
                        callback(500)
                    }
                }
        }
        this.SanatizeAddXpubRequest = function(callback)
        {
                let data = _.pick(request, ['xpubkey']);
            
                let len = _.keys(data).length
                if (len !== 1) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback('Data doesn\'t contain all required attributes')
                }
                else
                {
                    if(_.isString(data.xpubkey))
                    {
                        callback(null,"Data validated");
                    }
                    else{
                        callback(500)
                    }
                }
        }
        
        this.SanatizeProfileUpdateRequest = function(callback)
        {
                let data = _.pick(request, ['firstname', 'lastname', 'orgName']);
            
                let len = _.keys(data).length
                if (len !== 3) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback('Data doesn\'t contain all required attributes')
                }
                else
                {
                    if(_.isString(data.firstname) && _.isString(data.lastname) && _.isString(data.orgName))
                    {
                        callback(null,"Data validated");
                    }
                    else{
                        callback(500)
                    }
                }
        }

        this.SanatizePasswordUpdateRequest = function(callback)
        {
                let data = _.pick(request, ['oldpassword','newpassword']);
            
                let len = _.keys(data).length
                if (len !== 2) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback('Data doesn\'t contain all required attributes')
                }
                else
                {
                    
                    if(_.isString(data.oldpassword) && _.isString(data.newpassword))
                    {
                        if(data.newpassword.length >= 8)
                        {
                            callback(null,"Data validated");
                        }
                        else{
                            callback("Password length must be 8 chracters");
                        }
                        
                        
                    }
                    else{
                        callback("Data is not in correct format")
                    }
                }
        }

        this.SanatizeSignupRequest = function(callback)
        {
                let data = _.pick(request, ['firstname','lastname','email', 'password','organizationName']);
            
                let len = _.keys(data).length
                if (len !== 5) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback('Data doesn\'t contain all required attributes')
                }
                else
                {
                    
                    if(_.isString(data.firstname) && _.isString(data.lastname) && _.isString(data.email) && _.isString(data.password) && _.isString(data.organizationName))
                    {
                        if(data.password.length < 8)
                        {
                            callback("Minimum password length must be 8 chracters");
                        }
                        else{
                            callback(null,"Data validated");
                        }
                        
                        
                        
                    }
                    else{
                        callback("Data format is not correct")
                    }
                }
        }

        this.SanatizeForgetPassRequest = function(callback)
        {
                let data = _.pick(request, ['email']);
            
                let len = _.keys(data).length
                if (len !== 1) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback(500)
                }
                else
                {
                    if(_.isString(data.email))
                    {
                        callback(null,"Data validated");
                    }
                    else{
                        callback(500)
                    }
                }
        }

        this.SanatizeContactusRequest = function(callback)
        {
                let data = _.pick(request, ['email', 'subject', 'message']);
            
                let len = _.keys(data).length
                if (len !== 3) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback('Data doesn\'t contain all required attributes')
                }
                else
                {
                    if(_.isString(data.email) && _.isString(data.subject) && _.isString(data.message))
                    {
                        callback(null,"Data validated");
                    }
                    else{
                        callback(500)
                    }
                }
        }

        this.SanatizePasswordResetRequest = function(callback)
        {
                let data = _.pick(request, ['newpassword','confirmpassword']);
            
                let len = _.keys(data).length
                if (len !== 2) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback(500)
                }
                else
                {
                    if(data.newpassword == data.confirmpassword)
                    {
                        if(_.isString(data.newpassword) && _.isString(data.confirmpassword) && data.newpassword.length >= 8)
                        {
                            
                            callback(null,"Data validated");
                            
                        }
                        else{
                            callback(500)
                        }
                    }
                    else{
                        callback("New password and Confirm Password fields did not match");
                    }
                }
        }

        this.SanitizeRequest = function(callback)
        {
            
            let data = _.pick(request, ['invoiceId', 'amount', 'merchantId']);
            
                let len = _.keys(data).length
                if (len !== 3) {
                    log.warn('Data doesn\'t contain all required attributes', data)
                    callback(500)
                }
                else
                {
                    if(_.isString(data.merchantId) && _.isString(data.invoiceId))
                    {
                        
                        if(typeof(data.amount) == "number")
                        {
                            callback(null,"Data validated");
                        }
                        else{
                            callback(500)
                        }
                    }
                    else{
                        callback(500)
                    }
                }
                
        }

        
    } 
module.exports = ProcessRequest

