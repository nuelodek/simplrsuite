
  function initializePayment() {
      const paymentProvider = document.getElementById('paymentProvider').value;
      const currency = document.getElementById('currency').value;
      const amount = document.getElementById('amount').value;
      const unique_id = document.getElementById('unique_id').value;
      const customerEmail = document.querySelector(".customerEmail").value;
      const customerName = document.querySelector(".customerName").value;
      const paymentDescription = document.querySelector(".paymentDescription").value;   
      const logo = "https://example.com/logo.png";

      const paymentData = {
          amount: amount,
          currency: currency,
          customer: {
              email: customerEmail,
              name: customerName
          },
          metadata: {
              customerName: customerName,
              description: paymentDescription,
              unique_id: unique_id
          },
          logo: logo
      };

      if (paymentProvider === 'flutterwave') {
          initializeFlutterwavePayment(paymentData);
      } else if (paymentProvider === 'paystack') {
          initializePaystackPayment(paymentData);
      } else {
          console.error('Unsupported payment provider');
      }
  }

  function initializeFlutterwavePayment(paymentData) {
      FlutterwaveCheckout({
          public_key: "",
          tx_ref: generateReference(),
          amount: paymentData.amount,
          currency: paymentData.currency,
          customer: paymentData.customer,
          metadata: paymentData.metadata,
          payment_options: "card, banktransfer, ussd",
          customizations: {
              title: paymentData.metadata.customerName,
              description: paymentData.metadata.description,
              logo: paymentData.logo
          },
          callback: function (response) {
              handlePaymentResponse(response, 'flutterwave');
          },
      });
  }

  function initializePaystackPayment(paymentData) {
      const paystackData = {
          key: '',
          email: paymentData.customer.email,
          amount: paymentData.amount * 100,
          currency: paymentData.currency,
          metadata: paymentData.metadata,
          callback: function(response) {
              handlePaymentResponse(response, 'paystack');
          },
          onClose: function() {
              console.log('Payment closed');
          }
      };

      const paystackPopup = PaystackPop.setup(paystackData);
      paystackPopup.openIframe();
  }

  function generateReference() {
      return 'tx_' + Math.random().toString(36).substr(2, 9);
  }

  function handlePaymentResponse(response, provider) {
      console.log("Full response object:", response);

      if ((provider === 'flutterwave' && response.status === "successful") ||
          (provider === 'paystack' && response.status === "success")) {
          console.log("Payment successful");
          sendPaymentFormData(response);
      } else {
          console.log("Payment failed with response:", response);
      }
  }

  function sendPaymentFormData(response) {
      fetch('process_payment.php', {
          method: 'POST',
          body: JSON.stringify(response),
          headers: {
              'Content-Type': 'application/json',
          },
      })
      .then(response => response.json())
      .then(data => {
          console.log(data);
          // Handle the successful response from the server
          if (data.success) {
              // Display success message to the user
              alert("Payment processed successfully!");
          } else {
              // Display error message to the user
              alert("Payment processing failed. Please try again.");
          }
      })
      .catch(error => {
          console.error('Error:', error);
          // Handle errors during payment processing
          alert("An error occurred while processing the payment. Please try again later.");
      });
  }
