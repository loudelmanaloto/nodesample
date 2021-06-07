module.exports = app => {
    const tasks = require('../controller/tasks.controller')
    app.get('/tasks', tasks.getAllTasks)
}