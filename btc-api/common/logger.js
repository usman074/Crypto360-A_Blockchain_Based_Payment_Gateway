const log4js = require('log4js');

/**
 * Setup logging system.
 * @param  {Object} [options]
 * @param  {String} [options.logLevel] Minimum logging threshold (default: info).
 * @param  {String} [options.logFolder] Log folder to write logs to.
 */
exports.setup = function (options) {
  const logFolder = options.logFolder;
  const level = options.logLevel || 'info';

  const config = {
    appenders: {
      out: { type: 'console' },
      all: {
        type: 'file',
        filename: `${logFolder}/all.log`
      }
    },
    categories: {
      default: { appenders: ['out', 'all', 'all'], level }
    }
  };

  log4js.configure(config);
};

exports.create = category => {
  const logger = log4js.getLogger(category);

  // Allow for easy creation of sub-categories.
  logger.create = subCategory => {
    return exports.create(`${category}/${subCategory}`);
  };

  return logger;
};
