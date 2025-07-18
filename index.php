<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name='impact-site-verification' value='1985e7ee-bae4-44ea-89fb-c14ae1b0a3b4'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simplr - making your businesses simply simplr</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/a75a719a54.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5601720201870466" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="/simple-social-svgrepo-com.png">    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
   

    <style>
                
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: #ffffff; 
            box-sizing: border-box;
        } 

        .container {
            width: 80%; 
            margin: auto;
            overflow: hidden;
        }

        header {
            background: #333;
            color: #fff;
            padding: 20px 0;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header nav ul {
            list-style: none;
            padding: 0;
        }

        header nav ul li {
            display: inline;
            margin: 0 10px;
        }

        header nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 1.0em;
        }

        header nav ul li a.cta-button {
            background: #e8491d;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        header nav ul li a.cta-button:hover {
            background: #c73b1a;
        }
        .hero {
            position: relative;
            background: url('https://cdn.pixabay.com/photo/2015/07/17/22/43/student-849821_1280.jpg') no-repeat center center/cover;
            color: white;
            text-align: center;
            padding: 50px 0;
            overflow: hidden; 
            
            /* Ensure the pseudo-element doesn't overflow the container */
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: black;
            filter: blur(200px); 
            z-index: 1; 
        }

        .hero-content {
            position: relative;
            z-index: 1; /* Ensure content is above the blurred background */
        }


        .hero .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }


        .hero-content h1 {
            font-size: 4em;
            margin-bottom: 20px;
        }

        .hero-content p {
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .hero-content .cta-button {
            font-size: 1.2em;
        }

        .hero-image img {
            width: 300px;
            margin-left: 20px;
        }

        .features {
            background: #f4f4f4;
            padding: 50px 0;
        }

        .features h2 {
            text-align: center;
            margin-bottom: 50px;
        }

        .feature-grid {
            display: flex;
            justify-content: space-between;
        }

        .feature-item {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1;
            margin: 0 10px;
        }

        section h2 {
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 30px;
        }

        .testimonials h2 {
            text-align: center;
            margin-bottom: 50px;
        }

        .testimonial {
            background: #f9f9f9;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .testimonial .quote {
            flex: 1;
        }

        .testimonial .quote p {
            font-size: 1.4em;
            line-height: 1.6;
        }

        .testimonial .author {
            flex: 1;
            text-align: right;
        }

        .testimonial .author img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-left: 20px;
        }

        .testimonial .author p {
            font-size: 1.2em;
            margin-top: 10px;
        }
        .cta-section {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 100px 0;
        }

        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            background: #e8491d;
            color: #fff;
            text-decoration: none;
            font-size: 1.5em;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .cta-button:hover {
            background: #c73b1a;
        }

        .cta-buttondemo {
            display: inline-block;
            padding: 5px 10px;
            background: #e8491d;
            color: #fff;
            text-decoration: none;
            /*font-size: 1.5em;*/
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .cta-buttondemo:hover {
            background: #c73b1a;
        }

        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 20px 0;
        }

        footer p {
            font-size: 1.2em;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .body {
                width: auto;
            }
            .container {
                width: auto;
            }

            .hero {
                height: 500px;
                width: auto;
            }

            .hero h1 {
                font-size: 3em;
            }

            .hero p {
                font-size: 1.1em;
            }

            .testimonial {
                flex-direction: column;
                text-align: center;
            }

            .testimonial .quote, .testimonial .author {
                flex: 1;
            }

            .testimonial .author img {
                margin: 0 auto;
            }
        }

        </style>
</head>
<body>
    <header>
        <div class="container">
        <h1 class="logo">         
Simplr </h1>
<!--<img src="simple-social-svgrepo-com.png" width="40" height="40">-->
        
        <nav>
                <ul>
                    <li><a href="#toolkits" class="">Toolkits</a></li>
                    <li><a href="#use-cases" class=""> Use Cases </a></li>
                    <li><a href="#faqs">Pricing & FAQs</a></li>
                    <li><a href="talentpool.php" class=""> Careers </a></li>
                                                                <li> <a href="#" class="cta-buttondemo" onclick="openDemoModal();"> Book Demo </a></li>
          
                                                                   <li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" onclick="toggleDropdown(this)">Login <i class="fa fa-caret-down"></i></a>
    <ul class="dropdown-menu dropdown-menu-right">
        <li><a href="./users/managers/login.php">Manager's Login</a></li>
        <li><a href="./users/admin/login.html">Admin Login</a></li>
        <li><a href="./users/employees/login.php">Employee Login</a></li>
        <li><a href="./users/talents/logintalent.php">Talent Login</a></li>
    </ul>
</li>

<style>



       .modal {
  display: none;
  position: fixed;
  z-index: 99;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4);
}

.modal-content {
  background-color: #ffffff;
  margin: 5% auto;
  padding: 0;
  /*border: 1px solid #888;*/
  width: 80%;
  max-width: 800px;
  border-radius: 10px;
  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
}

.modal-header {
  padding: 10px 16px;
  background-color: #f8f8f8;
  border-bottom: 1px solid #e7e7e7;
  border-radius: 10px 10px 0 0;
}

.close {
  color: #aaa;
  float: right;
  font-size: 20px;
  font-weight: bold;
  line-height: 1;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

.modal-body {
  padding: 20px;
  max-height: 400px;
  overflow-y: auto;
}
    .dropdown-menu {
        display: none;
        position: absolute;
        background-color: black;
        min-width: 160px;
        border-radius: 10px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 999;
        right: 0;
        margin-right: 50px;
        margin-top: 10px;
        font-size: 13px;
    }
    .dropdown-menu.show {
        display: block;
    }
    .dropdown-menu li {
        display: block;
    }
    .dropdown-menu li a {
        color: white;
        padding: 10px;
        text-decoration: none;
        display: block;
        cursor: pointer;
    }
   
</style>
<script>
    function toggleDropdown(element) {
        element.nextElementSibling.classList.toggle('show');
    }

    window.onclick = function(event) {
        if (!event.target.matches('.dropdown-toggle')) {
            var dropdowns = document.getElementsByClassName("dropdown-menu");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
</script>



                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1> Business Management Toolkit, Built for Professionals</h1>
                <p>Everything you need to go agile and manage your business processes.</p>
                <!-- <a href="#" class="cta-button">Try Simplr</a> -->
                <a href="/signup" class="cta-button"> Sign Up for a 14 days Free Trial</a>
            </div>
            <div class="hero-image">
                <!-- <img src="uploads\docusign-BbSBf5uv50A-unsplash-removebg-preview.png" alt="Simplr App">
                <img src="image2.png" alt="Simplr App"> -->
            </div>
        </div>
    </section>

    <section id="toolkits" class="features toolkits">
    <script>
        document.querySelector('a[href="#toolkits"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('toolkits').scrollIntoView({
                behavior: 'smooth'
            });
        });
    </script>
    
    <style>
        
        .feature-item img {
           width: 60px; height: 60px;
        }
    </style>
    <div class="container">
        <h2>with superior logic tools</h2>
        <div class="feature-grid">
            <div class="feature-item">
                <img src="https://img.freepik.com/free-vector/bill-analysis-concept-illustration_114360-19348.jpg?ga=GA1.1.1567904732.1726138057&semt=ais_hybrid">
                <h3>Payroll Management</h3>
                <p>Efficiently manage your payroll with our intuitive and user-friendly tools.</p>
            </div>
            <div class="feature-item">
                <img src="https://img.freepik.com/free-vector/tax-preparation-concept-illustration_114360-19336.jpg?t=st=1726231546~exp=1726235146~hmac=a07e9aa907cfe78f06fc672b673ce6d8aad26b15717055681fa782e8f229b44a&w=740">
                <h3>Recruiting</h3>
                <p>Simplify and enhance your recruiting process with our integrated platform.</p>
            </div>
            <div class="feature-item">
                <img src="https://img.freepik.com/free-vector/paying-bills-concept-illustration_114360-19357.jpg?t=st=1726231895~exp=1726235495~hmac=db2f66a5d1f0fc43ad2a0fe535625c3a1e938fa6897cd84bd171b297c3d10236&w=740">
                <h3>Customer Management</h3>
                <p>Maintain strong relationships with our comprehensive customer management solutions.</p>
            </div>
        </div>
        <br>
        <div class="feature-grid">
            <div class="feature-item">
                
                <img src="https://img.freepik.com/free-vector/general-data-security-personal-information-protection-database-access-control-cyber-privacy-synchronized-gadgets-cross-platform-devices-regulation_335657-2547.jpg?ga=GA1.1.1567904732.1726138057&semt=ais_hybrid">
                <h3>Cybersecurity Console</h3>
                <p>Protect your business with our robust cybersecurity management tools.</p>
            </div>
            <div class="feature-item">
                <img src="https://img.freepik.com/free-vector/team-company-people-working-business-project-together-office-workers-meeting-flat-vector-illustration-workflow-teamwork-concept-banner-website-design-landing-web-page_74855-25316.jpg?ga=GA1.1.1567904732.1726138057&semt=ais_hybrid">
                <h3>Business Strategy</h3>
                <p>Develop and execute effective strategies with our business planning tools.</p>
            </div>
            <div class="feature-item">
                <img src="https://img.freepik.com/free-vector/scrum-board-concept-illustration_114360-1570.jpg?t=st=1726232344~exp=1726235944~hmac=19f0cc8c9e713d5175cec148014c81a71e00c90e4db771272a14cf5e02f24635&w=900">
                <h3>Project Management</h3>
                <p>Keep your projects on track with our streamlined project management platform.</p>
            </div>
        </div>
        <br>
        <div class="feature-grid">
            <div class="feature-item">
                <img src="https://img.freepik.com/free-vector/risk-management-concept-illustration_114360-6957.jpg?ga=GA1.1.1567904732.1726138057&semt=ais_hybrid">
                <h3>Risk Management</h3>
                <p>Identify and mitigate risks effectively with our risk management solutions.</p>
            </div>
            <div class="feature-item">
                <img src="https://img.freepik.com/free-vector/interaction-design-concept-illustration_114360-1442.jpg?ga=GA1.1.1567904732.1726138057&semt=ais_hybrid">
                <h3>Product Development</h3>
                <p>Innovate and manage product lifecycles seamlessly with our tools.</p>
            </div>
            <div class="feature-item">
                <img src="https://img.freepik.com/free-vector/flat-hand-drawn-multitask-business-woman_23-2148823079.jpg?ga=GA1.1.1567904732.1726138057&semt=ais_hybrid">
                <h3>Office Management</h3>
                <p>Optimize office operations with our comprehensive management tools.</p>
            </div>
            <div class="feature-item">
                <img src="https://img.freepik.com/premium-vector/business-marketing-flat-design-illustration_234169-6589.jpg?w=900">
                <h3>Sales Management</h3>
                <p>Boost your sales performance with our integrated management platform.</p>
            </div>
        </div>
    </div>
</section>

<div class="section">

<!-- <style>
   .section {
            display: flex;
            justify-content: space-around;
            padding: 50px 0;
            text-align: center;
            background-color: white;
        }

        .section div {
            max-width: 300px;
            margin: 20px;
        }

        .section h1 {
            font-size: 36px;
            color: #d50032;
            margin-bottom: 40px;
        }

        .section h2 {
            font-size: 48px;
            color: #ba1e68;
            margin-bottom: 20px;
        }

        .section h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .section p {
            font-size: 18px;
            color: #666;
            line-height: 1.5;
        }
    </style> -->
        <div style="display:none;">
            <h1>Entrepreneurship <span style="color:#ba1e68;">enthusiasts.</span></h1>
        </div>
    </div>
    <div class="section" style="display:none;">
        <div>
            <h2>01</h2>
            <h3>Payroll Management</h3>
            <p>Efficiently manage your payroll with our intuitive and user-friendly tools.</p>
        </div>
        <div>
            <h2>02</h2>
            <h3>Tinkerers</h3>
            <p>If you enjoy doing side-projects and are looking to explore new ideas with someone.</p>
        </div>
        <div>
            <h2>03</h2>
            <h3>Explorers</h3>
            <p>If you're curious about the startup world and might be interested in launching a venture down the road.</p>
        </div>
    </div>
   
    <section class="features-section">
    <h2> ...built with businesses in mind...</h2>

    <div class="bluecontainer">
        <div class="feature">
            <img src="https://cdn2.iconfinder.com/data/icons/time-management-95/64/quick_response_speed_quick_clock_service_time_response_time_management_skills-512.png" alt="Feature Image 1">
            <h3 class="feature-title">Easy Setup</h3>
            <ul class="features-list">
                <li><span class="checkmark">&#10004;</span> Quick integration with your systems</li>
                <li><span class="checkmark">&#10004;</span> Seamless onboarding process</li>
                <li><span class="checkmark">&#10004;</span> Intuitive user interface</li>
                <li><span class="checkmark">&#10004;</span> Responsive customer support</li>
                <li><span class="checkmark">&#10004;</span> Secure data handling</li>
            </ul>
        </div>

        <div class="feature">
            <img src="https://cdn2.iconfinder.com/data/icons/exoskeleton-bionic-arms-and-body-for-human/189/exoskeleton-bionic-13-512.png" alt="Feature Image 2">
            <h3 class="feature-title">Powerful Tools</h3>
            <ul class="features-list">
                <li><span class="checkmark">&#10004;</span> Access to comprehensive toolkit</li>
                <li><span class="checkmark">&#10004;</span> Advanced analytics and reporting</li>
                <li><span class="checkmark">&#10004;</span> Scalable solutions for growth</li>
                <li><span class="checkmark">&#10004;</span> Flexible pricing options</li>
                <li><span class="checkmark">&#10004;</span> Continuous updates and improvements</li>
            </ul>
        </div>

        <div class="feature">
            <img src="https://cdn3.iconfinder.com/data/icons/fluent-regular-24px-vol-5/24/ic_fluent_person_lock_24_regular-512.png" alt="Feature Image 3">
            <h3 class="feature-title">Secure and Reliable</h3>
            <ul class="features-list">
                <li><span class="checkmark">&#10004;</span> Robust security measures</li>
                <li><span class="checkmark">&#10004;</span> Reliable uptime and performance</li>
                <li><span class="checkmark">&#10004;</span> GDPR compliance</li>
                <li><span class="checkmark">&#10004;</span> Data encryption in transit and at rest</li>
                <li><span class="checkmark">&#10004;</span> Regular security audits</li>
            </ul>
        </div>
    </div>
</section>

<style>
.features-section {
    padding: 60px 0;
    text-align: center;
}

.bluecontainer {
    background-color: blue;
    display: flex;
    justify-content: space-around;
    gap: 30px;
    margin: 50px;
    flex-wrap: wrap;
    padding: 30px;
    height: 30vh;
    border-radius: 20px;
}

.feature {
    background-color: #ffffff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    width: calc(33.33% - 40px); /* Adjust based on your desired layout */
    max-width: 300px;
    margin-bottom: 30px;
}

.feature img {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    margin-bottom: 15px;
}

.feature-title {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333333;
}

.features-list {
    list-style-type: none;
    padding: 0;
    text-align: left;
}

.features-list li {
    margin-bottom: 8px;
    font-size: 0.9rem;
    color: #555555;
}

.checkmark {
    margin-right: 10px;
    color: green;
}
</style>

<section class="testimonials" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh;">
    <p style="font-size: 1.2rem; color: #666; max-width: 800px; text-align: center; margin-bottom: 30px;">Experience the power of our intuitive dashboard, designed to streamline your tasks and boost productivity.</p>
    <img src="public\images\simplr pitch.PNG" style="width: 1100px; height: 6000px; border-radius: 20px;">
</section>


<section id="use-cases" class="use-cases">
    <script>
        document.querySelector('a[href="#use-cases"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('use-cases').scrollIntoView({
                behavior: 'smooth'
            });
        });
    </script>
    <h2> with numerous use cases </h2>
      <div class="card-container">
          <button class="scroll-btn left"><</button>
          <div class="card-wrapper">
              <div class="card">
                  <h3>Streamlining HR Management</h3>
                  <p><strong>Challenge:</strong> Managing a growing workforce efficiently.</p>
                  <p><strong>Solution:</strong> Simplr's HR tools automate payroll, track attendance, and manage benefits, all within a single platform.</p>
                  <p><strong>Impact:</strong> 30% reduction in HR-related inquiries and 20% faster payroll processing.</p>
              </div>
            
              <div class="card">
                  <h3>Enhancing Project Management for Remote Teams</h3>
                  <p><strong>Challenge:</strong> Coordinating projects across multiple time zones.</p>
                  <p><strong>Solution:</strong> Simplr's project management tools centralize task tracking, milestone monitoring, and real-time collaboration.</p>
                  <p><strong>Impact:</strong> 25% increase in project completion rates and 15% faster time-to-market.</p>
              </div>
            
              <div class="card">
                  <h3>Optimizing Sales and CRM</h3>
                  <p><strong>Challenge:</strong> Managing a disconnected sales pipeline.</p>
                  <p><strong>Solution:</strong> Simplr's CRM consolidates customer data, manages leads, and provides sales performance insights.</p>
                  <p><strong>Impact:</strong> 40% increase in lead conversion and 35% improvement in customer retention.</p>
              </div>

              <div class="card">
                  <h3>Automating Financial Operations</h3>
                  <p><strong>Challenge:</strong> Managing finances efficiently for a small business.</p>
                  <p><strong>Solution:</strong> Simplr automates invoicing, tracks expenses, and provides visibility into cash flow.</p>
                  <p><strong>Impact:</strong> 50% reduction in time spent on financial tasks and 20% better cash flow stability.</p>
              </div>

              <div class="card">
                  <h3>Improving Marketing Campaigns with Data-Driven Insights</h3>
                  <p><strong>Challenge:</strong> Optimizing client campaigns using data-driven strategies.</p>
                  <p><strong>Solution:</strong> Simplr's marketing tools analyze data from various channels and provide actionable insights.</p>
                  <p><strong>Impact:</strong> 45% increase in campaign ROI and 30% improvement in client satisfaction.</p>
              </div>

              <div class="card">
                  <h3>Simplifying Compliance and Reporting</h3>
                  <p><strong>Challenge:</strong> Managing regulatory compliance and reporting for a healthcare provider.</p>
                  <p><strong>Solution:</strong> Simplr tracks regulations, automates compliance checks, and generates reports on-demand.</p>
                  <p><strong>Impact:</strong> 60% reduction in compliance tasks and improved audit readiness.</p>
              </div>

              <div class="card">
                  <h3>Scaling E-Commerce Operations</h3>
                  <p><strong>Challenge:</strong> Scaling operations for an online retailer.</p>
                  <p><strong>Solution:</strong> Simplr automates inventory tracking, order processing, and integrates customer support channels.</p>
                  <p><strong>Impact:</strong> 35% increase in sales and 25% reduction in order processing times.</p>
              </div>

              <div class="card">
                  <h3>Boosting Productivity in Manufacturing</h3>
                  <p><strong>Challenge:</strong> Enhancing productivity and reducing downtime in manufacturing.</p>
                  <p><strong>Solution:</strong> Simplr optimizes production schedules, tracks maintenance, and monitors performance.</p>
                  <p><strong>Impact:</strong> 20% increase in production output and 15% reduction in downtime.</p>
              </div>
          </div>
          <button class="scroll-btn right">></button>
      </div>
</section>
  <style>
      .card-container {
          position: relative;
          max-width: 1000px;
          margin: 0 auto;
          overflow: hidden;
      }

      .card-wrapper {
          display: flex;
          transition: transform 0.3s ease;
      }

      .card {
          background-color: #ffffff;
          border-radius: 10px;
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
          width: 300px;
          flex: 0 0 300px;
          margin: 20px 10px;
          padding: 20px;
          transition: transform 0.3s ease;
      }

      .card:hover {
          transform: translateY(-10px);
      }

      .card h3 {
          font-size: 1.2em;
          margin-bottom: 10px;
          color: #333;
      }

      .card p {
          font-size: 0.9em;
          color: #666;
          line-height: 1.5;
          margin-bottom: 10px;
      }

      strong {
          color: #333;
      }

      .scroll-btn {
          position: absolute;
          top: 50%;
          transform: translateY(-50%);
          background-color: rgba(0, 0, 0, 0.5);
          color: white;
          border: none;
          border-radius: 10px;
          padding: 10px;
          cursor: pointer;
          font-size: 18px;
          z-index: 1;
      }

      .scroll-btn.left {
          left: 10px;
      }

      .scroll-btn.right {
          right: 10px;
      }
  </style>

  <script>
      const cardWrapper = document.querySelector('.card-wrapper');
      const leftBtn = document.querySelector('.scroll-btn.left');
      const rightBtn = document.querySelector('.scroll-btn.right');
      const cardWidth = 320; // card width + margin
      const visibleCards = 3;
      let currentPosition = 0;
      const totalCards = cardWrapper.children.length;

      leftBtn.addEventListener('click', () => {
          currentPosition = (currentPosition - 1 + totalCards) % totalCards;
          updatePosition();
      });

      rightBtn.addEventListener('click', () => {
          currentPosition = (currentPosition + 1) % totalCards;
          updatePosition();
      });

      function updatePosition() {
          cardWrapper.style.transform = `translateX(-${currentPosition * cardWidth}px)`;
      }
  </script>



    <section id="testimonials" class="testimonials">
    <div class="container">
            <h2>and great reviews from business people like you </h2>
            <div class="testimonial">
                <div class="quote">
                    <i class="fas fa-quote-left"></i>
<p>Found Simplr through <a href="https://www.producthunt.com/posts/simplr" style="color: grey; text-decoration: none;"> Product Hunt </a> and it looks like a solid tool. One quick thing, are there any plans to expand the integration options? Would love to see more flexibility on that front. Keep up the great work!</p>
         
                </div>
                <div class="author">
                    <!-- <img src="avatar1.jpg" alt="Customer Avatar"> -->
                    <img src="https://ph-avatars.imgix.net/7256913/0a5fbaf2-95fd-42e9-8816-d2cda242f766.png?auto=compress&codec=mozjpeg&cs=strip&auto=format&w=120&h=120&fit=crop&dpr=2" alt="Client 1">
                    <p><strong>Rowan Maxwell Everett</strong><br>Cofounder of ByteCraft</p>
                </div>
            </div>
            <!--<div class="testimonial">-->
            <!--    <div class="quote">-->
            <!--        <i class="fas fa-quote-left"></i>-->
            <!--        <p>Great experience. I love how seamless it is.</p>-->
            <!--    </div>-->
            <!--    <div class="author">-->
                    <!-- <img src="avatar2.jpg" alt="Customer Avatar"> -->
            <!--        <img src="https://cdn.pixabay.com/photo/2015/05/01/19/40/girl-748932_1280.jpg" alt="Client 1">-->

            <!--        <p><strong>Pius Iniobong</strong><br>Founder, XYZ Inc.</p>-->
            <!--    </div>-->
            <!--</div>-->
            
            
                                    <a href="#" id="showMoreReviews" style="color: grey;">+ 8 more great reviews</a>

                  <div id="additionalTestimonials" style="display: none; opacity: 0; transition: opacity 0.5s ease-in-out;">
                      <div class="testimonial">
                          <div class="quote">
                              <i class="fas fa-quote-left"></i>
                              <p>Great experience. I love how seamless it is.</p>
                          </div>
                          <div class="author">
                              <img src="https://cdn.pixabay.com/photo/2015/05/01/19/40/girl-748932_1280.jpg" alt="Client 1">
                              <p><strong>Pius Iniobong</strong><br>Founder, XYZ Inc.</p>
                          </div>
                      </div>
                      <div class="testimonial">
                          <div class="quote">
                              <i class="fas fa-quote-left"></i>
                              <p>Exceptional service! Highly recommended.</p>
                          </div>
                          <div class="author">
                              <img src="https://cdn.pixabay.com/photo/2016/11/18/19/07/happy-1836445_1280.jpg" alt="Client 2">
                              <p><strong>Sarah Johnson</strong><br>CEO, ABC Corp.</p>
                          </div>
                      </div>
                      <div class="testimonial">
                          <div class="quote">
                              <i class="fas fa-quote-left"></i>
                              <p>The product exceeded my expectations. Will definitely use again!</p>
                          </div>
                          <div class="author">
                              <img src="https://cdn.pixabay.com/photo/2017/08/01/01/33/beanie-2562646_1280.jpg" alt="Client 3">
                              <p><strong>John Smith</strong><br>Marketing Director, DEF Ltd.</p>
                          </div>
                      </div>
                  </div>

                  <script>
                  document.getElementById('showMoreReviews').addEventListener('click', function(e) {
                      e.preventDefault();
                      var additionalTestimonials = document.getElementById('additionalTestimonials');
                      additionalTestimonials.style.display = 'block';
                      setTimeout(function() {
                          additionalTestimonials.style.opacity = '1';
                      }, 10);
                      this.style.display = 'none';
                  });
                  </script>
                  
                  </div>
<!-- 
        <div class="container">
            <h2>What Our Clients Say</h2>
            <div class="testimonial-grid">
                <div class="testimonial">
                    <div class="quote">
                    <i class="fas fa-quote-left"></i>
                        <p>"Simplr has transformed the way we manage our business processes."</p>
                    </div>


               
                    <div class="author">
                        <img src="https://cdn.pixabay.com/photo/2015/05/01/19/40/girl-748932_1280.jpg" alt="Client 1">
                        <p>Mariam Rapheal, 
                            <br>CEO of SoftBrushes</p>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="quote">
                        <p>"The best investment we've made for our company."</p>
                    </div>
                    <div class="author">
                        <img src="avatar2.png" alt="Client 2">
                        <p>Client 2, CFO of Another Company</p>
                    </div>
                </div>
                 Add more testimonials as needed 
            </div>
        </div> -->
        
     
    </section>
    
    <script>
        document.querySelector('a[href="#faqs"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('faqs').scrollIntoView({
                behavior: 'smooth'
            });
        });
    </script>
    
  
    <div class="faq-section" id="faqs">
    <div class="faq-header">
        <h1>Still Curious? <br> We've got you!</h1>
<p> Find the answers you need <span style="position: relative; top: -5px;">👉</span></p>



        <div class="faq-footer">
        <p>Still have a question that needs a human touch? <br> Send a message to <a href="mailto:support@simplr.com.ng" style="color:black;">support@simplr.com.ng</a></p>
    </div>
    </div>
    <div class="faq-content">
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Is Simplr a productivity tool or app?</button>
            <div class="faq-answer">
            Simplr is an ultra heavyweight and business logic software.
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Is Simplr free?</button>
            <div class="faq-answer">
                <!-- Answer content here -->
                 Nah, but try out our 14 days free trial version.
            </div>
        </div>
        <div class="faq-item">
    <button class="faq-question" onclick="toggleFaq(this)">How much does it cost to use Simplr?</button>
    <div class="faq-answer">
  <p>Simplr offers a <strong>14-day free trial</strong> for all tools.</p>
  <p>After the trial, our <strong>Premium plan</strong> is priced at:</p>
  <ul>
    <li><strong>$21.37 USD/month</strong> for all tools</li>
    <li><strong>$256.40 USD/year</strong> (billed annually)</li>
  </ul>
  <p>You can message support to help determine <br> the right tools for your business. 
     <a href="your-support-link" style="color: #808080;">Contact support</a>.
  </p>
</div>

    </div>

        <div class="faq-item">
  <button class="faq-question" onclick="toggleFaq(this)">How secure is my business information on Simplr?</button>
  <div class="faq-answer">
    <p>Your business information on Simplr is secured with:</p>
    <ul>
      <li>AES-256 encryption</li>
      <li>Bcrypt hashing</li>
      <li>Two-Factor Authentication (2FA)</li>
      <li>Regular security audits</li>
      <li>Strict access control</li>
      <li>Data backups</li>
      <li>Compliance with data protection regulations</li>
    </ul>
    <p>Super encrypted, thoroughly hashed, and leak-proof.</p>
  </div>
</div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How does Simplr work?</button>
            <div class="faq-answer">
                <!-- Answer content here -->
                 Simplr revloutionizes how you think through & manage business logic <br> by providing a suite of tools and integrations,  
                 from one dashboard <br> covering recruitment, sales, payroll, accounting and lots more.
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Can I operate my multiple accounts on Simplr?</button>
            <div class="faq-answer">
                <!-- Answer content here -->
                 Yes, you can create multiple business accounts as you have <br> and switch betweeen any of your busineses at will.
            </div>
        </div>
    </div>
    
</div>



  <style>
         
          .input-container {
              position: relative;
              margin-bottom: 15px;
          }
          label {
              position: absolute;
              left: 8px;
              color: #667;
              top: 50%;
              transform: translateY(-50%);
              font-size: 10px;
              pointer-events: none;
              transition: 0.2s ease all;
              padding: 0 4px;
              background-color: white;
          }
          input[type="text"],
          input[type="email"],
          input[type="password"], 
          input[type="date"], 
          input[type="file"],
          input[type="tel"],
          input[type="time"],
          input[type="number"],
          select, textarea{
              width: 100%;
              padding: 12px 8px 8px;
              border: 1px solid #ddd;
              border-radius: 4px;
              box-sizing: border-box;
          }
          
          input:focus + label,
          input:not(:placeholder-shown) + label,
          select:focus + label,
          select:not(:placeholder-shown) + label,
          textarea:focus + label,
          textarea:not(:placeholder-shown) + label{
              top: 0;
              color: grey;
          }
          
         
           
          input[type="submit"] {
              width: 100%;
              padding: 10px;
              background-color: #4CAF50;
              color: white;
              border: none;
              border-radius: 4px;
              cursor: pointer;
              font-size: 11px;
          }
          input[type="submit"]:hover {
              background-color: #45a049;
          }
      </style>
<div id="createDemoModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">x</span>
      <h2>Book Demo</h2>
    </div>
    <div class="modal-body">
     
             <form id="demorequestform" method="POST">
             <div class="input-container">
               <input type="text" id="name" name="name" required>
                              <label for="name">Full Name:</label>

             </div>
             <div class="input-container">
               <input type="email" id="email" name="email" required>
                              <label for="email">Email:</label>

             </div>
             <div class="input-container">
               <input type="text" id="companyname" name="company">
                              <label for="company">Company Name:</label>

             </div>
             
             
             
             <div class="input-container">
               <input type="tel" id="phone" name="phone">
                              <label for="phone">Phone Number:</label>

             </div>
             
              <div class="input-container">
               <select id="preferred_contact_method" name="preferred_contact_method">
                   <option value="email">Email</option>
                   <option value="phone">Phone</option>
               </select>
               <label for="preferred_contact_method">Preferred Contact Method:</label>
                </div>
                
                <div class="input-container">
                               <input type="text" id="industry" name="industry">
                               <label for="industry">Industry:</label>
                </div>
                
                <div class="input-container">
                               <input type="number" id="number_of_employees" name="number_of_employees">
                               <label for="number_of_employees">Number of Employees:</label>
                </div>
                
                <div class="input-container">
                               <input type="text" id="heard_from" name="heard_from">
                               <label for="heard_from">How did you hear about us?</label>
                </div>

             <div class="input-container">
               <input type="date" id="date" name="date">
                              <label for="date">Date:</label>

             </div>
             
             <div class="input-container">
               <input type="time" id="time" name="time">
                              <label for="time">Time (GMT +1.00):</label>

             </div>
             <div class="input-container">
               <textarea id="message" name="message" rows="4"></textarea>
                              <label for="message">Additional Information:</label>

             </div>
        <input type="submit" value="Request Demo">
           </form>
     
    </div>
  </div>
</div>



<style>
    .faq-section {
    background-color: #ffffff;
    padding: 50px 20px;
    text-align: left;
    display: flex;
    gap: 150px;
    font-family: Arial, sans-serif;
}

.faq-header {
    text-align: left;
    margin-left: 120px;
    margin-bottom: 20px;
}

.faq-header h1 {
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 10px;
}

.faq-header p {
    font-size: 1.2em;
    color: #555;
}

.faq-content {
    margin-bottom: 20px;
}

.faq-item {
    margin-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.faq-question {
    font-size: 1.1em;
    color: #333;
    cursor: pointer;
    background: none;
    border: none;
    text-align: left;
    width: 100%;
    padding: 10px 0;
    font-family: inherit;
    font-weight: bold;
}

.faq-question:focus {
    outline: none;
}

/*.faq-answer {*/
/*    display: none;*/
/*    padding: 10px 0;*/
/*    color: #555;*/
/*    font-size: 1em;*/
/*}*/

.faq-footer {
    font-size: 1em;
    color: #555;
    margin-top: 60px;
}

.faq-footer a {
    color: #0073e6;
    text-decoration: none;
}

.faq-footer a:hover {
    text-decoration: underline;
}

    </style>
    <section class="cta-section">
        <div class="container">
            

        <p> Get all the support you need to keep your business smart </p>

            <h2>Ready to Transform Your Business?</h2>
            <a href="/users/admin/signup.php" class="cta-button">Get Started Now</a>

        </div>
    </section>

<footer>
    
   <section class="testimonials" style="display: flex; flex-direction: column; align-items: center; margin-left: 5%; margin-right: 5%;">
    <p style="margin-bottom: -30px; font-size: 14px;">Support Simplr by giving us an upvote.</p>

    <a href="https://www.producthunt.com/posts/simplr?embed=true&utm_source=badge-featured&utm_medium=badge&utm_souce=badge-simplr" target="_blank"><img src="https://api.producthunt.com/widgets/embed-image/v1/featured.svg?post_id=484064&theme=light" alt="Simplr - making your businesses simply simplr | Product Hunt" style="width: 150px; height: 150px;" width="250" height="10" /></a>
</section>
    <div class="container">
        <ul style="list-style-type: none; padding: 0; display: flex; flex-wrap: wrap; justify-content: space-between;">
            <li><a href="#" style="color: grey; text-decoration: none; margin-right: 15px;">Careers</a></li>
            <li><a href="#" style="color: grey; text-decoration: none; margin-right: 15px;">Privacy Policy</a></li>
            <li><a href="#" style="color: grey; text-decoration: none; margin-right: 15px;">Terms of Service</a></li>
            <li><a href="#" style="color: grey; text-decoration: none; margin-right: 15px;">Contact Us</a></li>
            <li><a href="#" style="color: grey; text-decoration: none; margin-right: 15px;">About Us</a></li>
                        <li><a href="#" style="color: grey; text-decoration: none; margin-right: 15px;">Developers</a></li>
                        <li><a href="#" style="color: grey; text-decoration: none; margin-right: 15px;">Documentation</a></li>

            <li><a href="#" style="color: grey; text-decoration: none;">FAQ</a></li>
        </ul>
<p style="font-size: 0.8em; margin-top: 20px;">© 2024 Simplr. All rights reserved.</p>    </div>
</footer> 

<!-- end of main index page -->





    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script> function toggleFaq(element) {
    var answer = element.nextElementSibling;
    var isVisible = answer.style.maxHeight && answer.style.maxHeight !== '0px';

    // Hide all answers
    document.querySelectorAll('.faq-answer').forEach(function(el) {
        el.style.maxHeight = null; // Reset max-height
    });

    // Toggle the clicked answer
    if (!isVisible) {
        answer.style.maxHeight = answer.scrollHeight + 'px'; // Set to full height
    } else {
        answer.style.maxHeight = '0px'; // Collapse
    }
}

</script>


<style>
    
    .faq-answer {
    max-height: 0;
    overflow: hidden;
    color: #555;
    margin-bottom: 10px;
    font-size: 1em;
    transition: max-height 0.4s ease-in-out;
}

</style>
<!-- 
<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.13.1/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.13.1/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyCI__9Kay3eVjzhqT7CZQ6r9b3JYZy6dkU",
    authDomain: "simplr-business-suite.firebaseapp.com",
    projectId: "simplr-business-suite",
    storageBucket: "simplr-business-suite.appspot.com",
    messagingSenderId: "468904396749",
    appId: "1:468904396749:web:a5ed6eee150b35b168a684",
    measurementId: "G-DY6RVXNL6V"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
</script> -->

<script>
  function openDemoModal() {
    var demoModal = document.getElementById("createDemoModal");
    demoModal.style.display = "block";
  }
  
  var demoModal = document.getElementById("createDemoModal");
  var demoSpan = document.querySelector("#createDemoModal .close");
  
  // When the user clicks on <span> (x), close the respective modal
  if (demoSpan) {
    demoSpan.addEventListener("click", function() {
      demoModal.style.display = "none";
    });
  }
  
  window.addEventListener("click", function(event) {
    if (event.target == demoModal) {
      demoModal.style.display = "none";
    }
  });
</script>
<script>
    document.getElementById('demorequestform').addEventListener('submit', function(e) {
        e.preventDefault();
          
        var demorequestData = new FormData(this);
          
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'bookdemo.php', true);
          
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Request successful
                alert(xhr.responseText);
                // Handle the response as needed
                
                // Clear the form
                document.getElementById('demorequestform').reset();
                
                // Close the modal
                var modal = document.getElementById('createDemoModal');
                modal.style.display = 'none';
            } else {
                // Request failed
                console.error('Error:', xhr.status);
            }
        };
          
        xhr.onerror = function() {
            console.error('Request failed');
        };
          
        xhr.send(demorequestData);
    });
</script>
</body>
</html>
