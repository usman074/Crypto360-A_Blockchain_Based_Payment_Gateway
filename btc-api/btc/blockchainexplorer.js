'use strict';

// ===========================================
// Load Modules
// ===========================================
var _ = require('lodash');
var $ = require('preconditions').singleton();
var Insight = require('./insight');
const config = require('../common/config');

const PROVIDERS = config.insight;
const Constants = config.Constants


function BlockChainExplorer() {
  var network = config.testnet ? Constants.TESTNET : Constants.LIVENET;
  var url = PROVIDERS[network]['url'];
  return new Insight({
        network: network,
        url: url
      });
  };

module.exports = BlockChainExplorer;


