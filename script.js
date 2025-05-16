document.getElementById('launchButton').addEventListener('click', function() {
    // Obtener parámetros del formulario
    const angle = parseFloat(document.getElementById('angle').value);
    const velocity = parseFloat(document.getElementById('velocity').value);
    const airResistance = parseFloat(document.getElementById('airResistance').value);
    const mass = parseFloat(document.getElementById('mass').value);
    const density = parseFloat(document.getElementById('density').value);
    const dragCoefficient = parseFloat(document.getElementById('dragCoefficient').value);
    const crossSectionalArea = parseFloat(document.getElementById('crossSectionalArea').value);

    // Simular trayectoria y obtener resultados
    const results = simulateTrajectory(angle, velocity, airResistance, mass, density, dragCoefficient, crossSectionalArea);

    // Mostrar resultados en la interfaz
    document.getElementById('results').innerHTML = `
        Alcance: ${results.range.toFixed(2)} m<br>
        Altura máxima: ${results.maxHeight.toFixed(2)} m<br>
        Tiempo de vuelo: ${results.timeOfFlight.toFixed(2)} s
    `;

    // Enviar datos a PHP para guardar en MySQL
    const data = {
        angle,
        velocity,
        airResistance,
        mass,
        density,
        dragCoefficient,
        crossSectionalArea,
        range: results.range,
        max_height: results.maxHeight,
        time_of_flight: results.timeOfFlight
    };
    fetch('save_simulation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const downloadLink = document.getElementById('downloadReport');
            downloadLink.style.display = 'block';
            downloadLink.href = `generate_report.php?id=${data.id}`;
        }
    });
});

function simulateTrajectory(angle, velocity, airResistance, mass, density, dragCoefficient, crossSectionalArea) {
    const g = 9.81; // Gravedad (m/s^2)
    const rad = angle * Math.PI / 180.0;
    let vx = velocity * Math.cos(rad);
    let vy = velocity * Math.sin(rad);
    let x = 0.0;
    let y = 0.0;
    const dt = 0.01; // Incremento de tiempo más pequeño para mayor precisión
    let maxHeight = 0.0;
    let range = 0.0;
    let timeOfFlight = 0.0;

    // Configurar canvas
    const canvas = document.getElementById('trajectoryCanvas');
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Arrays para almacenar puntos de la trayectoria
    const points = [];
    points.push({ x: x, y: y });

    // Simulación con RK4
    for (let t = 0; t < 1000; t += dt) {
        const step = rk4Step(vx, vy, x, y, dt, mass, density, dragCoefficient, crossSectionalArea, airResistance);
        vx = step.vx;
        vy = step.vy;
        x = step.x;
        y = step.y;
        timeOfFlight = t;

        // Actualizar altura máxima
        if (y > maxHeight) maxHeight = y;

        // Detener si el proyectil toca el suelo
        if (y < 0.0) {
            range = x;
            break;
        }

        points.push({ x: x, y: y });
    }

    // Escalar trayectoria para el canvas
    const maxX = Math.max(...points.map(p => p.x));
    const maxY = Math.max(...points.map(p => p.y));
    const scaleX = canvas.width / (maxX * 1.1);
    const scaleY = canvas.height / (maxY * 1.1);

    // Dibujar trayectoria
    ctx.beginPath();
    ctx.moveTo(0, canvas.height);
    points.forEach(point => {
        ctx.lineTo(point.x * scaleX, canvas.height - point.y * scaleY);
    });
    ctx.strokeStyle = 'red';
    ctx.stroke();

    return { range, maxHeight, timeOfFlight };
}

function rk4Step(vx, vy, x, y, dt, mass, density, dragCoefficient, crossSectionalArea, airResistance) {
    const g = 9.81;
    let k1_vx, k1_vy, k1_x, k1_y;
    let k2_vx, k2_vy, k2_x, k2_y;
    let k3_vx, k3_vy, k3_x, k3_y;
    let k4_vx, k4_vy, k4_x, k4_y;

    // Paso 1
    derivatives(vx, vy, x, y, mass, density, dragCoefficient, crossSectionalArea, airResistance, (d) => {
        k1_vx = d.dvx_dt;
        k1_vy = d.dvy_dt;
        k1_x = d.dx_dt;
        k1_y = d.dy_dt;
    });

    // Paso 2
    derivatives(vx + 0.5 * dt * k1_vx, vy + 0.5 * dt * k1_vy, x + 0.5 * dt * k1_x, y + 0.5 * dt * k1_y, mass, density, dragCoefficient, crossSectionalArea, airResistance, (d) => {
        k2_vx = d.dvx_dt;
        k2_vy = d.dvy_dt;
        k2_x = d.dx_dt;
        k2_y = d.dy_dt;
    });

    // Paso 3
    derivatives(vx + 0.5 * dt * k2_vx, vy + 0.5 * dt * k2_vy, x + 0.5 * dt * k2_x, y + 0.5 * dt * k2_y, mass, density, dragCoefficient, crossSectionalArea, airResistance, (d) => {
        k3_vx = d.dvx_dt;
        k3_vy = d.dvy_dt;
        k3_x = d.dx_dt;
        k3_y = d.dy_dt;
    });

    // Paso 4
    derivatives(vx + dt * k3_vx, vy + dt * k3_vy, x + dt * k3_x, y + dt * k3_y, mass, density, dragCoefficient, crossSectionalArea, airResistance, (d) => {
        k4_vx = d.dvx_dt;
        k4_vy = d.dvy_dt;
        k4_x = d.dx_dt;
        k4_y = d.dy_dt;
    });

    // Actualizar variables
    vx += (dt / 6.0) * (k1_vx + 2.0 * k2_vx + 2.0 * k3_vx + k4_vx);
    vy += (dt / 6.0) * (k1_vy + 2.0 * k2_vy + 2.0 * k3_vy + k4_vy);
    x += (dt / 6.0) * (k1_x + 2.0 * k2_x + 2.0 * k3_x + k4_x);
    y += (dt / 6.0) * (k1_y + 2.0 * k2_y + 2.0 * k3_y + k4_y);

    return { vx, vy, x, y };
}

function derivatives(vx, vy, x, y, mass, density, dragCoefficient, crossSectionalArea, airResistance, callback) {
    const g = 9.81;
    const v = Math.sqrt(vx * vx + vy * vy);
    const dvx_dt = -0.5 * density * dragCoefficient * crossSectionalArea * v * vx / mass - airResistance * vx;
    const dvy_dt = -g - 0.5 * density * dragCoefficient * crossSectionalArea * v * vy / mass - airResistance * vy;
    const dx_dt = vx;
    const dy_dt = vy;

    callback({ dvx_dt, dvy_dt, dx_dt, dy_dt });
}
