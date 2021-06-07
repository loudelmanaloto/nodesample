module.exports = app => {
    const task = require('../controller/tasks.controller')

    app.get('/tasks', task.findAll)
}