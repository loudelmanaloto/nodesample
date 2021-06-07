const mysql = require('mysql')
const dbConfig = require('../config/db.config')

const conn = mysql.createConnection({
    host: dbConfig.HOST,
    user: dbConfig.USER,
    password: dbConfig.PASSWORD,
    database: dbConfig.DATABASE
})

module.exports = conn