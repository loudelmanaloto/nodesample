const express = require('express');
const app = express();
const pool = require('./connection');

app.use(express.json());

app.get('/api/students', async(req, res)=>{
    try{
        const students = await pool.query("SELECT * FROM students_tbl");
        console.log(students);
    }
    catch(err){
        console.log(err.message);
    }
    
})


app.post('/api/students', async (req, res)=>{
    try{
      
      const { fname, lname } = req.body;
    ;
      const newStudent = await pool.query("INSERT INTO students (fname, lname) VALUES ($1, $2) RETURNING *", [fname, lname]);
      
        res.json(newStudent.rows);
    }
    catch(err){
        console.log(err.message);
    }
})


app.listen(5000, ()=>{
    console.log("Server is listening on port 5000....");
});