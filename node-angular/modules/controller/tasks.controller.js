const Tasks = require('../model/tasks.model')

exports.findAll = (req, res)=>{
    Tasks.getAll((err, data)=>{
       if(err){
           res.status(500).json({
               message: err.message || "Some error occured while getting tasks"
           })
       }
       else res.json(data)
    })
}