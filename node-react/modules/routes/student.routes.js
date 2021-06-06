module.exports = app =>{

    const students = require('../controller/student.controller')
    
    app.get("/students", students.findAll)

    app.get("/students/:studentId", students.findOne)

    app.post("/students", students.create)

    app.put("/students/:studentId", students.update)

    app.delete("/students/:studentId", students.delete)
}
