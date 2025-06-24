<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drones de la Fuerza Armada Venezolana - Visualización 2D y 3D</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background-color: #f0f0f0; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
        }
        #container { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 20px; 
            flex: 1; 
        }
        #droneSelector { 
            width: 100%; 
            padding: 10px; 
            font-size: 16px; 
        }
        #info { 
            width: 300px; 
            padding: 10px; 
            background: white; 
            border-radius: 5px; 
        }
        #canvas2D { 
            border: 1px solid #000; 
            width: 300px; 
            height: 300px; 
            background-color: #ddd; 
        }
        #canvas3D { 
            width: 600px; 
            height: 400px; 
            border: 1px solid #000; 
        }
        .drone-2d { 
            width: 100px; 
            height: 50px; 
            background-color: red; 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
        }
        h2 { 
            margin: 0 0 10px; 
        }
        p { 
            margin: 5px 0; 
        }
        footer { 
            text-align: center; 
            padding: 10px; 
            background-color: #333; 
            color: white; 
            width: 100%; 
            margin-top: 20px; 
        }
    </style>
</head>
<body>
    <h1>Drones de la Fuerza Armada Venezolana</h1>
    <form method="GET" action="">
        <select name="drone" id="droneSelector" onchange="this.form.submit()">
            <option value="mohajer2" <?php echo isset($_GET['drone']) && $_GET['drone'] == 'mohajer2' ? 'selected' : ''; ?>>Mohajer-2 (Arpía)</option>
            <option value="mohajer6" <?php echo isset($_GET['drone']) && $_GET['drone'] == 'mohajer6' ? 'selected' : ''; ?>>Mohajer-6</option>
            <option value="shahed" <?php echo isset($_GET['drone']) && $_GET['drone'] == 'shahed' ? 'selected' : ''; ?>>Shahed-131/136 (Zamora V-1)</option>
            <option value="ansu200" <?php echo isset($_GET['drone']) && $_GET['drone'] == 'ansu200' ? 'selected' : ''; ?>>ANSU-200</option>
        </select>
    </form>
    <div id="container">
        <div id="info">
            <h2 id="droneName"><?php echo isset($_GET['drone']) ? ucfirst($_GET['drone']) : 'Seleccione un dron'; ?></h2>
            <?php
            $drones = [
                'mohajer2' => [
                    'name' => 'Mohajer-2 (Arpía)',
                    'type' => 'Vigilancia y Ataque',
                    'length' => '3.6 m',
                    'wingspan' => '3.8 m',
                    'weight' => '85 kg',
                    'range' => '150 km',
                    'speed' => '200 km/h',
                    'capacity' => 'Antitanque y antipersonal',
                    'color' => '#808080'
                ],
                'mohajer6' => [
                    'name' => 'Mohajer-6',
                    'type' => 'ISR y Ataque',
                    'length' => '5.6 m',
                    'wingspan' => '10 m',
                    'weight' => '600 kg',
                    'range' => '2000 km',
                    'speed' => '200 km/h',
                    'capacity' => 'Carga útil de 100 kg',
                    'color' => '#006400'
                ],
                'shahed' => [
                    'name' => 'Shahed-131/136 (Zamora V-1)',
                    'type' => 'Ataque Kamikaze',
                    'length' => '3.5 m',
                    'wingspan' => '2.5 m',
                    'weight' => '200 kg',
                    'range' => '900-2000 km',
                    'speed' => '180 km/h',
                    'capacity' => 'Explosivos de 50 kg',
                    'color' => '#FF0000'
                ],
                'ansu200' => [
                    'name' => 'ANSU-200',
                    'type' => 'Multifunción (ISR, Ataque, Antidrones)',
                    'length' => '4.5 m',
                    'wingspan' => '6.0 m',
                    'weight' => '300 kg',
                    'range' => '500 km',
                    'speed' => '220 km/h',
                    'capacity' => 'Sensores y armas ligeras',
                    'color' => '#0000FF'
                ]
            ];

            $selectedDrone = isset($_GET['drone']) ? $_GET['drone'] : 'mohajer2';
            $drone = $drones[$selectedDrone];
            ?>
            <p><strong>Tipo:</strong> <span id="type"><?php echo $drone['type']; ?></span></p>
            <p><strong>Longitud:</strong> <span id="length"><?php echo $drone['length']; ?></span></p>
            <p><strong>Envergadura:</strong> <span id="wingspan"><?php echo $drone['wingspan']; ?></span></p>
            <p><strong>Peso:</strong> <span id="weight"><?php echo $drone['weight']; ?></span></p>
            <p><strong>Alcance:</strong> <span id="range"><?php echo $drone['range']; ?></span></p>
            <p><strong>Velocidad:</strong> <span id="speed"><?php echo $drone['speed']; ?></span></p>
            <p><strong>Capacidad:</strong> <span id="capacity"><?php echo $drone['capacity']; ?></span></p>
        </div>
        <div id="canvas2D">
            <div class="drone-2d" style="background-color: <?php echo $drone['color']; ?>;"></div>
        </div>
        <canvas id="canvas3D" width="600" height="400"></canvas>
    </div>
    <footer>
        Elaborado por el Profesor Ángel Chacín Ávila, Licenciado en Física, UNES Núcleo Zulia, página web con fines académicos, educativos
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // Configuración de Three.js para el modelo 3D
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, 600 / 400, 0.1, 1000);
        const canvas3D = document.getElementById('canvas3D');
        const renderer = new THREE.WebGLRenderer({ canvas: canvas3D });
        renderer.setSize(600, 400);
        camera.position.z = 5;

        const light = new THREE.AmbientLight(0x404040);
        scene.add(light);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
        directionalLight.position.set(0, 1, 1);
        scene.add(directionalLight);

        let currentMesh = null;

        // Crear modelo 3D basado en el dron seleccionado
        function updateDrone() {
            const droneColor = '<?php echo $drone['color']; ?>';
            if (currentMesh) scene.remove(currentMesh);

            const geometry = '<?php echo $selectedDrone; ?>' === 'shahed' ? 
                new THREE.CylinderGeometry(0.3, 0.3, 1.5, 32) : 
                new THREE.BoxGeometry(1.5, 0.3, 0.5);
            const material = new THREE.MeshPhongMaterial({ color: new THREE.Color(droneColor) });
            currentMesh = new THREE.Mesh(geometry, material);
            scene.add(currentMesh);

            function animate() {
                requestAnimationFrame(animate);
                if (currentMesh) {
                    currentMesh.rotation.y += 0.01;
                    renderer.render(scene, camera);
                }
            }
            animate();
        }

        // Inicializar con el dron seleccionado
        updateDrone();

        // Verificar si WebGL está soportado
        if (!renderer) {
            console.error('WebGL no está soportado en este navegador.');
            alert('Error: WebGL no está soportado. Por favor, usa un navegador compatible como Chrome o Firefox.');
        }
    </script>
</body>
</html>
