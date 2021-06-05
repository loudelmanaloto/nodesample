
const http = require('http');

const server = http.createServer((req, res)=>{
    if(req.url === '/'){
        res.write('Hello World aa');
        res.end();
    }

    if(req.url === '/api/course'){
        res.write(JSON.stringify([1,2,3,4]));
        res.end();
    }

    if(req.url === '/api/course'){
        res.write(JSON.stringify([1,2,3,4]));
        res.end();
    }

    if(req.url === '/api/course'){
        res.write(JSON.stringify([1,2,3,4]));
        res.end();
    }

    if(req.url === '/api/course'){
        res.write(JSON.stringify([1,2,3,4]));
        res.end();
    }

});


server.listen(3001);

console.log('Listening on port 3001....');
