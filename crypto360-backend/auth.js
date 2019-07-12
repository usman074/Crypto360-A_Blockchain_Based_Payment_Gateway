var jwt = require('jsonwebtoken');
var secret='secretSignatutre'
var sign=function(merchant_id){
    var token = jwt.sign({exp: Math.floor(Date.now() / 1000) + (3600), merchant_id: merchant_id },secret );
    return token;
}
var verify=function(token){
  return new Promise(function(resolve,reject){
    if (!token) {
      reject("Authentication Failed");
    }
    else{
      jwt.verify(token, secret, function(error, res) {
              if (error)
              {
                reject(error);
              }
              else{
                resolve(res.merchant_id)
              }
      });
    }
  });
}

module.exports = {
   sign: sign,
   verify:verify,
};