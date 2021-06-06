const conn = require("./db")

const Student = function(student) {
  this.studnum_fld = student.studnum_fld
  this.fname_fld = student.fname_fld
  this.lname_fld = student.lname_fld
}

Student.getAll = result => {
  conn.query("SELECT * FROM students_tbl LIMIT 50", (err, res) => {
    if (err) {
      console.log(`error: ${err}`)
      result(null, err)
      return
    }
    result(null, res)
  })
}

Student.findById = (id, result) => {
    conn.query(`SELECT * FROM students_tbl WHERE studnum_fld = ${id}`, (err, res) => {
      if (err) {
        console.log(`error: ${err}`)
        result(err, null)
        return
      }
  
      if (res.length) {
        result(null, res[0]);
        return
      }
      // not found Student with the id
      result({ kind: "not_found" }, null)
    })
}




Student.create = (newStudent, result)=>{
    conn.query("INSERT INTO students_tbl SET ?", newStudent, (err, res)=>{
        if(err){
            console.log(`error: ${err}`)
            result(null, err)
            return
        }
        result(null, {id: res.insertId, ...newStudent})
    })
}

Student.updateById = (id, student, result) => {
 
  conn.query(
    "UPDATE students_tbl SET ? WHERE studnum_fld = ?",
    [student,id],
    (err, res) => {
      if (err) {
        console.log("error: ", err);
        result(null, err);
        return;
      }

      if (res.affectedRows == 0) {
        // not found Student with the id
        result({ kind: "not_found" }, null);
        return;
      }

      
      result(null, {...student, studnum_fld: id });
      
    }
  );
};

Student.remove = (id, result) => {
  conn.query("DELETE FROM students_tbl WHERE studnum_fld = ?", id, (err, res) => {
    if (err) {
      console.log("error: ", err);
      result(null, err);
      return;
    }

    if (res.affectedRows == 0) {
      // not found student with the id
      result({ kind: "not_found" }, null);
      return;
    }

    console.log("deleted student with id: ", id);
    result(null, res);
  });
};

Student.removeAll = result => {
  conn.query("DELETE FROM students", (err, res) => {
    if (err) {
      console.log("error: ", err);
      result(null, err);
      return;
    }

    console.log(`deleted ${res.affectedRows} students`);
    result(null, res);
  });
};


module.exports = Student
