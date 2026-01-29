<html>
<head>
<script>
 // Escucha los mensajes enviados desde la página principal
window.addEventListener('message', function(event) {
    // Filtra solo los mensajes que contienen los datos esperados
    if (event.data && event.data.message && event.data.value) {
        const data = event.data;
        // Muestra tanto el mensaje como el valor
        document.getElementById('receivedData').innerText = 
            `Mensaje: ${data.message}, Valor: ${data.value}`;
        if (data.message.includes("ComercioExterior Control")) document.getElementById('receivedData').innerText += " => PERMITIDO";
    }
});
</script>
</head>
<body>
<h1>COMEXT TEST</h1>
<div id="receivedData"></div>
</body>
</html>