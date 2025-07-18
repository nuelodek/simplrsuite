<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simplr Mobile Homepage</title>
    <link rel="stylesheet" href="styles.css">
    
    
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: #333;
}

h1, h2, h3 {
    color: #2a2a2a;
    margin: 0.5em 0;
}

ul {
    padding-left: 1.5em;
}

p, li {
    font-size: 1em;
    line-height: 1.5em;
}

a {
    color: #0066cc;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Header */
header {
    background-color: #4caf50;
    background: url('https://cdn.pixabay.com/photo/2015/07/17/22/43/student-849821_1280.jpg') no-repeat center center/cover;
    color: white;
    text-align: center;
    padding: 1.5em 1em;
}


header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 50%;
    height: 30%;
    background: black;
    filter: blur(200px); /* Adjust the blur intensity as needed */
    z-index: 1; /* Ensure the blurred background stays behind the content */
}

.cta-button {
    background-color: #ff5722;
    color: white;
    border: none;
    padding: 1em 1.5em;
    font-size: 1em;
    cursor: pointer;
    margin: 1em 0;
    border-radius: 5px;
}

.cta-button:hover {
    background-color: #e64a19;
}

.subtext {
    font-size: 0.9em;
    margin-top: -0.5em;
}

/* Features Section */
.features {
    background-color: #ffffff;
    padding: 1.5em;
    margin: 1em 0;
}

.features h2 {
    font-size: 1.5em;
    margin-bottom: 1em;
}

.features ul {
    list-style: disc;
    margin: 0;
    padding-left: 1.5em;
}

.features li {
    margin-bottom: 0.5em;
}

/* Why Simplr Section */
.why-simplr {
    background-color: #e0e0e0;
    padding: 1.5em;
    margin: 1em 0;
}

.why-simplr h2 {
    font-size: 1.5em;
    margin-bottom: 1em;
}

.feature-block {
    margin-bottom: 1.5em;
}

.feature-block h3 {
    font-size: 1.2em;
    margin-bottom: 0.5em;
}

.feature-block ul {
    list-style: disc;
    margin: 0;
    padding-left: 1.5em;
}

/* Use Cases Section */
.use-cases {
    background-color: #ffffff;
    padding: 1.5em;
    margin: 1em 0;
}

.use-cases h2 {
    font-size: 1.5em;
    margin-bottom: 1em;
}

.use-case {
    margin-bottom: 1em;
}

.use-case h3 {
    font-size: 1.2em;
    margin-bottom: 0.5em;
}

/* Reviews Section */
.reviews {
    background-color: #e0e0e0;
    padding: 1.5em;
    margin: 1em 0;
}

.reviews h2 {
    font-size: 1.5em;
    margin-bottom: 1em;
}

blockquote {
    font-style: italic;
    margin: 0;
    padding: 0.5em;
    border-left: 5px solid #ff5722;
    background-color: #ffffff;
    padding-left: 1em;
}

cite {
    display: block;
    font-size: 0.9em;
    margin-top: 0.5em;
    color: #555;
}

/* FAQ Section */
.faq {
    background-color: #ffffff;
    padding: 1.5em;
    margin: 1em 0;
}

.faq h2 {
    font-size: 1.5em;
    margin-bottom: 1em;
}

/* Footer */
footer {
    background-color: #4caf50;
    color: white;
    text-align: center;
    padding: 1em;
}

footer nav a {
    margin: 0 0.5em;
    color: white;
    font-size: 0.9em;
}

footer p {
    font-size: 0.8em;
    margin-top: 0.5em;
}

        
    </style>
</head>
<body>
    <header>
        <h1>Simplr</h1>
        <p>Business Management Toolkit, Built for Professionals</p>
        <button class="cta-button">Sign Up for a 14 Days Free Trial</button>
        <p class="subtext">with Superior Logic Tools</p>
    </header>

    <section class="features">
        <h2>Features</h2>
        <ul>
            <li><strong>Payroll Management:</strong> Efficiently manage your payroll with intuitive tools.</li>
            <li><strong>Recruiting:</strong> Simplify your hiring process with integrated tools.</li>
            <li><strong>Customer Management:</strong> Strengthen customer relationships with our comprehensive solutions.</li>
            <li><strong>Cybersecurity Console:</strong> Protect your business with robust security tools.</li>
            <li><strong>Business Strategy:</strong> Plan and execute effective strategies.</li>
            <li><strong>Project Management:</strong> Keep projects on track with streamlined management.</li>
            <li><strong>Risk Management:</strong> Identify and mitigate risks effectively.</li>
            <li><strong>Product Development:</strong> Innovate and manage product lifecycles seamlessly.</li>
            <li><strong>Office Management:</strong> Optimize your office operations.</li>
            <li><strong>Sales Management:</strong> Boost your sales performance.</li>
        </ul>
    </section>

    <section class="why-simplr">
        <h2>Why Simplr?</h2>
        <div class="feature-block">
            <h3>Easy Setup</h3>
            <ul>
                <li>Quick integration with your systems</li>
                <li>Seamless onboarding</li>
                <li>Intuitive UI</li>
                <li>Responsive support</li>
                <li>Secure data handling</li>
            </ul>
        </div>
        <div class="feature-block">
            <h3>Powerful Tools</h3>
            <ul>
                <li>Comprehensive toolkit</li>
                <li>Advanced analytics & reporting</li>
                <li>Scalable solutions</li>
                <li>Flexible pricing</li>
                <li>Continuous updates</li>
            </ul>
        </div>
        <div class="feature-block">
            <h3>Secure & Reliable</h3>
            <ul>
                <li>Robust security</li>
                <li>Reliable performance</li>
                <li>GDPR compliant</li>
                <li>Data encryption</li>
                <li>Regular security audits</li>
            </ul>
        </div>
    </section>

    <section class="use-cases">
        <h2>Use Cases</h2>
        <div class="use-case">
            <h3>Streamlining HR Management</h3>
            <p>30% reduction in HR inquiries</p>
            <p>20% faster payroll processing</p>
        </div>
        <div class="use-case">
            <h3>Enhancing Project Management</h3>
            <p>25% increase in project completion</p>
            <p>15% faster time-to-market</p>
        </div>
        <div class="use-case">
            <h3>Optimizing Sales & CRM</h3>
            <p>40% increase in lead conversion</p>
            <p>35% improvement in customer retention</p>
        </div>
        <div class="use-case">
            <h3>Automating Financial Operations</h3>
            <p>50% time saved on financial tasks</p>
            <p>20% better cash flow stability</p>
        </div>
        <div class="use-case">
            <h3>Improving Marketing Campaigns</h3>
            <p>45% increase in campaign ROI</p>
            <p>30% improvement in client satisfaction</p>
        </div>
    </section>

    <section class="reviews">
        <h2>Customer Reviews</h2>
        <blockquote>
            “Found Simplr through Product Hunt, and it looks like a solid tool! Would love to see more integration options. Keep up the great work!”
            <cite>- Rowan Maxwell Everett, Cofounder of ByteCraft</cite>
        </blockquote>
    </section>

    <section class="faq">
        <h2>Still Curious?</h2>
        <p>Find the answers you need 👉</p>
        <p>Got a question? Send a message to <a href="mailto:support@simplr.com.ng">support@simplr.com.ng</a></p>
    </section>

    <footer>
        <button class="cta-button">Get Started Now</button>
        <p>Support Simplr by giving us an upvote on <a href="#">Product Hunt</a>.</p>
        <nav>
            <a href="#">Careers</a> | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a> | <a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">FAQ</a>
        </nav>
        <p>&copy; 2024 Simplr. All rights reserved.</p>
    </footer>
</body>
</html>
