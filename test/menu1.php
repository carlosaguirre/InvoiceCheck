<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Ocultable</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            transition: margin-left 0.5s;
        }
        
        #menu {
            width: 250px;
            height: 100vh;
            background: #333;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.3s;
        }
        
        #menu.hidden {
            transform: translateX(-250px);
        }
        
        #content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
            transition: margin-left 0.5s;
        }
        
        #content.full-width {
            margin-left: 0;
        }
        
        #toggle-btn {
            position: fixed;
            left: 260px;
            top: 10px;
            z-index: 100;
            background: #333;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 0 5px 5px 0;
            transition: left 0.3s;
        }
        
        #toggle-btn.hidden {
            left: 10px;
        }
        
        .menu-item {
            padding: 15px;
            border-bottom: 1px solid #444;
            cursor: pointer;
        }
        
        .menu-item:hover {
            background: #444;
        }
    </style>
</head>
<body>
    <div id="menu">
        <div class="menu-item">Inicio</div>
        <div class="menu-item">Productos</div>
        <div class="menu-item">Servicios</div>
        <div class="menu-item">Contacto</div>
    </div>
    
    <button id="toggle-btn">☰</button>
    
    <div id="content">
        <h1>Contenido Principal</h1>
        <p>Aquí va el contenido de tu página.</p>
    </div>
    
    <script>
        const toggleBtn = document.getElementById('toggle-btn');
        const menu = document.getElementById('menu');
        const content = document.getElementById('content');
        
        toggleBtn.addEventListener('mouseover', function() {
            menu.classList.toggle('hidden');
            content.classList.toggle('full-width');
            toggleBtn.classList.toggle('hidden');
            
            // Cambiar el ícono del botón
            if (menu.classList.contains('hidden')) {
                toggleBtn.innerHTML = '☰';
            } else {
                toggleBtn.innerHTML = '×';
            }
        });
    </script>
</body>
</html>
