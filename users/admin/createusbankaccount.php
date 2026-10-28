
<form id="applicationForm" class="usbankaccountcreation">
      <label for="ssn">SSN:</label>

      
      <input type="text" id="ssn" name="ssn" value="301880999" required pattern="\d{9}" title="Please enter a 9-digit SSN">

      <label for="firstName">First Name:</label>
      <input type="text" id="firstName" name="firstName" value="David" required>

      <label for="lastName">Last Name:</label>
      <input type="text" id="lastName" name="lastName" value="Cook" required>

      <label for="dateOfBirth">Date of Birth:</label>
      <input type="date" id="dateOfBirth" name="dateOfBirth" value="1962-05-03" required>

      <label for="street">Street:</label>
      <input type="text" id="street" name="street" value="8609 Woodsman Ave." required>

      <label for="city">City:</label>
      <input type="text" id="city" name="city" value="Chicopee" required>

      <label for="state">State:</label>
      <input type="text" id="state" name="state" value="MA" required pattern="[A-Z]{2}" title="Please enter a 2-letter state code">

      <label for="postalCode">Postal Code:</label>
      <input type="text" id="postalCode" name="postalCode" value="01020" required pattern="\d{5}" title="Please enter a 5-digit postal code">

      <label for="country">Country:</label>
      <input type="text" id="country" name="country" value="US" required>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="david.cook@mymail.com" required>

      <label for="phoneCountryCode">Phone Country Code:</label>
      <input type="text" id="phoneCountryCode" name="phoneCountryCode" value="1" required pattern="\d+" title="Please enter a valid country code">

      <label for="phoneNumber">Phone Number:</label>
      <input type="tel" id="phoneNumber" name="phoneNumber" value="2252124876" required pattern="\d{10}" title="Please enter a 10-digit phone number">

      <label for="occupation">Occupation:</label>
      <input type="text" id="occupation" name="occupation" value="Doctor" required>

      <label for="tagTest">Tag (test):</label>
      <input type="text" id="tagTest" name="tagTest" value="webhook-tag">

      <label for="tagKey">Tag (key):</label>
      <input type="text" id="tagKey" name="tagKey" value="another-tag">

      <label for="tagNumber">Tag (number):</label>
      <input type="text" id="tagNumber" name="tagNumber" value="111">

      <input type="submit" value="Submit Application">
</form>

<div id="responseContainer"></div>

<script>
    async function createAccount(customerId) {
      const API_URL = 'https://api.s.unit.sh';
      const API_KEY = 'Bearer '; // Replace with your actual API key

      try {
        // Create account
        const accountResponse = await fetch(`${API_URL}/accounts`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/vnd.api+json',
            'Authorization': API_KEY
          },
          body: JSON.stringify({
            data: {
              type: "depositAccount",
              attributes: {
                depositProduct: "checking",
                tags: {
                  purpose: "primary"
                }
              },
              relationships: {
                customer: {
                  data: {
                    type: "customer",
                    id: customerId
                  }
                }
              }
            }
          })
        });

        const accountData = await accountResponse.json();
        console.log('Account created:', accountData);
        displayResponse('Account Creation Response', accountData);
        return accountData;
      } catch (error) {
        console.error('Error creating account:', error);
        displayResponse('Account Creation Error', error);
        throw error;
      }
    }

    document.getElementById('applicationForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const jsonData = {
        data: {
          type: "individualApplication",
          attributes: {
            ssn: formData.get('ssn'),
            fullName: {
              first: formData.get('firstName'),
              last: formData.get('lastName')
            },
            dateOfBirth: formData.get('dateOfBirth'),
            address: {
              street: formData.get('street'),
              city: formData.get('city'),
              state: formData.get('state'),
              postalCode: formData.get('postalCode'),
              country: formData.get('country')
            },
            email: formData.get('email'),
            phone: {
              countryCode: formData.get('phoneCountryCode'),
              number: formData.get('phoneNumber')
            },
            occupation: formData.get('occupation'),
            tags: {
              test: formData.get('tagTest'),
              key: formData.get('tagKey'),
              number: formData.get('tagNumber')
            }
          }
        }
      };

      try {
        const response = await fetch('https://api.s.unit.sh/applications', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/vnd.api+json',
            'Authorization': 'Bearer ' // Replace with your actual API key
          },
          body: JSON.stringify(jsonData)
        });

        const data = await response.json();
        console.log('Application submitted:', data);
        displayResponse('Application Submission Response', data);

        if (response.ok && data.data.attributes.status === 'Approved') {
          // Create account after successful application
          const customerId = data.data.relationships.customer.data.id;
          const accountData = await createAccount(customerId);
          console.log('Account created:', accountData);

          alert('Application submitted and approved successfully! Account creation initiated.');
        } else if (response.ok) {
          alert('Application submitted successfully, but not yet approved. Please check the response details below.');
        } else {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
      } catch (error) {
        console.error('Error:', error);
        displayResponse('Error', error);
        alert('An error occurred while submitting the application or creating the account. Please check the response details below.');
      }
    });

    function displayResponse(title, data) {
      const responseContainer = document.getElementById('responseContainer');
      const responseElement = document.createElement('div');
      responseElement.innerHTML = `
        <h2>${title}</h2>
        <pre>${JSON.stringify(data, null, 2)}</pre>
      `;
      responseContainer.appendChild(responseElement);
    }
</script>