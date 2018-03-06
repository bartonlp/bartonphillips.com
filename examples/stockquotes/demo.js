// This DOES NOT RUN it is just an example of how to do error
// handeling.

let saveUser = async function(){
  let err, user;

  [err, user] = await to(UserModel.findById(1));
  if(err) TE(err.message, true);

  user.name = “'this rocks';

  [err, user] = await to(user.save());
  if(err) TE(‘error on saving user’);

  return user;
}

//*** HELPER FUNCTIONS *** 
//*** npm install parse-error
pe = require('parse-error');
to = function(promise) {
  return promise
      .then(data => {
    return [null, data];
  }).catch(err =>
                [pe(err)]
          );
}
TE = function(err_message, log){ // TE stands for Throw Error
  if(log === true){
    console.error(err_message);
  }
  throw new Error(err_message);
};
