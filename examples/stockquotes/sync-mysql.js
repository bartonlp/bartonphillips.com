// nodejs
// Do a sync-mysql request

const MySql = require('sync-mysql');

const sql = new MySql({
  host: 'localhost',
  user: 'barton',
  password: '7098653'
});

const result = sql.query("select * from stocks.stocks limit 1");
console.log(result);
