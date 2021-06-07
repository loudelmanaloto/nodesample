const Tasks = require('../model/tasks.model')
const task = new Tasks()


exports.getAllTasks = async (req, res) => {
    task.getTask((err, data)=>{
        if(err) res.status(500).json({ message: err.message || "An Error occured while retrieving tasks" })
        else if(data.length === 0) res.status(404).json({ message: "No data found" })
        else res.json(data)
    })
}