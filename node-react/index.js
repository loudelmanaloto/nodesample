const express = require('express')
const cors = require('cors')
const app = express()

app.use(cors())
app.use(express.json())
app.options('*', cors())

app.get('/', (req, res)=>{
    res.send('Api is working fine')
})


require('./modules/routes/student.routes')(app)

PORT = process.env.PORT || 3001

app.listen(PORT, ()=>console.log(`Listening to port ${PORT}....`))