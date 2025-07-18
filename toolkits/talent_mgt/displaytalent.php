<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
include 'db.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch talent data from the database
$sql = "SELECT * FROM talents WHERE talent_id = $talentId";
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    $talent = $result->fetch_assoc();
    echo "<h2> My Profile </h2>";
    echo "<div class='talent-profile'>";
    echo "<div class='profile-header'>";
    echo "<img src='" . htmlspecialchars($talent["profile_picture"]) . "' alt='Profile Picture' class='profile-picture'>";
    echo "<div class='header-text'>";
    echo "<h1>" . htmlspecialchars($talent["name"]) . "</h1>";
    echo "<p><a href='" . htmlspecialchars($talent["linkedin_profile"]) . "' target='_blank'>LinkedIn</a> | <a href='" . htmlspecialchars($talent["portfolio_url"]) . "' target='_blank'>Portfolio</a> | <a href='" . htmlspecialchars($talent["resume_file"]) . "' target='_blank'>Resume</a> | <a href='" . htmlspecialchars($talent["resume_file"]) . "' target='_blank'>Edit Profile</a> </p>";
    echo "<div class='share-profile'>";
    echo "<p>Share Profile:</p>";
    echo "<a href='https://www.facebook.com/sharer/sharer.php?u=" . urlencode($_SERVER['REQUEST_URI']) . "' target='_blank'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMwAAADACAMAAAB/Pny7AAAAYFBMVEU7V53///80Upvw8vdTaqfO0+R4ibf3+fslSJaapcg3VJzAx9yCkLoxUJro6/JqfLBGYKFfdKwdQ5SUoce5wdlzhbWoss+vuNMrTJiLmMAVP5NYb6mgrMzh5O7Y3OlDXaFEFf40AAADXElEQVR4nO3cUXdjQBjG8ekQZBhhgkga8f2/5bbbm+5u7Yh2vA/n+V/1xjnzO4oYg3o5O7WL3PlFpTuxvGlSld+kB/FT3RpVWOlB/FS2ULGRHsRPZWLpETDGGGOMMcYYY2xWRveZcy77nfvo/c/eWm22NFVhrHPqtb3eu0v+UdNcLt39frwWyamOK9U7p6VHOSeT3WzbHYayjNL05VPpW1EUleUwDI9DXuPvHu1skUfRH4qvK9B3jb3FTeR3bAGj7ekxU4KO0ao+zKdAY4yN789QkDFaJ0/8h2Fj9Hice9zDY2x1mXEu3gbGVvnzFlCMUc3zFFRMdlliwcS44yILJCY7LbMgYrRZcOyjYrJ8oQUQkxVLdwwexozP/ogBxmTHxTsGDqOr5TsGDpOdl+8YNIxRi09leJg+KXeE6b5hAcOY+Kl7fmyMbZ++uwTGnL9jwcKYcf59TBqVfxclSNOzeuYhE+X3a9Ge/qmSBnxO13NOzMO5rpT9KqQd83b8z7B0RmuoUU+kr35L4bYgec8/HXvNpMc4MzN6J5jycSv7xYy+k1mabGbJuPHeyxxipMvifzPV4MF0W/kne/+Z6bnMpNdeeoyzM7HnZ2aZ7AgznDZz/Psxj3ozx78fs6GTGTGwEYMaMagRgxoxqBGDGjGoEYMaMahtEWP0RL0f009tq4Wec9TJVIVvdqZoJ7dNkrZanWOq76zB+F8Cs+rhMAJThOEwAqeHcJh8T8fMRe0I061/EQqHOa//vCMYJhJ4EhUMU7Y7wjzq9Z9EBcMcBD5eFgyTr39mDodpBB7eBsN0AqtqQmHS844wEpeZYJihFVgjEArzeBWYHwiFOQicmcNhJJYIhsI0Et/IDYRJux1hIonLTDCMyFK0QJhS4AYgGGZYf2omIKbfEeYg8vXyQJhG5Ovl75h0Kt+QJzeMjjLvCIzJ/TjR3bd6dnLLo9RSVJtN5Ebfw6bRTW2L90jN/+RsS8vnt/dMczJiUCMGNWJQIwY1YlAjBjViUCMGNWJQIwY1YlAjBjViUCMGNWJQIwY1YlAjBjViUCMGNWJQIwY1YlAjBjViUCMGNWJQIwY1YlAjBjViUCMGtdAYo17XezMqMMbEqljvneHAGFuofL23OQNjbrlK13tpOCzG9OkvIr5L1KScoZYAAAAASUVORK5CYII=' alt='Share on Facebook'></a>";
    echo "<a href='https://twitter.com/intent/tweet?url=" . urlencode($_SERVER['REQUEST_URI']) . "&text=" . urlencode("Check out " . $talent["name"] . "'s profile!") . "' target='_blank'><img src='data:image/png;base64,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' alt='Share on Twitter'></a>";
    echo "<a href='https://www.linkedin.com/shareArticle?mini=true&url=" . urlencode($_SERVER['REQUEST_URI']) . "&title=" . urlencode($talent["name"] . "'s Profile") . "' target='_blank'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFwAAABcCAMAAADUMSJqAAAAYFBMVEUAd7f///8AcbQAdbYAa7KXu9nP4O3M3OsAc7Xl7/YAbrOQttdzpc5MjcH0+fuuyOBnnMkog73s9Pm0zeN9rNK80+aIsdQAaLFYlcbc5vGfv9s/icDF2uode7kof7sAYq7hDdldAAACZklEQVRoge2a27KjIBBF5RKDreL9njj//5djkokjIIZTaR5OlbsqDwFdhW2zG0ICskhmeREgqsgz+eAGy6cKQDBMOBMQVC94yzHBb/H2Aa+8sBd6RQLpB/2QDDLwxYYsyIUvuMiDAjVPtmKo6e0gARyAUx8PJLo+G6JoGKcOHQ99RP4pLCgum1/IRjlqloLCXuiIYxc90XTHg9NIhzdovsNynU3IjJUyUJnwGivqnRGVpY5gJUwnTfjtd8AHE47m9pCZ8ATrhYrUhOPNIjDiglgDxaSxJaarQ63CU9T6Csl23BOyodPiusb7jr4uYHyOmzCsauFnPUOBc/BSoL2KbfShlT0fcZHjczKY0lUFsDfFbF3Ic5uFUSllOTSX1IF/Vyz99kpFNit2GT42CpTFqvuX4/whA3TrSp7JCI3aWlPGk9L0oQscDp5ftcufQ+eh2hrTvYK4aDicGY7wP7dd9hIbdjB2N3i74/vvsR9s3Nzg2ldFmX2h4wY/VG8NOwI8tCYkApxMtqhjwK2VEQMuO49wa1x+AC8v/TT1444LtJbi6A6PORWMCdqNRldjCbozvF7nCr/ofYNlHrnCt/Ow03ttBuMK3zqIsZGS81cjV1KZGTspS7o4wtW79VpC0m/gpeqrtNXgFu9yg1/VXBOJ2k0sv+C4wTX3MN5o8g08Vqcg0/cMJ/yEn/ATfsJP+Ak/4S+4tjVGhesrs9fCjI57rf/hQoPb9i0wRhu9f2uFatva6jsHkQ57d+3Qga7i61WbRgrmveJDvyd5PfjzemTp9bDV3zExk34PuP0ezRNSMX9/KvD3d4i/QB8pnxFRoRMAAAAASUVORK5CYII=' alt='Share on LinkedIn'></a>";
    echo "</div>";
    
    echo "</div>";
    echo "</div>";
    echo "<div class='talent-info'>";
    echo "<div class='info-section'>";
    echo "<h2>Overview</h2>";
    echo "<p>" . html_entity_decode(htmlspecialchars(substr($talent["other_information"], 0, 400))) . (strlen($talent["other_information"]) > 250 ? "..." : "") . "</p>";
    echo "</div>";
    echo "<div class='info-section'>";
    echo "<h2>Contact Information</h2>";
    echo "<p> <strong> Email <br> </strong></i> " . htmlspecialchars($talent["email"]) . "</p>";
    echo "<br>";
    echo "<p><strong> Phone <br> </strong></i> " . htmlspecialchars($talent["telephone"]) . "</p>";
    echo "</div>";
    echo "<div class='info-section'>";
    echo "<h2>Professional Details</h2>";
    

    echo "<p><strong>Experience <br></strong> " . html_entity_decode(htmlspecialchars(substr($talent["experience"], 0, 400))) . (strlen($talent["experience"]) > 250 ? "..." : "") . "</p>";
    echo "<br>";
$skills = json_decode($talent["skills"], true);
echo "<p><strong>Skills</strong></p>";
echo "<div>";
foreach ($skills as $skill) {
    $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    echo "<span style='background-color: " . $color . "; color: #fff; margin: 0 5px 5px 0; padding: 2px 6px; display: inline-block; border-radius: 3px; font-size: 11px;'>" . htmlspecialchars($skill) . "</span>";
}
echo "</div>";

     echo "<br>";
    echo "<p><strong>Education <br></strong> " . html_entity_decode(htmlspecialchars(substr($talent["education"], 0, 400))) . (strlen($talent["education"]) > 250 ? "..." : "") . "</p>";
         echo "<br>";

    echo "<p><strong>Certifications:</strong> " . htmlspecialchars($talent["certifications"]) . "</p>";
         echo "<br>";

$languages = json_decode($talent["languages"], true);
echo "<p><strong>Languages:</strong></p>";
echo "<div>";
foreach ($languages as $language) {
    $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    echo "<span style='background-color: " . $color . "; color: #fff; margin: 0 5px 5px 0; padding: 2px 6px; display: inline-block; border-radius: 3px; font-size: 11px;'>" . htmlspecialchars($language) . "</span>";
}
echo "</div>";
    echo "</div>";
    echo "<div class='info-section'>";
    echo "<h2>Work Preferences</h2>";
    
    echo "<p><strong>Preferred Role <br> </strong> " . html_entity_decode(htmlspecialchars($talent["preferred_role"])) . "</p>";
         echo "<br>";
    echo "<p><strong>Availability <br> </strong> " . htmlspecialchars($talent["availability"]) . "</p>";
       echo "<br>";
    echo "<p><strong>Salary Expectation <br></strong> " . htmlspecialchars($talent["currency"]) . " " . htmlspecialchars($talent["salary_expectation"]) . "</p>";
       echo "<br>";
    echo "<p><strong>Preferred Working Arrangement <br></strong> " . htmlspecialchars($talent["preferred_working_arrangement"]) . "</p>";
       echo "<br>";
    echo "<p><strong>Visa Sponsorship <br> </strong> " . (htmlspecialchars($talent["visasponsorship"]) === 'yes' ? 'Required' : 'Not Required') . "</p>";
           echo "<br>";

        echo "<p><strong>Preferred Locations <br> </strong> " . htmlspecialchars($talent["location"]) . "</p>";

    echo "</div>";
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>Talent not found</div>";
}

$conn->close();
?>

<style>
.talent-profile {
    /*max-width: 800px;*/
    /*margin: 0 auto;*/
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    /*box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);*/
    /*font-family: Arial, sans-serif;*/
}

.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.profile-picture {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 20px;
    /*border: 2px solid #ddd;*/
}

.header-text h1 {
    margin: 0;
    font-size: 16px;
    color: #333333;
}

.header-text p {
    margin: 5px 0 0;
    font-size: 11px;
    colour: grey;
    /*color: #007bff;*/
}

.talent-info {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

.info-section {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    /*border: 1px solid #e6e6e6;*/
}

.info-section h2 {
    margin-top: 0;
    color: #333333;
    font-size: 12px;
    margin-bottom: 10px;
}

.info-section p {
    margin: 0;
    font-size: 11px;
    color: #666666;
}

.edit-button {
    text-align: center;
    margin-top: 20px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.share-profile img {
    width: 10px;
    margin-right: 10px;
    height: 10px;
    border-radius: 5px;
}
@media (min-width: 768px) {
    .talent-info {
        grid-template-columns: repeat(2, 1fr);
    }
    
}
</style>
