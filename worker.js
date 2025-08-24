export default {
  async fetch(request, env) {
    const db = env.MYDB;

    if (request.method === "POST") {
      const data = await request.json();

      // вставка пациента
      await db.prepare(
        `INSERT INTO patients 
         (patientId, patientName, dateOfBirth, dateOfAdmission, gender, height, weight, bloodType, rhFactor, bmi) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`
      )
      .bind(
        data.patientId,
        data.patientName,
        data.dateOfBirth,
        data.dateOfAdmission,
        data.gender,
        data.height,
        data.weight,
        data.bloodType,
        data.rhFactor,
        data.bmi
      )
      .run();

      return new Response(JSON.stringify({ status: "ok" }), {
        headers: { "Content-Type": "application/json" },
      });
    }

    return new Response("Method not allowed", { status: 405 });
  },
};