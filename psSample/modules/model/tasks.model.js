const conn = require('../config/db')

class Task{
    constructor(){

    }

    getTask = result => {
        conn.query("SELECT * FROM tasks_tbl", (err, res)=>{
            if(err){ result(null, err); return;}
            // if(res.length === 0){ result(null, {kind: "no_data_found"}); return; }
            result(null, res)
        })
    }

}

module.exports = Task