const yaml = require('node-yaml-config');
let config = yaml.load('./config.yml');
const _ = require('lodash');
const $ = require('preconditions').singleton();

// borrowing insight terminology
let Constants = config.Constants = {
	TESTNET: 'testnet',
	LIVENET: 'livenet'
}

let PROVIDERS = config.insight
let network = config.testnet ? Constants.TESTNET : Constants.LIVENET;

$.shouldBeDefined(config.insight, 'Insight provider not defined')
$.checkState(PROVIDERS[network], 'Network [' + network +'] not supported by insight! Did you configure insight for this network?');
$.shouldBeDefined(PROVIDERS[network]['url'], 'Insight url(s) not defined for network ['+ network +']!')

// set sane defaults
_.defaultsDeep(config, {
	port: 7271,
	testnet: true,
	base: 'ETH',
	timeout: 10000,
	logging: {
		path: './logs',
		level: 'info'
	},
	insight: {
		livenet: {
			confirmations: 6
		},
		testnet: {
			confirmations: 1
		}
	}
});

module.exports = config