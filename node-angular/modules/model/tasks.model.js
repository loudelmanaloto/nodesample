const conn = require('./db')

const Task = function(){
}

Task.getAll = result => {
   conn.query("SELECT * FROM tasks_tbl", (err, data)=>{
       if(err){
          console.log(`Error ${err}`)
          result(null, err)
          return
       }
       else{
        result(null, data)
       }
   })
}

module.exports = Task