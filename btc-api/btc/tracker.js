'use strict'
/** 
 * It will get the unused addresses and find UTXO's aginst the addresses
*/
// ===========================================
// Load Modules
// ===========================================
const db = require('.././db/dbQueries');
const log = require("../common/logger").create('tracker');
const _ = require("lodash");
const BlockchainExplorer = require('../btc/blockchainexplorer');
const config = require('../common/config');
const explorer = BlockchainExplorer();
const mailer = require('.././misc/mailer');
const axios = require('axios');

// ===========================================
// Load DB
// ===========================================
const DB = new db();

let slidingFactor = 0; //increase recursive interval 
const slidingInterval = 15000; // 15 seconds
const slidingIntervalLimit = 600000; // 10 minutes
const pollLimit = 30000; // tracker interval 
const PROVIDERS = config.insight;
const Constants = config.Constants
const network = config.testnet ? Constants.TESTNET : Constants.LIVENET
const confirmations = PROVIDERS[network]['confirmations']
const RATE_DEADLINE = config.deadline * 60 * 60 * 1000

// wait for db to load before initiating tracker
setTimeout(() => {
    tracker();
}, 2000)


let processContribution = (item) => {
    DB.GetRecordByAddress(item.address).then((result)=>
    {
        
        if(!_.isEmpty(result))
        {
            let updated = false;
            var temp_result = []
            temp_result = result;
            console.log(result)
            if(item.amount >= temp_result[0].amount)
            {
                console.log(item.amount)
                console.log(result)
                if(temp_result[0].tx_timestamp == "null")
                {
                    updated = true;
                    temp_result[0].tx_timestamp = item.timestamp ? item.timestamp : Date.now()
                }
                if(temp_result[0].tx_id == "null")
                {
                    updated = true;
                    temp_result[0].tx_id = item.id ? item.id : null
                }
                if(item.confirmations >= confirmations)
                {
                    temp_result[0].Unused_Addresses = false;
                    temp_result[0].confirmations= item.confirmations;
                    updated=true;
                }
                else{
                    updated = false;
                }
            }
            if(updated)
            {
                     DB.UpdatePaymentRecord(temp_result[0]).then((result) =>{
                        log.debug('Record Updated');
                        axios.get(temp_result[0].callback_url+"&order_id="+temp_result[0].invoice_id).then(()=>{
                            const html = 'Hi there, <br/> Your payment '+temp_result[0].amount+' BTC aginst order '+temp_result[0].invoice_id+' has been received and confirmed successfully.<br>Thankyou!';
                            mailer.sendEmail("admin@crypto360.com",temp_result[0].user_email,"Your Payment has been Received",html).then((info)=>{
                                log.debug("Email to user has been sent.");
                            }).catch((err)=>{
                                log.error("Error trying to send email to the user for payment confirmation.",err);
                            });
                        })
                        .catch(error => {
                            log.error("Error trying to send callback to the merchant for payment confirmation.",error);
                        });
                    }).catch((error)=>{
                        log.error('Unexpected error occurred while trying to update contribution',error);
                    })
            }
            
        }
        else if(_.isEmpty(result))
        {
            log.error('Couldn\'t get record from db against %s:', item.address);
        }
    })

}


/**
* Fetch utxos for given addresses
*/
let fetchUtxos = (addresses) => {
    return new Promise((resolve, reject) => {
        try {
            explorer.getUtxos(addresses, function(err, utxos) {
            if (err) {
                log.error('Error trying to get utxos for addresses', addresses, err)
                reject();
            }
            utxos = _.map(utxos, function(utxo) {
                let u = _.pick(utxo, ['txid', 'ts', 'address', 'amount', 'confirmations']);
                let len = _.keys(u).length
                if (len < 4) {
                    log.warn('UTXO doesn\'t contain all required attributes', u)
                }

                if (isNaN(u.amount)) {
                    log.warn('Amount in UTXO data is NaN')
                }
                
                u.confirmations = u.confirmations || 0;
                u.id = u.txid;
                if (u.ts) {
                    let ts = new Date(u.ts)
                    u.timestamp = !isNaN(ts) ? u.ts * 1000 : Date.now()
                    delete u.ts
                }

                delete u.txid;
                return u;
            });
            resolve(utxos);
            });
        } catch (error) {
            log.error('Unexpected error trying to fetch utxos', error)
            reject();
        }
    });
}

/**
 * @dev function to go through the given addresses and fetch any deposit on success the deposit details are forwaded to [/txwatch]  
 * @param {array} addresses
 */
let trackContributions = async (addresses) => {
    // get utxos for limit number of addresses at a time
    let addrs = _.chunk(addresses, 10);

    _.forEach(addrs, (add) => {
        fetchUtxos(add).then((utxos) => {
            console.log(utxos)
            if (!_.isEmpty(utxos)) {
                log.debug('Got utxo result: ', utxos)
                _.forEach(utxos, async (item, n) => {
                    await processContribution(item)
                })
            } else {
                log.debug('No activity found on provided addresses', add)
            }
        }).catch((err) => {
            log.error('Couldn\'t get utxos from insight servers', err)
            //TODO: might want to do something about this
        })
    });
}

/**
 * @dev recursive function with sliding interval to put a watcher on  BTC addresses.
 * Every recursive call duration is increased by 15 seconds and max limit is 30 min.
 */
 let tracker = async () => {
    
    let time,
         
        unusedAddresses = await DB.GetUnusedPaymentaddresses();
        
    let length = unusedAddresses.length == 0 ? 1 : unusedAddresses.length;
    if (!_.isEmpty(unusedAddresses)) {
        // to preserve FIFO queue
        log.info('%d unused addresses found. Trying to find contributions.', length)
        trackContributions(unusedAddresses);
        slidingFactor = 0; //reset sliding counter
        time = pollLimit + (300 * length); // add 3ms delay per the number of addresses to be tracked

    } else {
        // increase 30 seconds in recursive calls if there is no btc address
        log.debug('No unused addresses found');
        let interval = (slidingFactor + slidingInterval);
        slidingFactor = (interval <= slidingIntervalLimit) ? interval : slidingIntervalLimit;
        time = (length * pollLimit) + slidingFactor;
    }

    log.debug('Will track again after %d seconds', time / 1000)

    //recursive call
    setTimeout(tracker, time);
};
