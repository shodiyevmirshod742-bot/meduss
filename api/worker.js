export default {
  async fetch(request, env) {
    const db = env.MYDB;

    const { results } = await db.prepare("SELECT * FROM patients").all();

    return new Response(JSON.stringify(results), {
      headers: { "Content-Type": "application/json" },
    });
  },
};