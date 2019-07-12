/**
 * express-ip-access-control module options
 * @ https://www.npmjs.com/package/express-ip-access-control
*/

module.exports = {
    mode: 'deny',
    denys: [],
    allows: ['127.0.0.1'],
    forceConnectionAddress: false,
    log: function (clientIp, access) {
        if (!access)
            console.log(clientIp + (' denied.'));
    },
    statusCode: 401,
    redirectTo: '',
    message: 'Access Denied'
};