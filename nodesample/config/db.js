const mysql = require("mysql");

const db = mysql.createConnection({
  host: "localhost",
  user: "root",
  database: "task_db",
  password: "",
});

db.connect((err) => {
  if (err) {
    throw err;
  } else {
    console.log("MySql Connected");
  }
});

module.exports = db;
