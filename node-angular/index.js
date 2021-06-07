const express = require('express')
const cors = require('cors')
const app = express()

app.use(express.json())
app.use(cors())
app.options('*', cors())

app.get('/', (req, res)=>{
    res.send('Api is working')
})

require('./modules/routes/tasks.route')(app)



const PORT = process.env.PORT || 3001

app.listen(PORT, ()=>console.log(`Listening on port ${PORT}.....`))
