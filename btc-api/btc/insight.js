'use strict';

// ===========================================
// Load Modules
// ===========================================
var _ = require('lodash');
var async = require('async');
var $ = require('preconditions').singleton();
const log = require("../common/logger.js").create('insight');
var requestList = require('../btc/request-list');

const INSIGHT_REQUEST_POOL_SIZE = 100;


function Insight(opts) {
  $.checkArgument(opts);
  $.checkArgument(opts.url);

  this.apiPrefix = _.isUndefined(opts.apiPrefix)? '/api' : opts.apiPrefix;
  this.hosts = opts.url;
  this.userAgent = opts.userAgent || 'bws';
  // queue that handles all the requests to the API
  this.requestQueue = async.queue(this._doRequest.bind(this), INSIGHT_REQUEST_POOL_SIZE);
}

var _parseErr = function(err, res) {
  if (err) {
    log.error('Insight error: ', err);
    return "Insight Error";
  }
  log.warn("Insight " + res.request.href + " Returned Status: " + res.statusCode);
  return "Error querying the blockchain";
};


Insight.prototype._doRequest = function(args, cb) {
  
  var opts = {
    hosts: this.hosts,
    headers: {
      'User-Agent': this.userAgent,
    }
  };
  var s  = JSON.stringify(args);

  var x = requestList(_.defaults(args, opts), cb);
};

Insight.prototype.getConnectionInfo = function() {
  return 'Insight ( btc/' + this.network + ') @ ' + this.hosts;
};

/**
 * Retrieve a list of unspent outputs associated with an address or set of addresses
 */
Insight.prototype.getUtxos = function(addresses, cb) {
  var self = this; 
  
  var url = this.url + this.apiPrefix + '/addrs/utxo';
  var args = {
    method: 'POST',
    path: this.apiPrefix + '/addrs/utxo',
    json: {
      addrs: _.uniq([].concat(addresses)).join(',')
    }
  };
  this.requestQueue.push(args, function(err, res, unspent) {
    if (err || res.statusCode !== 200) return cb(_parseErr(err, res));
    return cb(null, unspent);
  });
};

module.exports = Insight;
