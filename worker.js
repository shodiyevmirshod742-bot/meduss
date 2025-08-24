export default {
  async fetch(request, env) {
    // env.myp — это твоя база
    const { results } = await env.myp.prepare("SELECT * FROM patients").all();
    return new Response(JSON.stringify(results));
  }
}
