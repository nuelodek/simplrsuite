<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Admin Sign Up </title>
    <style>
        body {
            background-color: white;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: white;
            width: 500px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        form .levelone, form .leveltwo {
            display: flex;
            flex-direction: column;
            transition: opacity 0.5s, visibility 0.5s;
        }

        .levelone.hidden, .leveltwo.hidden {
            opacity: 0;
            visibility: hidden;
            height: 0;
            overflow: hidden;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input, select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid grey;
            outline: none;
            margin-bottom: 10px;
        }

        button {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .word {
            font-size: 30px;
            margin-bottom: 0px;
        }

        input[type="submit"]:hover {
            background-color: black;
            color: white;
        }

        p {
            line-height: 1.0;
        }
    </style>
</head>
<body>
    <div class="container">
        <form id="authForm" action="congrats.php" method="post">
            <h3 class="word">SIMPLR</h3>
            <p id="formDescription">Create your all-in-one business management account</p>
            <div class="levelone">
                <label>Company Name*</label>
                <input type="text" name="company_name" placeholder="Enter your company name" required maxlength="25" title="Maximum 24 characters">
                <label>Industry*</label>
                <select name="industry" class="industry" required>
                    <option value="" disabled selected>Select an industry</option>
                    <option value="agriculture">Agriculture</option>
                    <option value="automotive">Automotive</option>
                    <option value="banking">Banking</option>
                    <option value="biotechnology">Biotechnology</option>
                    <option value="chemicals">Chemicals</option>
                    <option value="construction">Construction</option>
                    <option value="consulting">Consulting</option>
                    <option value="education">Education</option>
                    <option value="electronics">Electronics</option>
                    <option value="energy">Energy</option>
                    <option value="entertainment">Entertainment</option>
                    <option value="fashion">Fashion</option>
                    <option value="finance">Finance</option>
                    <option value="food_beverage">Food and Beverage</option>
                    <option value="healthcare">Healthcare</option>
                    <option value="hospitality">Hospitality</option>
                    <option value="insurance">Insurance</option>
                    <option value="it">Information Technology</option>
                    <option value="legal">Legal</option>
                    <option value="manufacturing">Manufacturing</option>
                    <option value="marketing">Marketing</option>
                    <option value="media">Media</option>
                    <option value="mining">Mining</option>
                    <option value="pharmaceuticals">Pharmaceuticals</option>
                    <option value="real_estate">Real Estate</option>
                    <option value="retail">Retail</option>
                    <option value="telecommunications">Telecommunications</option>
                    <option value="transportation">Transportation</option>
                    <option value="travel">Travel</option>
                    <option value="utilities">Utilities</option>
                    <option value="others">Others</option>
                </select>

                <button type="button" onclick="toggleL1()">Next</button>
                                <!--<button> Sign up with LinkedIn</button>-->

            </div>
            <div class="leveltwo hidden">
                <label>Email Address*</label>
                <input type="email" name="email" placeholder="name@company.com" required>
                              <label>Password*</label>
                              <div style="position: relative;">
                                  <input type="password" name="password" placeholder="********" required style="padding-right: 30px; width: 100%; box-sizing: border-box;">
                                  <span class="password-toggle" onclick="togglePasswordVisibility(this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); top: 17px;  cursor: pointer;">👁️</span>
                              </div>

                              <label>Confirm Password</label>
                              <div style="position: relative;">
                                  <input type="password" id="confirmPassword" name="confirm_password" placeholder="********" required style="padding-right: 30px; width: 100%; box-sizing: border-box;">
                                  <span class="password-toggle" onclick="togglePasswordVisibility(this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); top: 17px; cursor: pointer;">👁️</span>
                              </div>
                <!-- Hidden input for generated company ID -->
                <input type="hidden" id="companyID" name="company_id" value="" required>

                <!-- Hidden input for status -->
                <input type="hidden" name="status" value="unverified">

                <button type="button" onclick="toggleL2()">Back</button>
                <input type="submit" value="Submit">
                
                
                
            </div>

            <p>Got an account? <a href="login.html" id="toggleFormLink">Login</a></p>
        </form>
    </div>

    <script>
        document.getElementById("authForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent the form from submitting

            // Generate a random number for company ID
            var companyId = Math.floor(Math.random() * 1000000); // Change range as needed

            // Update the input field with the generated ID
            document.getElementById("companyID").value = companyId;

            // Proceed with form submission
            this.submit();
        });

        function toggleL1() {
            // Validate levelone inputs
            var companyName = document.querySelector('input[name="company_name"]').value;
            var industry = document.querySelector('select[name="industry"]').value;

            if (companyName && industry) {
                document.querySelector('.levelone').classList.add('hidden');
                document.querySelector('.leveltwo').classList.remove('hidden');
            } else {
                alert("Please fill out all required fields.");
            }
        }

        function toggleL2() {
            document.querySelector('.levelone').classList.remove('hidden');
            document.querySelector('.leveltwo').classList.add('hidden');
        }
    </script>


<script>

        function togglePasswordVisibility(element) {
            var passwordInput = element.previousElementSibling;
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                element.textContent = "🙈";
            } else {
                passwordInput.type = "password";
                element.textContent = "👁️";
            }
        }
</script>


</body>
</html>
