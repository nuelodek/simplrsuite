<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZipRecruiter Job API</title>
</head>
<body>
    <h1>ZipRecruiter Job API</h1>
    <form id="jobForm">
        <label for="action">Action:</label>
        <select id="action" name="action">
            <option value="post">Post a job</option>
            <option value="update">Update a job</option>
            <option value="get">Get a job</option>
            <option value="close">Close a job</option>
        </select><br><br>
        
        <div id="jobIdField" style="display:none;">
            <label for="jobId">Job ID:</label>
            <input type="text" id="jobId" name="jobId"><br><br>
        </div>
        
        <div id="jobDetails">
            <label for="title">Job Title:</label>
            <input type="text" id="title" name="title"><br><br>
            
            <label for="description">Job Description:</label>
            <textarea id="description" name="description"></textarea><br><br>
            
            <label for="location">Job Location:</label>
            <input type="text" id="location" name="location"><br><br>
            
            <label for="salary">Job Salary:</label>
            <input type="text" id="salary" name="salary"><br><br>
        </div>
        
        <input type="submit" value="Submit">
    </form>

    <script>
        document.getElementById('action').addEventListener('change', function() {
            var jobIdField = document.getElementById('jobIdField');
            var jobDetails = document.getElementById('jobDetails');
            
            if (this.value === 'post') {
                jobIdField.style.display = 'none';
                jobDetails.style.display = 'block';
            } else if (this.value === 'update') {
                jobIdField.style.display = 'block';
                jobDetails.style.display = 'block';
            } else {
                jobIdField.style.display = 'block';
                jobDetails.style.display = 'none';
            }
        });

        document.getElementById('jobForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            var action = document.getElementById('action').value;
            var jobId = document.getElementById('jobId').value;
            var jobData = {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                location: document.getElementById('location').value,
                salary: document.getElementById('salary').value
            };

            let url = '';
            let method = '';

            switch(action) {
                case 'post':
                    url = 'https://api.ziprecruiter.com/partner/v0/job';
                    method = 'POST';
                    break;
                case 'update':
                    url = `https://api.ziprecruiter.com/partner/v0/job/${jobId}`;
                    method = 'PUT';
                    break;
                case 'get':
                    url = `https://api.ziprecruiter.com/partner/v0/job/${jobId}`;
                    method = 'GET';
                    break;
                case 'close':
                    url = `https://api.ziprecruiter.com/partner/v0/job/${jobId}`;
                    method = 'DELETE';
                    break;
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Authorization': 'Basic meowmeowmeow', // Replace with your actual API key
                    'Content-Type': 'application/json'
                },
                body: (method === 'GET' || method === 'DELETE') ? null : JSON.stringify(jobData)
            });

            const result = await response.json();
            console.log(result);
            alert('Response received, check the console for details.');
        });
    </script>
</body>
</html>
