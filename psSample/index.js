const express = require('express')
const app = express()

app.use(express.json())


require('./modules/routes/routes')(app)

const PORT = process.env.PORT || 3001

app.listen(PORT, ()=>console.log(`Listening on port ${PORT}`))