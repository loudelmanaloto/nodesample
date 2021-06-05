const express = require("express");
const app = express();
const db = require("./config/db");

app.use(express.json());


app.get("/", (req, res) => {
  res.send("Hello World");
});

app.get("/tasks", async (req, res) => {
  
  let sql = "SELECT * FROM tasks_tbl";
  const query = await db.query(sql, (err, result) => {
    if (err) {
      throw err;
    }
    res.json(result);
  });
});

app.get("/tasks/:id", async (req, res) => {
  let sql = `SELECT * FROM tasks_tbl WHERE id='${req.params.id}'`;
  const query = await db.query(sql, (err, result) => {
    if (err) {
      throw err;
    }
    res.json(result);
  });
});

app.post("/tasks", async (req, res) => {
  let body = req.body;
  let sql = "INSERT INTO tasks_tbl SET ?";
  const query = await db.query(sql, body, (err, result) => {
    if (err) throw err;
    body.id = result.insertId;
    res.json(body);
  });

});

app.delete("/tasks/:id", async (req, res) => {

  let sql = `DELETE FROM tasks_tbl WHERE id='${req.params.id}'`;
  const query = await db.query(sql, (err, result) => {
    if (err) {
      throw err;
    }
    res.json(result.insertId);
  });
});

app.put("/tasks/:id", async (req, res) => {


  let body = req.body[0];
  reminder = !body.reminder?1:0;
 
  let sql = `UPDATE tasks_tbl SET reminder=${reminder} WHERE id='${
    req.params.id
  }'`;

  const query = await db.query(sql, (err, result) => {
    if (err) throw err;
    
    body.reminder = reminder;
    res.json(body);
  });

});

const port = process.env.PORT || 3001;

app.listen(port, () => console.log(`Listening on port ${port}...`));
