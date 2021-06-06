const Students = require('../models/student.model')

exports.create = (req, res) => {
    if (!req.body) {
      res.status(400).send({
        message: "Content can not be empty!"
      });
    }



const student = new Students({
    studnum_fld: req.body.studnum_fld,
    fname_fld: req.body.fname_fld,
    lname_fld: req.body.lname_fld
})

Students.create(student, (err, data)=>{
    if(err)
        res.status(500).send({
            message: err.message || "Some error occured while creating student"
        })
    else
        res.json(data)
})


}

exports.findAll = (req, res) => {
    Students.getAll((err, data) => {
      if (err)
        res.status(500).send({
          message:
            err.message || "Some error occurred while retrieving students."
        });
      else res.json(data);
    });
  };

exports.findOne = (req, res) => {
  Students.findById(req.params.studentId, (err, data) => {
    if (err) {
      if (err.kind === "not_found") {
        res.status(404).send({
          message: `Not found Student with id ${req.params.studentId}.`
        });
      } else {
        res.status(500).send({
          message: "Error retrieving Student with id " + req.params.studentId
        });
      }
    } else res.json(data);
  });
};

exports.update = (req, res) => {
    // Validate Request
    if (!req.body) {
      res.status(400).send({
        message: "Content can not be empty!"
      });
    }
  
    Students.updateById(
      req.params.studentId,
      new Students(req.body),
      (err, data) => {
        if (err) {
          if (err.kind === "not_found") {
            res.status(404).send({
              message: `Not found Student with id ${req.params.studentId}.`
            });
          } else {
            res.status(500).send({
              message: "Error updating Student with id " + req.params.studentId
            });
          }
        } else res.json(data);
      }
    );
  };
  
  // Delete a Student with the specified StudentId in the request
  exports.delete = (req, res) => {
    Students.remove(req.params.studentId, (err, data) => {
      if (err) {
        if (err.kind === "not_found") {
          res.status(404).send({
            message: `Not found Student with id ${req.params.studentId}.`
          });
        } else {
          res.status(500).send({
            message: "Could not delete Student with id " + req.params.studentId
          });
        }
      } else res.json({ message: `Student with id ${req.params.studentId} was deleted successfully!` });
    });
  };
  
  // Delete all Students from the database.
  exports.deleteAll = (req, res) => {
    Students.removeAll((err, data) => {
      if (err)
        res.status(500).send({
          message:
            err.message || "Some error occurred while removing all students."
        });
      else res.send({ message: `All Students were deleted successfully!` });
    });
  };

