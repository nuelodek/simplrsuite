<?php
// PHP QR Code Generator

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
  
    // Encode product information
    $data = "Name: $name | Price: $price | Quantity: $quantity";
  
    // We'll generate the QR code client-side, so we just need to return the data
    echo json_encode(['data' => $data]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
</head>
<body>
    <h1>QR Code Generator</h1>
    <form id="qrCodeForm">
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" required><br>
      
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required><br>
      
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required><br>
      
        <button type="submit">Generate QR Code</button>
    </form>

    <div id="qrCodeContainer"></div>

    <script>
        document.getElementById('qrCodeForm').addEventListener('submit', function(e) {
            e.preventDefault();
          
            const name = document.getElementById('name').value;
            const price = document.getElementById('price').value;
            const quantity = document.getElementById('quantity').value;
          
            const data = `Name: ${name} | Price: ${price} | Quantity: ${quantity}`;
          
            // Generate QR code
            const container = document.getElementById('qrCodeContainer');
            container.innerHTML = '';
            new QRCode(container, {
                text: data,
                width: 128,
                height: 128
            });
          
            // Send data to server (optional, for logging or other purposes)
            const formData = new FormData(this);
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
