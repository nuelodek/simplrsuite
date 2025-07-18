  <?php
  // PHP Barcode Generator

  // Function to generate barcode using JsBarcode
  function generateBarcode($data) {
      // We'll use JavaScript to generate the barcode, so this function is not needed anymore
      return '';
  }

  // Handle form submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = $_POST['name'];
      $price = $_POST['price'];
      $quantity = $_POST['quantity'];
    
      // Encode product information
      $data = "Name: $name | Price: $price | Quantity: $quantity";
    
      // We'll generate the barcode client-side, so we just need to return the data
      echo json_encode(['data' => $data]);
      exit;
  }
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Barcode Generator</title>
      <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
  </head>
  <body>
      <h1>Barcode Generator</h1>
      <form id="barcodeForm">
          <label for="name">Product Name:</label>
          <input type="text" id="name" name="name" required><br>
        
          <label for="price">Price:</label>
          <input type="number" id="price" name="price" step="0.01" required><br>
        
          <label for="quantity">Quantity:</label>
          <input type="number" id="quantity" name="quantity" required><br>
        
          <button type="submit">Generate Barcode</button>
      </form>

      <div id="barcodeContainer"></div>

      <script>
          document.getElementById('barcodeForm').addEventListener('submit', function(e) {
              e.preventDefault();
            
              const name = document.getElementById('name').value;
              const price = document.getElementById('price').value;
              const quantity = document.getElementById('quantity').value;
            
              const data = `Name: ${name} | Price: ${price} | Quantity: ${quantity}`;
            
              // Generate barcode using JsBarcode
              const canvas = document.createElement('canvas');
              JsBarcode(canvas, data, {
                  format: "CODE128",
                  width: 2,
                  height: 100,
                  displayValue: true
              });
            
              const container = document.getElementById('barcodeContainer');
              container.innerHTML = '';
              container.appendChild(canvas);
            
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
