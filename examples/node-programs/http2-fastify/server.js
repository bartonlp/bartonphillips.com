const fastify = require('fastify')();

fastify.get('/', async (request, reply) => {
  return { hello: 'world' };
});

const start = async () => {
  try {
    await fastify.listen(3000, '0.0.0.0')
    console.log("listening on 3000");
  } catch (err) {
    console.log(err)
    process.exit(1)
  }
}
start()
