export default {
  async fetch(request, env) {
    if (request.method === "POST") {
      try {
        const {
          patientId,
          patientName,
          dateOfBirth,
          dateOfAdmission,
          gender,
          height,
          weight,
          bloodType,
          rhFactor,
          bmi
        } = await request.json();

        await env.DB.prepare(
          `INSERT INTO patients 
          (patientId, patientName, dateOfBirth, dateOfAdmission, gender, height, weight, bloodType, rhFactor, bmi)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`
        )
        .bind(
          patientId,
          patientName,
          dateOfBirth,
          dateOfAdmission,
          gender,
          height,
          weight,
          bloodType,
          rhFactor,
          bmi
        )
        .run();

        return new Response("✅ Patient inserted successfully", { status: 200 });

      } catch (err) {
        return new Response("❌ Error: " + err.message, { status: 500 });
      }
    }

    return new Response("Send POST with patient data", { status: 400 });
  }
}
