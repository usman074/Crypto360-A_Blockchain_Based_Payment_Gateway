const yaml = require('node-yaml-config');
let config = yaml.load('./config.yml');
const _ = require('lodash');
const $ = require('preconditions').singleton();

// borrowing insight terminology
let Constants = config.Constants = {
	TESTNET: 'testnet',
	LIVENET: 'livenet'
}

let network = config.testnet ? Constants.TESTNET : Constants.LIVENET;

// set sane defaults
_.defaultsDeep(config, {
	testnet: true,
	port: 7270,
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