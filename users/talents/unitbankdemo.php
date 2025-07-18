<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Unit Web Components Demo</title>
  <script async src="https://ui.s.unit.sh/release/latest/components.js"></script>
</head>
<body>
  <h1>Unit Web Components Demo</h1>

  <!-- Account Component -->
  <unit-elements-account account-id="355014"></unit-elements-account>

  <!-- Activity Component -->
  <unit-elements-activity account-id="355014"></unit-elements-activity>

  <script>
      // Event listener for account changes
      document
          .querySelector("unit-elements-account")
          .addEventListener("unitAccountChanged", async function (e) {
              const eventData = await e.detail;
              const selectedAccountId = eventData.data.id;
              document
                  .querySelector("unit-elements-activity")
                  .setAttribute("account-id", selectedAccountId);
          });

      // Event listener for component load
      document
          .querySelector("unit-elements-account")
          .addEventListener("unitOnLoad", function (e) {
              if (e.detail.errors) {
                  console.error("Error loading component: ", e.detail.errors);
              } else {
                  console.log("Component loaded successfully");
              }
          });

      // Set the customer token
      const customerToken = 'v2.public.eyJyb2xlIjoiYWRtaW4iLCJ1c2VySWQiOiIxMjI2OSIsInN1YiI6InN1cGVyYWRtaW5Ac2ltcGxyLmNvbS5uZyIsImV4cCI6IjIwMjQtMDgtMTdUMTM6MzU6MTIuOTM1WiIsImp0aSI6IjM1NTAxOCIsIm9yZ0lkIjoiNTU5NyIsInNjb3BlIjoiYXBwbGljYXRpb25zLXdyaXRlIGFjY291bnRzLXdyaXRlIGNhcmRzLXdyaXRlIiwib3JnIjoiU2ltcGxyIEJ1c2luZXNzIFN1aXRlIiwidXNlclR5cGUiOiJvcmciLCJpc1VuaXRQaWxvdCI6ZmFsc2V9ZouVA3aLkTmu7jE6zHra1PWnzasIG8gCNSNu--3tjbcTTYFU1WzfCIJGRq009YnjHreztDWid9BqdhLLqEBHBw';
      document.querySelector("unit-elements-account").setAttribute("customer-token", customerToken);
      document.querySelector("unit-elements-activity").setAttribute("customer-token", customerToken);
  </script>
</body>
</html>
