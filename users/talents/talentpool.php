<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
include "submit_talent.php"; 

session_start();

// Check if talent_id and user_id are set in the session
if (!isset($_SESSION['talent_id']) || !isset($_SESSION['user_id'])) {
    // Redirect to login.html if talent_id or user_id is not found
    header("Location: talentlogin.php");
    exit();
}


$userId = $_SESSION['user_id'];
$email = $_SESSION['email'];
$fullName = $_SESSION['full_name'];
$industry = $_SESSION['industry'];
$talentId = $_SESSION['talent_id'];
$status = $_SESSION['status'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/a75a719a54.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <link rel="icon" type="image/png" href="/simple-social-svgrepo-com.png">  

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <title>Talent Profile</title>    

    <style>

/* styles.css */
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');
  
    @import url('https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap');

html, body {
  font-family: "Plus Jakarta Sans", sans-serif;
  margin: 0;
  padding: 0;
  height: auto;
  display: flex;
  flex-direction: column;
}

body {
  background-color: #f5f7f8;
}

.main-content {
  background-color: #f5f7f8;
  flex-grow: 1;
  height: max-height; /* Ensures that main-content takes full height */
  box-sizing: border-box; /* Ensure padding is included in height */
}
.sidebar {
    width: 200px;
    background-color: #f8f8f8;
    padding: 20px;
    height: 100%;
    position: fixed;
    overflow-x: hidden;
    transition: 0.3s;
    border: 1px solid #ccc;
}



.container {
    /*display: flex;*/
    margin-left: 270px;
    /*height: auto;*/
}


.logo {
    margin-top: 20px;
    background-color:none;
}

.create-content-btn {
    width: 100%;
    border-radius: 5px;
    padding: 10px;
    text-align: center;
    background-color: #6200ea;
    color: #FFFFFF;
    border: none;
    cursor: pointer;
    margin-bottom: 20px;
    /* text-align: left; */
    font-size: 16px;
}

.menu {
    list-style: none;
    padding: 0;
    font-size: 12px;
}

.menu li {
    padding: 10px 0;
    cursor: pointer;
}

.trial-upgrade {
    margin-top: 20px;
    margin-bottom: -20px;
}

.upgrade-btn {
    width: 100%;
    border: 1px solid black;
    background-color: transparent;
    padding: 5px;
    color: black;
    cursor: pointer;
    text-align: center;
    border-radius: 5px;
    font-size: 13px;
}

.user-info {
    margin-top: 50px;
    /* text-align: center; */
    display: flex;
    gap: 10px;
}

.avatar {
    width: 30px;
    height: 30px;
    border-radius: 5px;
    background-color: #6200ea;
    color: #FFFFFF;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    margin-bottom: 10px;
}


.task-input {
    display: flex;
    /* justify-content: center; */
    /* align-items: center; */
    margin-top: 10px;
}

.task-input input {
    padding:20px;
    width: 60%;
    margin-right: 10px;
    border: 1px solid #CCC;
    border-radius: 4px;
}


.task-input {
            position: relative;
            width: 100%;
            max-width: 400px; /* Adjust this as needed */
        }
        .task-input input[type="text"] {
            width: 100%;
            padding-right: 80px; /* Adjust this value based on button width */
            box-sizing: border-box;
        }
        .task-input .create-btn {
            position: absolute;
            right: 20px;
            top: 10px;
            height: 65%;
            border: none;
            border-radius: 5px;
            /* background-color: #007BFF; Button color */
            color: white;
            padding: 10px 10px; /* Adjust padding for button size */
            cursor: pointer;
        }

.quick-tasks {
    display: flex;
    /* justify-content: space-between; */
    margin-top: 20px;
    margin-bottom: 20px;
}

.quick-tasks div {
    margin-right: 10px;
}
.task {
    background-color: #FFF;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-width: 170px;
    max-width: fit-content;
    height: 90px;
    /* text-align: center; */
    cursor: pointer;
}

.recent-content h2 {
    margin-bottom: 10px;
}

.recent-watched {
    background-color: #FFF;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.createclass {
    font-weight: 900;
    font-size: 2rem;
}

h1 {
    padding-top: 0px;
            margin-bottom: 5px; /* Adjust this value as needed */
        }
        p.createclass {
            margin-top: 0;
        }

        .styesl {
            margin-bottom: 40px;
        }



        .task span {
            font-weight: bold;
        }
       
        .task p small {
          
            background-color: yellow;
            padding: 5px 5px;
            border-radius: 5px;
            font-size: 0.5em; /* Makes the text smaller */
        }


/*.sidebar small:nth-child(1) {*/
/*      background-color: red;*/
/*      color: white;*/
/*      font-weight: bold;*/
/*      padding: 5px 5px;*/
/*      margin-left: 20px;*/
/*      border-radius: 5px;*/
/*      font-size: 0.5em;*/
/*}*/

/*.sidebar small:nth-child(2) {*/
/*      background-color: blue;*/
/*      color: white;*/
/*      font-weight: bold;*/
/*      padding: 5px 5px;*/
/*      margin-left: 20px;*/
/*      border-radius: 5px;*/
/*      font-size: 0.5em;*/
/*}*/

/*.sidebar small:nth-child(3) {*/
/*      background-color: green;*/
/*      color: white;*/
/*      font-weight: bold;*/
/*      padding: 5px 5px;*/
/*      margin-left: 20px;*/
/*      border-radius: 5px;*/
/*      font-size: 0.5em;*/
/*}*/

/*.sidebar small:nth-child(4) {*/
/*      background-color: orange;*/
/*      color: white;*/
/*      font-weight: bold;*/
/*      padding: 5px 5px;*/
/*      margin-left: 20px;*/
/*      border-radius: 5px;*/
/*      font-size: 0.5em;*/
/*}*/

/*.sidebar small:nth-child(5) {*/
/*      background-color: purple;*/
/*      color: white;*/
/*      font-weight: bold;*/
/*      padding: 5px 5px;*/
/*      margin-left: 20px;*/
/*      border-radius: 5px;*/
/*      font-size: 0.5em;*/
/*}*/
      
      .sidebar small {
      
      color: black;
      font-weight: bold;
      padding: 5px 5px;
      margin-left: 20px;
      border-radius: 5px;
      font-size: 0.7em;
}
      
            
        .menu li img {
width: 24px;
}



.menu li {
            display: flex;
            align-items: center;
        }

        .menu li img {
            margin-right: 10px;
        }

        
        .create-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #ffffff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .create-btn img {
            width: 20px;
            margin-left: 10px;
            /* background: white; */
        }

        .mayuassite {
            background-color: #FFFFFF;
            width: 10%;
            display: inline-flex;
            padding:5px; 
            float: right;
            font-size: 10px;
            border-radius: 5px;
            gap: 5px;
            margin-top: 10px;
            justify-content: center;
            margin-right: 20px;
        }

        .mayuassite span {
            margin-top: 3px;
        
        }
        

        .arrowbutton {
            width: 10px;
            height: 10px;
            margin-left: 60px;
        }

        .recent-watched {
    background-color: #f9f9f9;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.recent-content {
    display: flex;
    flex-direction: column;
}

.content-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.content-item:last-child {
    border-bottom: none;
}

.content-info {
    display: flex;
    flex-direction: column;
}

.content-info h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.content-info p {
    margin: 5px 0;
    font-size: 14px;
    color: #666;
}

.rating {
    display: flex;
    align-items: center;
}

.rating i {
    color: #f39c12;
    margin-right: 2px;
}

.content-actions {
    display: flex;
    align-items: center;
}

.content-actions button {
    background-color: #4CAF50;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.content-actions button:hover {
    background-color: #45a049;
}

.task p.notza span {
         
    }

.task p.notza small {
            font-size: 10px;
            color: #555; /* Slightly darker than the background */
            background-color: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
        }
      
li img {
 color: grey;   
}
        </style>



</head>
<body>
    
    
    <div class="main-content">
    <div class="content">
       <div class="sidebar">
                                                

<div class="logo" style="width:100px;" >
</div>
<div class="menulist" style="font-size: 12px;"> 

<ul class="menu"> 

 <div style="display: flex; align-items: center; margin-left: 0px; margin-top: 20px; margin-bottom: 10px; color: black;" >
    <img src="simple-social-svgrepo-com.png" style="width: 25px; height: 25px; background-color: none; border-radius: 5px;">
    <div style="display: flex; flex-direction: column; margin-left: 10px;">
        <span style="color: ; font-weight: bold;"> <?php echo $fullName; ?> </span>
        <span style="color: ; font-size: 8px;"> Talent ID: <?php echo $talentId; ?> </span>
      </div>
 </div>
    <li onclick="toggleLearn()"> <img src="https://www.svgrepo.com/show/501525/dashboard.svg" alt="Learn Icon">Talent Profile <span><small style="background-color: #FFD700;">5 alerts</small></span></li>
    <!--<li onclick="toggleCategories()"><img src="https://www.svgrepo.com/show/532824/folder-plus.svg" alt="Categories Icon"> Browse Companies-->
    <!--<img src="https://www.svgrepo.com/show/465697/next.svg" class="arrowbutton" alt="Next Arrow"></li>-->
    <li onclick="toggleNotifications()"><img src="https://www.svgrepo.com/show/460025/notification-alt.svg" alt="Notifications Icon">Notifications <span><small style="background-color: #90EE90;">10 new</small></span></li>
    <li onclick="toggleMessages()"> <img src="https://www.svgrepo.com/show/522602/messages.svg" alt="Message Icon"> Messages <span><small style="background-color: #87CEFA;">100+ unread</small></span></li></li>
    <li onclick="toggleAssessments()"> <img src="https://www.svgrepo.com/show/522602/messages.svg" alt="Message Icon"> Assessments <span><small style="background-color: #87CEFA;">100+ unread</small></span></li></li>
<li onclick="toggleWallet()"><img src="https://www.svgrepo.com/show/503592/support.svg" alt="Wallet Icon"><a href="mailto:support@simplr.com.ng" style="text-decoration: none; color: grey;">Support</a></li>
    <!--<li onclick="openBankModal()"><img src="https://www.svgrepo.com/show/521497/bank.svg" alt="Support Icon">Bank & Wallets <small style="background-color: #DDA0DD;"> Credits - 2 <br> Debits - 12 </small></span></li>              -->
                    <li onclick="toggleSupport()"><img src="https://www.svgrepo.com/show/453406/reports.svg" alt="Support Icon">Analytics <small style="background-color: #20B2AA;"> 12 new </small></span></li>              

                    <li onclick="toggleSupport()"><img src="https://www.svgrepo.com/show/106991/interview.svg" alt="Support Icon">Interviews <small style="background-color: #20B2AA;"> 12 new </small></span></li>              


    <!--<li onclick="toggleSupport()"><img src="https://www.svgrepo.com/show/488643/options.svg" alt="Support Icon">More -->
          <!--<img src="https://www.svgrepo.com/show/465697/next.svg" class="arrowbutton" alt="Next Arrow"></li>-->
</ul>
   
</div>
<div style="margin-top: 20px;">
<ul class="menu">
                    <!--<li onclick="toggleSupport()"><img src="https://www.svgrepo.com/show/524954/settings.svg" alt="Support Icon">Settings </li>-->
                   <li onclick="logout()"><img src="https://www.svgrepo.com/show/470849/logout.svg" alt="Support Icon">Logout </li>
<script>
function logout() {
    // Perform any necessary logout actions here
    // For example, clearing session storage or cookies
    sessionStorage.clear();
    localStorage.clear();
    
    // Redirect to the logout page
    window.location.href = 'talentlogout.php';
}
</script>

</ul>
</div>
</div>
     
      






    </div>
    
    
    
     
     <div class="main-contenta">
                
        
        
        <div class="form-group createprofile">
                                          <h1> Create Talent Profile </h1>
                       <div style="display: flex; align-items: center;">
                    <div style="margin-right: 20px;">
                        
                        <label for="import_profile">Import Linkedin Profile:</label>
                        <div style="display: flex; align-items: center;">
                            <div style="position: relative; display: inline-block;">
                                <input type="text" id="import_profile" name="import_profile" placeholder="Enter profile URL" style="height: 30px; padding-right: 70px; border-top-right-radius: 5px; border-bottom-right-radius: 5px;">
                                <button type="button" id="importButton" style="position: absolute; right: 0; top: 5px; padding: 5px 10px; background-color: #808080; color: white; height: 30px; outline: none; border:none; border-top-right-radius: 5px; border-bottom-right-radius: 5px;">Import</button>
                            </div>
                        </div>
                    </div>
                    <div style="margin: 0px 40px 0px 20px;">
                        <p style="color: #808080; font-weight: bold; text-transform: uppercase;">OR</p>
                    </div>
                    <div>
                        <label for="resumeUpload">Parse Resume:</label>
                        <input type="file" id="resumeUpload" accept=".pdf,.doc,.docx" onchange="parseResume()">
                        
                    </div>
                    
                    <div>
            
            <!--<button style ="margin-left: -40px; background-color: black; outline: none; border: none; border-radius: 5px; color: white; padding: 5px 5px; " onclick="parseResume();"> Parse </button>-->

                    </div>
                </div>
<script>
document.getElementById('importButton').addEventListener('click', function() {
	const profileUrl = document.getElementById('import_profile').value;
	if (!profileUrl) {
		alert('Please enter a LinkedIn profile URL');
		return;
	}

	const encodedUrl = encodeURIComponent(profileUrl);
	const xhr = new XMLHttpRequest();
	xhr.withCredentials = true;

	xhr.addEventListener('readystatechange', function () {
		if (this.readyState === this.DONE) {
			const response = JSON.parse(this.responseText);
			if (response.data) {
				// Populate form fields with the retrieved data
				document.getElementById('name').value = response.data.full_name || '';
				document.getElementById('email').value = response.data.email || '';
				document.getElementById('telephone').value = response.data.phone || '';
				document.getElementById('gender').value = ''; // LinkedIn API doesn't provide gender
				document.getElementById('location').value = response.data.location || '';
				document.getElementById('preferred_role').value = response.data.headline || '';
				document.getElementById('experience').value = response.data.experiences.map(exp => exp.title + ' at ' + exp.company).join(', ') || '';
				
				// Populate skills
				const skillsSelect = document.getElementById('skills');
				if (Array.isArray(response.data.skills)) {
					response.data.skills.forEach(skill => {
						const option = new Option(skill, skill, true, true);
						skillsSelect.appendChild(option);
					});
					$(skillsSelect).trigger('change'); // Refresh Select2
				}

				document.getElementById('education').value = response.data.educations.map(edu => edu.degree + ' from ' + edu.school).join('\n') || '';
				document.getElementById('certifications').value = response.data.certifications.map(cert => cert.name).join('\n') || '';
				
				// Populate languages
				const languagesSelect = document.getElementById('languages');
				if (response.data.languages) {
					response.data.languages.split(',').forEach(language => {
						const option = new Option(language.trim(), language.trim(), true, true);
						languagesSelect.appendChild(option);
					});
					$(languagesSelect).trigger('change'); // Refresh Select2
				}

				document.getElementById('availability').value = ''; // LinkedIn API doesn't provide availability
				document.getElementById('salary_expectation').value = ''; // LinkedIn API doesn't provide salary expectation
				document.getElementById('portfolio_url').value = ''; // LinkedIn API doesn't provide portfolio URL
				document.getElementById('linkedin_profile').value = response.data.linkedin_url || '';
				document.getElementById('contact_references').value = ''; // LinkedIn API doesn't provide references
				document.getElementById('other_information').value = response.data.about || '';
				document.getElementById('preferred_working_arrangement').value = ''; // LinkedIn API doesn't provide working arrangement preference
			} else {
				alert('Failed to retrieve profile data. Please try again.');
			}
		}
	});

	xhr.open('GET', `https://fresh-linkedin-profile-data.p.rapidapi.com/get-linkedin-profile?linkedin_url=${encodedUrl}&include_skills=true&include_certifications=true&include_publications=false&include_honors=false&include_volunteers=false&include_projects=false&include_patents=false&include_courses=false&include_organizations=false&include_company_public_url=false`);
	xhr.setRequestHeader('x-rapidapi-key', '18bedc1cebmsh3b384795340dd86p125cc0jsn03c7fa812fea');
	xhr.setRequestHeader('x-rapidapi-host', 'fresh-linkedin-profile-data.p.rapidapi.com');

	xhr.send();
});
</script>



                <form action="submit_talent.php" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault();">
                    <div id="personal-info">
                        
                    <input type="hidden" value="<?php echo $talentId;?>" name="talent_id">
                        
                          <div class="form-group">
                            <label for="profile_picture">Profile Picture:</label>
                            <input type="file" id="profile_picture" name="profile_picture">
                        </div> 
                        
                        
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" value ="<?php echo $fullName; ?> " required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
                        </div>
                        
                          <div class="form-group">
                            <label for="telephone">Telephone (Whatsapp preffered):</label>
                            <input type="tel" id="telephone" name="telephone" placeholder="e.g +2349151294786" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="location">Preferred Location(s):</label>
                            <input type="text" id="location" name="location" placeholder="Enter locations separated by commas" required>
                        </div>

                   <div class="form-group">
    <label for="visasponsorship">Do you require visa sponsorship for <br> any of the locations you indicated?</label>
    <select id="visasponsorship" class="form-control" name="visasponsorship">
        <option value="" disabled>Select an option</option>
        <option value="yes">Yes</option>
        <option value="no">No</option>
    </select>
</div>



                    </div>

                    <div id="professional-info">
                        <div class="form-group">
                            <label for="preferred_role">Preferred Role:</label>
                            <input type="text" id="preferred_role" name="preferred_role" required>
                        </div>

                        <div class="form-group">
                            <label for="experience">Experience:</label>
                            <input type="text" id="experience" name="experience" required>
                        </div>

                        <div class="form-group">
                            <label for="resume_file">Resume:</label>
                            <input type="file" id="resume_file" name="resume_file">
                        </div>
<div class="form-group">
<label for="skills">Skills (Type your skills & press enter):</label>
<select id="skills" name="skills[]" class="select2" multiple="multiple" required>
  <option value="JavaScript">JavaScript</option>
  <option value="Python">Python</option>
  <option value="Java">Java</option>
  <option value="C++">C++</option>
  <option value="HTML">HTML</option>
  <option value="CSS">CSS</option>
  <option value="React">React</option>
  <option value="Node.js">Node.js</option>
  <option value="SQL">SQL</option>
  <option value="Git">Git</option>
  <option value="Docker">Docker</option>
  <option value="AWS">AWS</option>
  <option value="Machine Learning">Machine Learning</option>
  <option value="Data Analysis">Data Analysis</option>
  <option value="Agile Methodologies">Agile Methodologies</option>
  <option value="Project Management">Project Management</option>
  <option value="UI/UX Design">UI/UX Design</option>
  <option value="RESTful APIs">RESTful APIs</option>
  <option value="TypeScript">TypeScript</option>
  <option value="DevOps">DevOps</option>
</select>
</div>

<script>
//   const apiKey = 'zQ3rZjnRhWBrugzj3dnJMIl9q1948fRc';
//   const apiUrl = 'https://api.apilayer.com/skills';

//   $(document).ready(function() {
//     initializeSelect2();
//   });

//   function initializeSelect2() {
//     $('#skills').select2({
//       ajax: {
//         url: apiUrl,
//         headers: {
//           'apikey': apiKey
//         },
//         dataType: 'json',
//         delay: 250,
//         data: function (params) {
//           return {
//             q: params.term
//           };
//         },
//         processResults: function (data) {
//           return {
//             results: data.map(skill => ({
//               id: skill.name,
//               text: skill.name
//             }))
//           };
//         },
//         cache: true
//       },
//       minimumInputLength: 1,
//       placeholder: 'Search for skills',
//       allowClear: true,
//       tags: true,
//       tokenSeparators: [',', ' '],
//       createTag: function (params) {
//         return {
//           id: params.term,
//           text: params.term,
//           newTag: true
//         }
//       }
//     });
//   }
</script>
                        <div class="form-group">
                            <label for="education">Education:</label>
                            <textarea id="education" name="education"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="certifications">Certifications:</label>
                            <textarea id="certifications" name="certifications"></textarea>
                        </div>
<div class="form-group">
    <label for="languages">Languages:</label>
    <select id="languages" name="languages[]" class="form-control select2" multiple="multiple" required>
    </select>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const languages = [
            'English', 'Spanish', 'French', 'German', 'Italian', 'Portuguese', 'Russian',
            'Chinese', 'Japanese', 'Korean', 'Arabic', 'Hindi', 'Bengali', 'Dutch',
            'Swedish', 'Norwegian', 'Danish', 'Finnish', 'Greek', 'Turkish'
        ];

        const select = document.getElementById('languages');
        languages.forEach(language => {
            const option = document.createElement('option');
            option.value = language.toLowerCase();
            option.textContent = language;
            select.appendChild(option);
        });

        // Initialize Select2
        $(select).select2({
            width: '100%',
            theme: 'classic',
            tags: true,
            createTag: function(params) {
                const term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            },
            insertTag: function(data, tag) {
                // Sanitize the input
                const sanitizedTag = DOMPurify.sanitize(tag.text);
                tag.text = sanitizedTag;
                data.push(tag);
            }
        });
    });
</script>

                    </div>

                    <div id="additional-info">
                       <div class="form-group">
                            <label for="availability">Availability:</label>
                            <select id="availability" name="availability" required>
                                <option value="Immediately">Immediately</option>
                                <option value="1 week">1 week</option>
                                <option value="2 weeks">2 weeks</option>
                                <option value="3 weeks">3 weeks</option>
                                <option value="4 weeks">4 weeks</option>
                                <option value="5 or more weeks">5 or more weeks</option>
                            </select>
                        </div>


                        <div class="salary-group">
                           <div class="form-group">
    <label for="currency">Currency:</label>
    <select id="currency" name="currency">
    </select>
</div>

<script>
    // Function to fetch currency data from API
    async function fetchCurrencies() {
        try {
            const response = await fetch('https://api.exchangerate-api.com/v4/latest/USD');
            const data = await response.json();
            const currencies = Object.keys(data.rates);
            
            const select = document.getElementById('currency');
            currencies.forEach(currency => {
                const option = document.createElement('option');
                option.value = currency;
                option.textContent = currency;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error fetching currencies:', error);
        }
    }

    // Call the function when the page loads
    document.addEventListener('DOMContentLoaded', fetchCurrencies);
</script>

                            <div class="form-group">
                                <label for="salary_expectation">Salary Expectation:</label>
                                <input type="number" id="salary_expectation" name="salary_expectation" step="0.01">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="portfolio_url">Portfolio URL:</label>
                            <input type="url" id="portfolio_url" name="portfolio_url">
                        </div>

                        <div class="form-group">
                            <label for="linkedin_profile">LinkedIn Profile:</label>
                            <input type="url" id="linkedin_profile" name="linkedin_profile">
                        </div>

                        <div class="form-group">
                            <label for="contact_references">Contact References:</label>
                            <textarea id="contact_references" name="contact_references"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="other_information">Other Information:</label>
                            <textarea id="other_information" name="other_information"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="preferred_working_arrangement">Preferred Working Arrangement:</label>
                            <select id="preferred_working_arrangement" name="preferred_working_arrangement" required>
                                <option value="Remote">Remote</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Onsite">Onsite</option>
                                <option value="Open">Open</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="submit" value="Submit">
                    </div>
                </form>
            </div> 
            
            
            
           
        <div class="displaytalents" style="display:none;">
            
            <?php include "displaytalent.php";?>
        </div>
        
         <div class="messages" style="display:none;">
            <?php //include "talentmessages.php"; ?>
        </div>
        
        
     </div> <!-- main contenta div ends here -->
    
    </div> <!-- main content div ends here -->

        
            <script>
            // Add the PDF.js library
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js';
            document.head.appendChild(script);
            
            // Add the Mammoth library
            const mammothScript = document.createElement('script');
            mammothScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.8/mammoth.browser.min.js';
            document.head.appendChild(mammothScript);
            
            // Wait for PDF.js to load before setting the worker source
            script.onload = function() {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.worker.min.js';
            };
            
function parseResume() {
    const fileInput = document.getElementById('resumeUpload');
    if (fileInput) {
        const file = fileInput.files[0];
        if (file) {
            // Create modal container
            const modal = document.createElement('div');
            modal.id = 'loaderModal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div id="loader"></div>
                    <div id="parseStatus" style="display: none; text-align: center; margin-top: 10px;"></div>
                </div>
            `;
            document.body.appendChild(modal);

            // Create and add CSS for the modal and loader
            const style = document.createElement('style');
            style.textContent = `
                #loaderModal {
                    display: none;
                    position: fixed;
                    z-index: 1000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0,0,0,0.5);
                }
                .modal-content {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                }
                #loader {
                    border: 5px solid #f3f3f3;
                    border-top: 5px solid #3498db;
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);

            // Show modal
            modal.style.display = 'block';

            const fileType = file.name.split('.').pop().toLowerCase();
            
            // Delay the parsing function by 20 seconds
            setTimeout(() => {
                if (fileType === 'pdf') {
                    parsePDF(file)
                        .then(() => {
                            showParseSuccess();
                        })
                        .catch((error) => {
                            console.error("Error parsing PDF:", error);
                            modal.style.display = 'none';
                        });
                } else if (fileType === 'docx') {
                    parseDocx(file)
                        .then(() => {
                            showParseSuccess();
                        })
                        .catch((error) => {
                            console.error("Error parsing DOCX:", error);
                            modal.style.display = 'none';
                        });
                } else {
                    console.log("Unsupported file type. Please upload a PDF or DOCX file.");
                    modal.style.display = 'none';
                }
            }, 5000); // 20 seconds delay
        }
    }
}

function showParseSuccess() {
    const modal = document.getElementById('loaderModal');
    const loader = document.getElementById('loader');
    const parseStatus = document.getElementById('parseStatus');
    
    loader.style.display = 'none';
    parseStatus.textContent = 'Resume Parsed Successfully';
    parseStatus.style.display = 'block';
    
    setTimeout(() => {
        modal.style.display = 'none';
    }, 4000); // Hide the modal after 2 seconds
}

function parsePDF(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = function(event) {
            const typedarray = new Uint8Array(event.target.result);
            pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
                let text = '';
                const numPages = pdf.numPages;
                let promises = [];
                for (let i = 1; i <= numPages; i++) {
                    promises.push(pdf.getPage(i).then(function(page) {
                        return page.getTextContent();
                    }).then(function(textContent) {
                        return textContent.items.map(item => item.str).join(' ');
                    }));
                }
                Promise.all(promises).then(function(pageTexts) {
                    text = pageTexts.join('\n');
                    console.log("Parsed PDF content:", text);
                    fillFormWithParsedData(text, 'pdf');
                    resolve(); // Resolve promise after successful parsing
                }).catch(reject); // Catch any errors while parsing pages
            }).catch(reject); // Catch errors from getDocument()
        };
        reader.onerror = reject; // Handle reader errors
        reader.readAsArrayBuffer(file);
    });
}

function parseDocx(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = function(event) {
            const arrayBuffer = event.target.result;
            mammoth.extractRawText({ arrayBuffer: arrayBuffer })
                .then(function(result) {
                    const text = result.value;
                    console.log("Parsed DOCX content:", text);
                    fillFormWithParsedData(text, 'docx');
                    resolve(); // Resolve promise after successful parsing
                })
                .catch(reject); // Catch errors from mammoth
        };
        reader.onerror = reject; // Handle reader errors
        reader.readAsArrayBuffer(file);
    });
}

            
         function fillFormWithParsedData(text, fileType) {
    // Basic information extraction using regex
    const nameMatch = text.match(/^(.{1,30})/);

               const emailMatch = text.match(/Email:\s*([^@\n]+@[^\n]+)/i);
const phoneMatch = text.match(/Telephone:\s*([+\d][^\n]+)/i);
                const locationMatch = text.match(/Location:\s*([^\n]+)/i);
                const educationMatch = text.match(/Education:\s*([^\n]+)/i);
                const experienceMatch = text.match(/Experience:\s*([^\n]+)/i);
                const skillsMatch = text.match(/Skills:\s*([^\n]+)/i);
                const genderMatch = text.match(/Gender:\s*([^\n]+)/i);
                const preferredRoleMatch = text.match(/Preferred Role:\s*([^\n]+)/i);
                const certificationsMatch = text.match(/Certifications:\s*([^\n]+)/i);
                const languagesMatch = text.match(/Languages:\s*([^\n]+)/i);
                const availabilityMatch = text.match(/Availability:\s*([^\n]+)/i);
                const salaryExpectationMatch = text.match(/Salary Expectation:\s*([^\n]+)/i);
                const portfolioUrlMatch = text.match(/Portfolio URL:\s*([^\n]+)/i);
                const linkedinProfileMatch = text.match(/LinkedIn Profile:\s*([^\n]+)/i);
                const preferredWorkingArrangementMatch = text.match(/Preferred Working Arrangement:\s*([^\n]+)/i);
                const otherInformationMatch = text.match(/Other Information:\s*([\s\S]+?)(?=\n\s*\n|\s*$)/i);
            
                // Fill the form fields
                if (nameMatch) document.getElementById('name').value = nameMatch[1].trim();
                if (emailMatch) document.getElementById('email').value = emailMatch[1].trim();
                if (phoneMatch) document.getElementById('telephone').value = phoneMatch[1].trim();
                if (locationMatch) document.getElementById('location').value = locationMatch[1].trim();
                if (educationMatch) document.getElementById('education').value = educationMatch[1].trim();
                if (experienceMatch) document.getElementById('experience').value = experienceMatch[1].trim();
                if (genderMatch) document.getElementById('gender').value = genderMatch[1].trim();
                if (preferredRoleMatch) document.getElementById('preferred_role').value = preferredRoleMatch[1].trim();
                if (certificationsMatch) document.getElementById('certifications').value = certificationsMatch[1].trim();
                if (availabilityMatch) document.getElementById('availability').value = availabilityMatch[1].trim();
                if (salaryExpectationMatch) document.getElementById('salary_expectation').value = salaryExpectationMatch[1].trim();
                if (portfolioUrlMatch) document.getElementById('portfolio_url').value = portfolioUrlMatch[1].trim();
                if (linkedinProfileMatch) document.getElementById('linkedin_profile').value = linkedinProfileMatch[1].trim();
                if (preferredWorkingArrangementMatch) document.getElementById('preferred_working_arrangement').value = preferredWorkingArrangementMatch[1].trim();
                if (otherInformationMatch) document.getElementById('other_information').value = otherInformationMatch[1].trim();
            
                // Handle skills (assuming Select2 is used)
                if (skillsMatch) {
                    const skills = skillsMatch[1].split(',').map(skill => skill.trim()).filter(skill => skill !== '');
                    const skillsSelect = $('#skills');
                    skills.forEach(skill => {
                        if (!skillsSelect.find("option[value='" + skill.replace(/'/g, "\\'") + "']").length) {
                            skillsSelect.append(new Option(skill, skill, true, true));
                        }
                    });
                    skillsSelect.trigger('change');
                }
            
                // Handle languages (assuming Select2 is used)
                if (languagesMatch) {
                    const languages = languagesMatch[1].split(',').map(language => language.trim()).filter(language => language !== '');
                    const languagesSelect = $('#languages');
                    languages.forEach(language => {
                        if (!languagesSelect.find("option[value='" + language.toLowerCase().replace(/'/g, "\\'") + "']").length) {
                            languagesSelect.append(new Option(language, language.toLowerCase(), true, true));
                        }
                    });
                    languagesSelect.trigger('change');
                }
            
                // Log the extracted name for debugging
                console.log("Extracted name:", nameMatch ? nameMatch[1].trim() : "No name found");
            }
            </script>
            
            <style>
            
            
            
            .main-contenta {
                flex: 1;
                padding: 20px;
                margin-left: 250px;
                min-height: 100vh;
                height: 100%;
            }
                        form {
                            flex: 1;
                            display: flex;
                            flex-wrap: wrap;
                            justify-content: space-between;
                            align-content: flex-start;
                            max-width: 1200px;
                            margin: 0 auto;
                            background-color: #fff;
                            margin-top: 10px;
                            padding: 30px;
                            border-radius: 12px;
                            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                            max-height: calc(100vh - 140px);
                            overflow-y: auto;
                        }
                        .form-group {
                            flex-basis: calc(30% - 20px);
                            margin-bottom: 20px;
                        }
                        label {
                            display: block;
                            margin-top: 10px;
                            font-weight: 600;
                            color: #34495e;
                            font-size: 0.9em;
                        }
                        input, select, textarea {
                            width: 100%;
                            padding: 8px;
                            margin-top: 5px;
                            border: 1px solid #bdc3c7;
                            border-radius: 6px;
                            box-sizing: border-box;
                            font-size: 0.9em;
                            transition: border-color 0.3s ease;
                        }
                        input:focus, select:focus, textarea:focus {
                            outline: none;
                            border-color: #3498db;
                            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
                        }
                        input[type="file"] {
                            border: none;
                            padding: 5px 0;
                        }
                        input[type="submit"] {
                            background-color: #3498db;
                            color: white;
                            border: none;
                            padding: 10px 18px;
                            margin-top: 20px;
                            cursor: pointer;
                            border-radius: 6px;
                            font-size: 1em;
                            font-weight: bold;
                            transition: background-color 0.3s ease, transform 0.1s ease;
                        }
                        input[type="submit"]:hover {
                            background-color: #2980b9;
                            transform: translateY(-2px);
                        }
                        input[type="submit"]:active {
                            transform: translateY(0);
                        }
                        textarea {
                            resize: vertical;
                            min-height: 70px;
                        }
                        .salary-group {
                            display: flex;
                            gap: 10px;
                        }
                        .salary-group .form-group {
                            flex-basis: calc(50% - 5px);
                        }
                        @media (max-width: 768px) {
                            .form-group {
                                flex-basis: 100%;
                            }
                            .container {
                                flex-direction: column;
                            }
                            .sidebar {
                                width: 100%;
                                position: static;
                                height: auto;
                            }
                            .main-contenta {
                                margin-left: 0;
                            }
                            .salary-group {
                                flex-direction: column;
                            }
                            .salary-group .form-group {
                                flex-basis: 100%;
                            }
                        }
                    </style>

</body>
</html>