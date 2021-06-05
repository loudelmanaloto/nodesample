// const Pool = require('pg').Pool;
const mysql = require('mysql');

// const pool = new Pool({
//     user: "postgres",
//     password: 'kainaeomi09',
//     datebase: '',
//     host: 'localhost',
//     port: 5432
// });

const pool = mysql.createPool({
    user: "root",
    password: '',
    datebase: 'chedro_lamp',
    host: 'localhost',
    port: 8080
})

module.exports = pool;