    <form id="customerForm" class="customizeforms">
        
        
        <input type="hidden" id="email" name="email" required placeholder="" value="<?php echo $email; ?>" readonly hidden>
        
                
        <div class="input-container">

        <input type="text" id="firstName" name="firstName" required placeholder="" value="<?php echo $companyName; ?>" readonly>
        <label> Account Name </label>

        </div> 

        
        <div class="input-container">
        <input type="tel" id="phone" name="phone" placeholder="" value="<?php echo $phonenumber; ?>" required>
        <label> Phone </label>
        </div>
        <div class="input-container">
        <select id="preferredBank" name="preferredBank" required>
            <option value="">Select Preferred Bank</option>
            <option value="wema-bank" selected>Wema Bank</option>
            <option value="polaris-bank" selected>Polaris Bank</option>
            <option value="paystack-titan">Titan Trust Bank</option>
        </select>
        <label> Preferred Bank </label>
        </div>

        <input type="hidden"  id="companyId" name="companyId" required placeholder="Company ID" value="<?php echo $companyId; ?>">
        <input type="hidden" id="lastName" class="lastnamephpne" name="lastName" required placeholder="Last Name" value="">
       
        
        <select id="userType" name="userType" hidden>
            <option value="">Select User Type</option>
            <option value="admin" selected>Admin</option>
        </select>

        <input type="submit" value="Create Account">
    </form> 

    <script>
    // Replace with your actual Paystack secret key
    const PAYSTACK_SECRET_KEY = 'sk_live_89ce8303adb74e9e95dd0401fceb9147f5c6f696';

    // Function to create a customer on Paystack
    const createCustomer = async (email, firstName, lastName, phone) => {
        const payload = {
            email,
            first_name: firstName,
            last_name: lastName,
            phone: phone
        };

        const response = await fetch('https://api.paystack.co/customer', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${PAYSTACK_SECRET_KEY}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(`Paystack API error: ${error.message}`);
        }

        return await response.json();
    };

    // Function to create a dedicated virtual account on Paystack
    const createDedicatedAccount = async (customerId, preferredBank) => {
        const response = await fetch('https://api.paystack.co/dedicated_account', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${PAYSTACK_SECRET_KEY}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                customer: customerId,
                preferred_bank: preferredBank
            })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(`Paystack API error: ${error.message}`);
        }

        return await response.json();
    };

    // Function to create a customer bank account and store it in the database
    const createCustomerBankAccount = async (email, firstName, lastName, phone, preferredBank, companyId, userType) => {
        try {
            // Step 1: Create the customer
            const customerResponse = await createCustomer(email, firstName, lastName, phone);
            if (!customerResponse.status) {
                throw new Error('Failed to create customer');
            }
            const customerId = customerResponse.data.id;

            // Step 2: Create the dedicated virtual account
            const accountResponse = await createDedicatedAccount(customerId, preferredBank);
            if (!accountResponse.status) {
                throw new Error('Failed to create dedicated account');
            }

            // Step 3: Store the customer and account information in the database
            const storeResponse = await fetch('store_bank_account.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    customer_id: customerId,
                    email,
                    first_name: firstName,
                    last_name: lastName,
                    phone,
                    preferred_bank: preferredBank,
                    account_number: accountResponse.data.account_number,
                    bank_name: accountResponse.data.bank.name,
                    company_id: companyId,
                    user_type: userType
                })
            });

            if (!storeResponse.ok) {
                const errorData = await storeResponse.json();
                throw new Error(errorData.message || 'Failed to store bank account information');
            }

            const responseData = await storeResponse.json();

            // Success: Notify the user with account number
            alert(`Customer bank account created successfully! Account Number: ${accountResponse.data.account_number}`);
            console.log('Customer bank account created and stored successfully:', accountResponse.data);

            return accountResponse.data;


        } catch (error) {
            console.error('Error creating customer bank account:', error.message);
            throw error;
        }
    };

    // Handle form submission
    const handleFormSubmit = async (event) => {
        event.preventDefault();
  
        // Get form data
        const email = document.getElementById('email').value;
        const firstName = document.getElementById('firstName').value;
        const lastName = document.getElementById('lastName').value;
        const phone = document.getElementById('phone').value;
        const preferredBank = document.getElementById('preferredBank').value;
        const companyId = document.getElementById('companyId').value;
        const userType = document.getElementById('userType').value;

        try {
            const account = await createCustomerBankAccount(email, firstName, lastName, phone, preferredBank, companyId, userType);
            console.log('Account created:', account);
        } catch (error) {
            if (error.message === 'Bank account created successfully') {
                alert('Customer bank account created successfully!');
                console.log('Account created successfully');
            } else {
                alert('Failed to create customer bank account: ' + error.message);
            }
        }
    };

    // Attach the form submit handler
    document.getElementById('customerForm').addEventListener('submit', handleFormSubmit);
    </script>
