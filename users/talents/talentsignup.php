<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Sign Up / Login</title>
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
        <form id="authForm" action="nextsteps.php" method="post">
            <h3 class="word">SIMPLR</h3>
            <p id="formDescription">Create your all-in-one talent account</p>
            <div class="levelone">
                <label>Full Name*</label>
                <input type="text" name="full_name" placeholder="Enter your full name" required>

                <label>Industry*</label>
                <select name="industry" class="industry" required>
                    <option value="" disabled selected>Select an industry you love to work</option>
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
                    <option value="other">Others</option>
                </select>

                <button type="button" onclick="toggleL1()">Next</button>
                                <!--<button> Sign up with LinkedIn</button>-->

            </div>
            <div class="leveltwo hidden">
                <label>Email Address*</label>
                <input type="email" name="email" placeholder="name@email.com" required>

                <label>Password*</label>
                <input type="password" name="password" placeholder="********" required>

                <label>Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirm_password" placeholder="********" required>

                <!-- Hidden input for generated company ID -->
                <input type="hidden" id="talentId" name="talent_id" value="" required>

                <!-- Hidden input for status -->
                <input type="hidden" name="status" value="unverified">

                <button type="button" onclick="toggleL2()">Back</button>
                <input type="submit" value="Submit">
                
                
                
            </div>

            <p>Got an account? <a href="talentlogin.php" id="toggleFormLink">Login</a></p>
        </form>
    </div>

    <script>
        document.getElementById("authForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent the form from submitting

            // Generate a random number for company ID
            var talentID = Math.floor(Math.random() * 1000000); // Change range as needed

            // Update the input field with the generated ID
            document.getElementById("talentId").value = talentID;

            // Proceed with form submission
            this.submit();
        });

        function toggleL1() {
            // Validate levelone inputs
            var fullName = document.querySelector('input[name="full_name"]').value;
            var industry = document.querySelector('select[name="industry"]').value;

            if (fullName && industry) {
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
</body>
</html>