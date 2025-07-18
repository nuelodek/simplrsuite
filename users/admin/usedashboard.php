<?php
// Start the session
session_start();

if (!isset($_SESSION['user_id'])) {
        echo 'Page restricted. Please create/login to your <a href="follower.php"> follower</a>, <a href="creator.php">creator</a> or <a href="affiliates.php">affiliate</a> account.';
    exit();
} 
 
// If logged in, retrieve user data from the session
$userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : '';
$lastName = isset($_SESSION['lastName']) ? $_SESSION['lastName'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$telephone = isset($_SESSION['telephone']) ? $_SESSION['telephone'] : '';
$regdate = isset($_SESSION['regdate']) ? $_SESSION['regdate'] : '';
$afref = isset($_SESSION['afref']) ? $_SESSION['afref'] : ''; 
$refLink = 'https://growsocial.com.ng/creator.php?ref=' . $afref;
$fullname = $firstName . ' ' . $lastName;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script defer data-domain="growsocial.com.ng" src="https://plausible.io/js/script.js"></script>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-0LH4FW6R1P"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-0LH4FW6R1P');
</script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $firstName ?> Dashboard - Grow Social </title>
            <script src="https://cdn.ckeditor.com/ckeditor5/38.0.0/classic/ckeditor.js"></script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

        <script src="https://js.stripe.com/v3/"></script>

     <script>
    const stripe = Stripe('pk_live_51MsE7oDHi2bCzDYgvbgbuG3eNdmZD8721WQp3iJH0ZF5A6swJj8PYMmgj03nFhWNpVdI2wXcxUbsMoqyMY7se8ip00apnj2uGh');
    </script>
  <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,800;1,300;1,800&display=swap');
      </style>
   
   
   
   <style>
   
   .paybutton {
       
       background-color: black;
       color: white;
       border:none;
         border-radius: 5px;
   }
   
   .paybutton:hover {
      
       background-color: yellow;
   }
   
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

        /* Styles for the modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

.yesbutton, .nobutton  {
    
    padding: 10px;
      border-radius:4px;
    border:none;
color: whitesmoke;
    background-color: black;
    margin-right: 10px;
}
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
        }

        /* Close button style */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
   <style>
        body {
            margin: 0;
            font-family: 'Open Sans', sans-serif;
            background-color: #fff;
            padding: 0; /* Remove default padding */
            width: 100%;
        }

        .sectionful {
            display: flex;
            flex-direction: row;
            width: 100%; 
        }

        .section1 {
            width:5%; 
           
        }
        
        
        
        /* Styles for screens smaller than 600 pixels wide (common for mobile devices) */
@media only screen and (max-width: 600px) {
  /* Change the flex direction of section 1 and section 2 to column */
  .sectionful {
    display: flex;
    flex-direction: column;
    padding: 10px;
    font-size: 12px;

  }
  
  .section1 ul{
    margin-left: 35px;
  }
 
  .section2 {
    margin-left: 20px;
  }
  
  .section2 iframe {
width: 420px !important; /* Ensure the iframe doesn't exceed its container's width */
  height: 300px;
      
  }
  
  .section2 .video-iframe {
        width: 420px;
        height: 300px;
    }
  
  
  .vidtitle {
      
       width: 400px;
  }
  
  .section3 { width: 400px;
  margin-left: 20px;
  font-size: 12px;
  }
  
  .categoryclassy {
      width: 400px;
  }

  .sjsj {
      color: red;
      font-size: 10px;
      width: 400px;
  }
   .section1 ul,
  .section2 ul {
    display: flex; /* Make the list a flex container */
    flex-direction: row; /* Display list items horizontally */
    list-style-type: none; /* Remove default list styles */
    padding: 0; /* Remove default padding */
  }
}


        

 .ff {
                font-size: 14px;
            font-weight: bold;          
        padding: 10px 15px 10px 15px; 
    background-color: black; 
    border-radius:4px;
    border:none;
color: whitesmoke;}


input[type="text"], input[type="name"], select, input[type="number"], input[type="url"], input[type="date"], input[type="tel"], input[type="file"]{
    padding: 12px; 
    margin-top: 10px;
    width: 290px;
    color: black; 
    font-size: 14px;
    border-radius: 4px;
    border: 1px solid rgba(128, 128, 128, 0.589);
}

textarea {
    margin-top: 10px;
    width:80%;
    color: black; 
    font-size: 14px;
    border-radius: 4px;
    border: 1px solid rgba(128, 128, 128, 0.589);
}

label {
    font-weight: bold;
    font-size: 14px;
}
        .section2 {
            width: 47%;
        }

        .section3 {
            width: 42%;
        }

         .emdk li {
            margin: 10px; /* Adjust the margin as needed */
            list-style-type: none;

        }

        
        .vid1d {

        width: 30px;
        height: auto; /* Maintain aspect ratio */
        position: relative;
        right: 30px;
      } 

      .section3 button {
                    border: 1px solid black;
                    cursor: pointer;
                    padding: 5px;
                    background-color: rgba(255, 255, 255, 0.5); /* Semi-transparent white background */
               }

               .jskdj {width: 10px;
               height: 10px;
               
               }

               .colorclassy {
                color:burlywood;
               }

</style>
</head>

        <body>
 
<div class="sectionful">

<div class="section1">


    <ul class="emdk"> 
    
    
    <li> <img src="black_white_grow_logo__1_-removebg-preview.png" class="vid1d"> </li>


<?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch email from session
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

$sql = "SELECT * FROM growsignup WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the profile picture URL from the database result
    $row = $result->fetch_assoc();
    $profilepictureJSON = $row["profilepicture"];
    $bio = $row["bio"];

    // Decode the JSON string to get an array of profile picture URLs
    $profilepictureArray = json_decode($profilepictureJSON, true);
    
    // Check if the array is not empty and contains at least one URL
    if (!empty($profilepictureArray) && is_array($profilepictureArray)) {
        // Output the first profile picture URL
        $profilepicture = $profilepictureArray[0];
    } else {
        // Output default profile picture URL if no profile picture found
        $profilepicture = "default_profile.png";
    }
} else {
    // Output default profile picture URL if user not found
    $profilepicture = "default_profile.png";
}

$conn->close();
?>
<li>
    <a href="#" onclick="tp()" class="profilepicture">
        <img id="profileImg" src="<?php echo $profilepicture; ?>" class="vid1d"` style="border-radius: 50%;"> <!-- Set a default profile picture -->
    </a>
</li>

<?php if ($userType === 'follower') : ?>
        
        <li> <a href="#" onclick="ttester()"  class="webtest"> <img src="webbug.png" class="vid1d"></a></li>
        <li> <a href="#" onclick="tfsurvey()"  class="surveyfollower"> <img src="surveycheck1.webp" class="vid1d"></a></li>
        <li> <a href="#" onclick="tfcart()"  class="followermarket"> <img src="cart.webp" class="vid1d"></a></li>
        <li> <a href="#" onclick="twebd()"  class="klol"> <img src="weblinka.png" class="vid1d"></a></li>
        <li> <a href="#" onclick="tv()"  class="video"> <img src="2849799_multimedia_computer_pc_screen_monitor_icon.png" class="vid1d"></a></li>
       <li> <a href="#" onclick="tfollow()"  class="followthem"> <img src="multimedia-10-256.webp" class="vid1d"> </a>  </li>

<?php endif; ?>


<?php if ($userType === 'curator') : ?>
        <li> <a href="#" onclick="blog()"  class="profile"> <img src="multimedia-43-64.webp" class="vid1d">  </a></li>
        <li> <a href="#" onclick="tgf()"  class="getfollower"> <img src="8577677.png" class="vid1d">  </a></li>
        <li> <a href="#" onclick="ta()"  class="followrequest"> <img src="3313055.png" class="vid1d">  </a></li>
        <li> <a href="#" onclick="tcsurvey()"  class="survey"> <img src="usertesting.png" class="vid1d"></a></li>
        <li> <a href="#" onclick="tusertest()"  class="usertesting"> <img src="usertester.png" class="vid1d"></a></li>
        <li> <a href="#" onclick="tmark()"  class="marketplace"> <img src="marketplace1.png" class="vid1d"></a></li>
         <li> <a href="#" onclick="tcont()"  class="marketplace"> <img src="6762838.png" class="vid1d"></a></li> 
        
        
        <li> <a href="#" onclick="tu()" class="upload"><img src="2849798_upload_multimedia_icon.png" class="vid1d"> </a> </li>
        <li> <a href="#" onclick="tf()"  class="contacts"> <img src="friends.png" class="vid1d"></a></li>
        <li> <a href="#" onclick="tweb()"  class="weblink"> <img src="weblinks.webp" class="vid1d"></a></li>
<?php endif; ?>


    
        
        <li> <a href="#" onclick="tw()"  class="wallet"><img src="5686068.png" class="vid1d"> </a> </li>
         <li> <a href="#" onclick="tsup()"  class="support"><img src="support.png" class="vid1d">  </a></li>
        <li> <a href="logout.php" class="logout"><img src="9881785.png" class="vid1d">  </a></li>
        
         <?php if ($userType === 'affiliate') : ?>
        <li> <a href="#" onclick="tonboard()" class="affonboard"> <img src="affliate.png" class="vid1d"> </a></li>
        <?php endif; ?>
        
        <!--
        <li> <a href="#" onclick="tmg()" class="messages"> <img src="2849834_message_letter_multimedia_mail_communication_icon.png" class="vid1d">  </a></li>
        <li> <a href="#" onclick ="tm()" class="more"> <img src="reslk.png" class="vid1d"></a></li> -->
        
</ul>
<script>

function tonboard() {
    
          document.querySelector(".onboardaff").style.display="block";
      document.querySelector(".contactsgetter").style.display="none";
                 document.querySelector(".followclassy").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
            document.querySelector(".followthemall").style.display="none";
       document.querySelector(".webheader").style.display="none";
          document.querySelector(".webdisplay").style.display="none";
         document.querySelector(".productheader").style.display="none";
        document.querySelector(".followermarketplace").style.display="none";
        document.querySelector(".webclassy").style.display="none";
       document.querySelector(".contactclassy").style.display="none";
      document.querySelector(".viewsclassy").style.display = "none";
      document.querySelector(".videoclassy").style.display = "none";
     document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
     document.querySelector(".profileclassy").style.display = "none"; 
     document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

}





function tcont() {
              document.querySelector(".onboardaff").style.display="none";

           document.querySelector(".contactsgetter").style.display="block";
                 document.querySelector(".followclassy").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
            document.querySelector(".followthemall").style.display="none";

       document.querySelector(".webheader").style.display="none";
          document.querySelector(".webdisplay").style.display="none";
         document.querySelector(".productheader").style.display="none";
        document.querySelector(".followermarketplace").style.display="none";
        document.querySelector(".webclassy").style.display="none";
       document.querySelector(".contactclassy").style.display="none";
      document.querySelector(".viewsclassy").style.display = "none";
      document.querySelector(".videoclassy").style.display = "none";
     document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
     document.querySelector(".profileclassy").style.display = "none"; 
     document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

}
function twebd() {
              document.querySelector(".onboardaff").style.display="none";

            document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
              document.querySelector(".webheader").style.display="block";
          document.querySelector(".webdisplay").style.display="block";
         document.querySelector(".productheader").style.display="none";
        document.querySelector(".followermarketplace").style.display="none";
        document.querySelector(".webclassy").style.display="none";
       document.querySelector(".contactclassy").style.display="none";
      document.querySelector(".viewsclassy").style.display = "none";
      document.querySelector(".videoclassy").style.display = "none";
     document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
     document.querySelector(".profileclassy").style.display = "none"; 
     document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

    
    
}



function tfcart() {
              document.querySelector(".onboardaff").style.display="none";

            document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
                  document.querySelector(".webheader").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
              document.querySelector(".webdisplay").style.display="none";

          document.querySelector(".followermarketplace").style.display="none";

         document.querySelector(".productheader").style.display="block";
      document.querySelector(".followermarketplace").style.display="block";
        document.querySelector(".webclassy").style.display="none";
       document.querySelector(".contactclassy").style.display="none";
      document.querySelector(".viewsclassy").style.display = "none";
      document.querySelector(".videoclassy").style.display = "none";
     document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
     document.querySelector(".profileclassy").style.display = "none"; 
     document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

} 

function tmark() {
                  document.querySelector(".onboardaff").style.display="none";

            document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
                  document.querySelector(".webheader").style.display="none";
             document.querySelector(".followclassy").style.display="none";
                  document.querySelector(".webdisplay").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";

             document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";

      document.querySelector(".videoclassy").style.display = "none";
      document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".profileclassy").style.display = "none";
        document.querySelector(".viewsclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none";
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="block";
     document.querySelector(".supportclassy").style.display="none";

}

function tp() {
                  document.querySelector(".onboardaff").style.display="none";

            document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
              document.querySelector(".webdisplay").style.display="none";
                           document.querySelector(".followclassy").style.display="none";
              document.querySelector(".webheader").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";

       document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
      document.querySelector(".videoclassy").style.display = "none";
      document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".profileclassy").style.display = "block";
        document.querySelector(".viewsclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none";
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";


  }
  
 function  tfollow()  {
                   document.querySelector(".onboardaff").style.display="none";

     
                 document.querySelector(".followheader").style.display="block";

            document.querySelector(".followthemall").style.display="block";
            document.querySelector(".uploadblog").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";

   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
      document.querySelector(".videoclassy").style.display = "none";
      document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".profileclassy").style.display = "none";
        document.querySelector(".viewsclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none";
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

  }
 
 
function tv() {
                  document.querySelector(".onboardaff").style.display="none";

            document.querySelector(".followthemall").style.display="none";
  document.querySelector(".uploadblog").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";

   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
      document.querySelector(".videoclassy").style.display = "block";
      document.querySelector(".categoryclassy").style.display = "block";
      document.querySelector(".walletclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".profileclassy").style.display = "none";
        document.querySelector(".viewsclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none";
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

  }

  function tu() {
                    document.querySelector(".onboardaff").style.display="none";

                                 document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";

   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
      document.querySelector(".videoclassy").style.display = "none";
      document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "block";
      document.querySelector(".walletclassy").style.display = "none";
      document.querySelector(".profileclassy").style.display = "none";
        document.querySelector(".viewsclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none";
            document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

  }
  
 
 function tw() {
                   document.querySelector(".onboardaff").style.display="none";

                                 document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";

   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
      document.querySelector(".videoclassy").style.display = "none";
      document.querySelector(".categoryclassy").style.display = "none"; 
      document.querySelector(".uploadclassy").style.display = "none"; 
      document.querySelector(".walletclassy").style.display = "block";
      document.querySelector(".profileclassy").style.display = "none";
        document.querySelector(".viewsclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none";
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

  }
  
  function blog() {
                    document.querySelector(".onboardaff").style.display="none";

                                 document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="block";

                 document.querySelector(".followclassy").style.display="none";

           document.querySelector(".contactsgetter").style.display="none";

              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";

   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
        document.querySelector(".viewsclassy").style.display = "none";
      document.querySelector(".videoclassy").style.display = "none";
      document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
      document.querySelector(".profileclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none"
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";


  }
  
  
  
  
  
  
  
  function tgf() {
                    document.querySelector(".onboardaff").style.display="none";

                                 document.querySelector(".followthemall").style.display="none";
                 document.querySelector(".followclassy").style.display="block";
                       document.querySelector(".uploadblog").style.display="none";

           document.querySelector(".contactsgetter").style.display="none";

              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";

   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
        document.querySelector(".viewsclassy").style.display = "none";
      document.querySelector(".videoclassy").style.display = "none";
      document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
      document.querySelector(".profileclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none"
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";


  }
  
  
  
  
  
  
  function ta() {
                    document.querySelector(".onboardaff").style.display="none";

                                 document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";
   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
        document.querySelector(".viewsclassy").style.display = "block";
      document.querySelector(".videoclassy").style.display = "none";
      document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
      document.querySelector(".profileclassy").style.display = "none";
             document.querySelector(".contactclassy").style.display="none"
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";

  }
  
   function tf() {
                                document.querySelector(".onboardaff").style.display="none";
          
     document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
       document.querySelector(".contactclassy").style.display="block";
      document.querySelector(".viewsclassy").style.display = "none";
      document.querySelector(".videoclassy").style.display = "none";
     document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
     document.querySelector(".profileclassy").style.display = "none"; 
           document.querySelector(".webclassy").style.display="none";
               document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";
     
  }
  
  function tweb() {
                    document.querySelector(".onboardaff").style.display="none";

                                         document.querySelector(".followthemall").style.display="none";
                       document.querySelector(".uploadblog").style.display="none";
              document.querySelector(".webdisplay").style.display="none";
              document.querySelector(".webheader").style.display="none";
           document.querySelector(".contactsgetter").style.display="none";
             document.querySelector(".followclassy").style.display="none";
   document.querySelector(".productheader").style.display="none";
      document.querySelector(".followermarketplace").style.display="none";
      document.querySelector(".webclassy").style.display="block";
       document.querySelector(".contactclassy").style.display="none";
      document.querySelector(".viewsclassy").style.display = "none";
      document.querySelector(".videoclassy").style.display = "none";
     document.querySelector(".categoryclassy").style.display = "none";
      document.querySelector(".uploadclassy").style.display = "none";
      document.querySelector(".walletclassy").style.display = "none";
     document.querySelector(".profileclassy").style.display = "none"; 
     document.querySelector(".marketclassy").style.display="none";
     document.querySelector(".supportclassy").style.display="none";
    
  }
  
  
 function tsup() {
    var elements = document.querySelectorAll(".followthemall, .uploadblog, .webdisplay, .webheader, .contactsgetter, .followclassy, .productheader, .followermarketplace, .webclassy, .contactclassy, .viewsclassy, .videoclassy, .categoryclassy, .uploadclassy, .walletclassy, .profileclassy, .marketclassy, .onbooardaff");

    for (var i = 0; i < elements.length; i++) {
        elements[i].style.display = "none";
    }

    document.querySelector(".supportclassy").style.display = "block";
}

  
</script>

</div>


<div class="section2">
    
     
    <div class="webdisplay" style="display:none">
        
        
            <div class="emporlm">
     <h3> Web Explainer </h3>
       
       
       
       
       <h2 class="vidtitle"> Click Weblinks and Earn </h2> 
    
        
       <!-- <iframe width="560" height="315" src="https://www.youtube.com/embed/M1hCs9UvJ2Q?si=Mtw4qfVvNkTkA3IX" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="iflo"></iframe>
    
    <h2 class="vidtitle"> How to Generate 100k Clicks for Your Website, Affiliate Link & Upwork Profile </h2> -->
    
    
    </div>
    
    
<div class="webfeeder"></div>
<div class="webfeed"></div>


    
    
        </div>
    
    
    <div class="followermarketplace" style="display:none">
     
     <div class="emporl">
     <h3> Market Explainer </h3>
    <iframe width="560" height="315" src="https://www.youtube.com/embed/BUbkid871HM?si=1_fyiJNIPW9Z-3jf" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="iflo"></iframe>
    <h2 class="vidtitle">How to purchase products on Grow Social Platform</h2>
    </div>
    
<div class="productfeeder"></div>
<div class="productfeed"></div>


<script>
    $(document).ready(function () {
        var startTime; // Variable to store the start time
        var isProductViewed = false; // Flag to track if the product has been viewed

        // Function to send data using XHR to the first PHP script
        function sendDataThirdScript(productId, userEmail, posterEmail, productRate, currency) { 
            var xhr = new XMLHttpRequest();
            var requestData = 'productId=' + encodeURIComponent(productId) +
                  '&userEmail=' + encodeURIComponent(userEmail) +
                  '&posterEmail=' + encodeURIComponent(posterEmail) +
                  '&productRate=' + encodeURIComponent(productRate) +
                  '&currency=' + encodeURIComponent(currency);

            xhr.open('POST', 'payforproducts.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.withCredentials = true;

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                   console.log('Request to the third script successful:', xhr.responseText);
                } else {
                    console.error('Request to the third script failed:', xhr.statusText);
                }
            };

            xhr.onerror = function () {
                console.error('Network error occurred');
            };

            xhr.send(requestData);
        }

        // Function to send data using XHR to the second PHP script
        function sendDataFourthScript(productId, userEmail) {
            var xhr = new XMLHttpRequest();
            var requestData = 'productId=' + encodeURIComponent(productId) +
                              '&userEmail=' + encodeURIComponent(userEmail);
console.log(requestData);
xhr.open('POST', 'update_viewed_products.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.withCredentials = true;

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    console.log('Request to the fourth script successful:', xhr.responseText);
                } else {
                    console.error('Request to the fourth script failed:', xhr.statusText);
                }
            };

            xhr.onerror = function () {
                console.error('Network error occurred');
            };

            xhr.send(requestData);
        }

        // Function to embed the product and log the time
// Function to embed the product and log the time
function embedProduct(title, views, price, qty, description, productId, userEmail, posterEmail, productRate, currency, photographs, fullname, phonenumber) {
    startTime = performance.now();

var productDiv = $('<div>', { 
    class: 'product-container'
});

$('.productfeeder').append(productDiv);
    
    var marketplaceHeader = $('<h3>', {
    class: 'markeplaces',
    text: 'Marketplace'
});

// Append the <h3> element to the '.productfeeder' div
marketplaceHeader.appendTo('.productfeeder');

    // Append product name to the '.productfeed' container
$('<div>', { class: 'productname', text: title }).appendTo('.productfeed');

// Append quantity information to the '.productfeed' container
$('<div>', { class: 'qtynumbers', html: "<b>Quantity Available: </b>" + qty }).appendTo('.productfeed');

// Determine currency symbol based on the currency
var currencySymbol;
if (currency === "NGN") {
    currencySymbol = "₦"; // Naira symbol
} else if (currency === "USD") {
    currencySymbol = "$"; // Dollar symbol
} else {
    currencySymbol = currency; // Use currency code if not NGN or USD
}

// Set the HTML of the .prodcurrency element
$('<div>', { class: 'prodcurrency', html: "<b>Price: </b>" + currencySymbol + price }).appendTo('.productfeed');

// Append views, product description, and payment button ID to the '.productfeed' container
//$('<div>', { class: 'views', text: views }).appendTo('.productfeed');
$('<div>', { class: 'productdescr', html: "<b>Product Description:</b><br>" + description }).appendTo('.productfeed');

//$('<div>', { class: 'paymentbuttonid', text: productId }).appendTo('.productfeed');

var photographsSingleQuotes = photographs.replace(/"/g, "'");

var urls = photographsSingleQuotes.split(',');

urls = urls.map(url => url.trim()).filter(Boolean);

function cleanImageSource(src) {
  src = src.replace(/^['\/\\\[\]\s]+|['\/\\\[\]\s]+\/?$/g, '');
  src = src.replace(/\\/g, '');
  return src;
}

var cleanedImageSources = urls.map(cleanImageSource);

cleanedImageSources.forEach((src, index) => {
    var imgClass = index === 0 ? 'product-image' : 'arrayed-image'; 
    var imgTag = $('<img>', {
        src: src,
        class: imgClass
    });
    $('.productfeeder').append(imgTag);
});



alert("Product feed appended successfully");

var form = document.createElement("form");
form.id = "productformescrow";
form.method = "post";
form.action = "escrowpayment.php";

var amountLabel = document.createElement("label");
amountLabel.textContent = "Qty:";

var inputAmount = document.createElement("input");
inputAmount.type = "number";
inputAmount.className = "productprice";
inputAmount.name = "amount";

inputAmount.setAttribute("readonly", true);


if (currency === "NGN") {
    inputAmount.id = "amountprice";
} else {
    inputAmount.id = "amountprice";
}


var inputUserEmail = document.createElement("input");
inputUserEmail.type = "email";
inputUserEmail.name = "userEmail";
inputUserEmail.id ="buyeremail";
inputUserEmail.value = userEmail;
inputUserEmail.setAttribute("hidden", true);
inputUserEmail.setAttribute("readonly", true);

var inputPosterEmail = document.createElement("input");
inputPosterEmail.type = "email";
inputPosterEmail.name = "creatorEmail"; 
inputPosterEmail.value = posterEmail;
inputPosterEmail.setAttribute("hidden", true);
inputPosterEmail.setAttribute("readonly", true); 


var productIdnum = document.createElement("input");
productIdnum.type = "number";
productIdnum.name = "productIdnum";
productIdnum.value = productId;
productIdnum.setAttribute("hidden", true);
productIdnum.setAttribute("readonly", true);
 
var currencyofproduct  = document.createElement("input");
currencyofproduct.type = "text";
currencyofproduct.name = "currency";
currencyofproduct.value = currency;
currencyofproduct.setAttribute("hidden", true);
currencyofproduct.setAttribute("readonly", true);

var creatorphone = document.createElement("input");
creatorphone.type = "number";
creatorphone.name = "phonenumber";
creatorphone.value = phonenumber;

var fullnameInput = document.createElement("input");
fullnameInput.type = "text";
fullnameInput.name = "fullname";
fullnameInput.value = fullname; // Assign the retrieved data to the input's value attribut

var productTitlelist  = document.createElement("input");
productTitlelist.type = "text";
productTitlelist.name = "productTitle";
productTitlelist.value = title;
productTitlelist.setAttribute("hidden", true);
productTitlelist.setAttribute("readonly", true);

var quantityLabel = document.createElement("label");
quantityLabel.textContent = "Total Cost: ";

// Create the input element
var inputQuantity = document.createElement("input");
inputQuantity.type = "number";
inputQuantity.name = "quantity";
inputQuantity.placeholder = "Quantity";
inputQuantity.min = 0;
inputQuantity.max = qty; // Assuming qty is defined somewhere in your code

inputQuantity.addEventListener('input', function() {
  var quantity = this.value;
  var totalPrice = quantity ? parseInt(quantity) * parseFloat(price) : 0; // 
  inputAmount.value = totalPrice.toFixed(2);
});

var inputPurchaseDesc = document.createElement("input");
inputPurchaseDesc.type = "text";
inputPurchaseDesc.id ="purchaseDescription";
inputPurchaseDesc.name = "purchaseDescription";
inputPurchaseDesc.value = "Being purchase of " + title; 
inputPurchaseDesc.setAttribute("hidden", true);
inputPurchaseDesc.setAttribute("readonly", true);

var button = document.createElement("button");
button.type = "button";
button.className = "ff"; // Corrected property name
button.textContent = "Buy";
button.onclick = function(event) { // Added parameter event
    event.preventDefault();
    //c(currency);
};
// Add event listener to the button
button.addEventListener("click", function() {
    // Gather necessary information
    var phoneNumber = creatorphone.value; // Assuming creatorphone is defined somewhere in your code
    var productTitle = productTitlelist.value; // Assuming productTitlelist is defined somewhere in your code
    var quantity = inputQuantity.value;
    var Price = parseFloat(price); // Assuming price is defined and parsed as a float
    var totalPrice = quantity ? parseInt(quantity) * Price : 0;
    var fullName = fullnameInput.value;
    
    // Validate inputs
    if (!phoneNumber || !productTitle || !quantity || !fullName || !Price) {
        // Handle missing inputs
        var missingFields = [];
        if (!phoneNumber) missingFields.push("Phone number");
        if (!productTitle) missingFields.push("Product title");
        if (!quantity) missingFields.push("Quantity");
        if (!fullName) missingFields.push("Full name");
        if (!Price) missingFields.push("Price");

        // Construct error message
        var errorMessage = "Please fill in the following required fields: " + missingFields.join(", ");
        
        // Display error message
        alert(errorMessage);
        return;
    }

    // Convert quantity to an integer
    var quantityInt = parseInt(quantity);
    
    // Check if quantity is at least 1
    if (quantityInt < 1) {
        alert("Minimum quantity must be 1.");
        return;
    }
    
    // Define the introductory text
var introText = "I saw your product listing on Grow Social platform. ";

// Construct the WhatsApp message with the introductory text
var message = "Hey " + fullName + ", " + introText + "I want to buy " + quantity + " " + productTitle + " for a total price of " + totalPrice.toFixed(2) + " " + currency + ".";
var waURL = "https://wa.me/" + encodeURIComponent(phoneNumber) + "?text=" + encodeURIComponent(message);

    // Redirect to WhatsApp chat window
    window.location.href = waURL;
});

form.appendChild(amountLabel);
form.appendChild(inputQuantity);
form.appendChild(quantityLabel);
form.appendChild(inputAmount);
form.appendChild(inputUserEmail);
form.appendChild(inputPosterEmail); 
form.appendChild(productIdnum);
form.appendChild(creatorphone);
form.appendChild(fullnameInput);
form.appendChild(currencyofproduct);
form.appendChild(productTitlelist); 
form.appendChild(inputPurchaseDesc);
form.appendChild(button);
 
document.querySelector(".productfeed").appendChild(form);

    isProductViewed = false;
 
    var intervalId = setInterval(function () {
        if (startTime && !isProductViewed) { 
            var currentTime = performance.now();
            var elapsedSeconds = (currentTime - startTime) / 1000; 

            if (elapsedSeconds >= 10) { // If 100 seconds have passed
                isProductViewed = true; // Set product viewed flag to true
                clearInterval(intervalId); // Stop the timer

                // Call functions to send data using XHR to the third and fourth scripts after product is viewed
                sendDataThirdScript(productId, userEmail, posterEmail, productRate, currency);
                sendDataFourthScript(productId, userEmail);
            }
        }
    }, 1000); // Update every second
}

// Attach click event to the table cell with class 'productclass'
$('.productclass').one('click', function () {
    // Retrieve data attributes from the clicked element
        $('.productfeeder').empty();
    $('.productfeed').empty();

    var views = $(this).data('views');
        var title = $(this).data('title');

    var description = $(this).data('description'); 
    var productId = $(this).data('product-id');
    var userEmail = "<?php echo $email; ?>"; // PHP variable for user email
    var posterEmail = $(this).data('email');
    var productRate = $(this).data('rate');
    var price = $(this).data('price');
    var currency = $(this).data('currency');
    var qty = $(this).data('qty');
    var photographs = $(this).data('photographs'); // Get the value of data-photographs attribute
    var fullname = $(this).data('fullname');
    console.log (fullname);
    var phonenumber= $(this).data('phonenumber');

    //console.log("photo:", photographs);
    //  console.log("photospeak:", photograpSingleQuotes); // Log the modified photographs variable

    $('.emporl').hide(); // Hide elements with class 'iflo'

    // Call the function to embed the product
    embedProduct(title, views, price, qty, description, productId, userEmail, posterEmail, productRate, currency, photographs, fullname, phonenumber);
    
});

    });
</script>



</div>






<div class="profileclassy" style="display:none">
    
        <h3>Profile</h3>

      <div class="profile1">
            
            <?php
    error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start or resume the session
// Get the email from the session
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Prepare and execute the SQL query
$sql = "SELECT * FROM growsignup WHERE email = '$email'";
$result = $conn->query($sql);

// Initialize variables
$lastName = $firstName = "";
//$username = $firstName = $dateOfBirth = "";

// Check if there are results
if ($result->num_rows > 0) {
    // Assuming there is only one result for the given email
    $row = $result->fetch_assoc();

    // Store data in variables
    $firstName = $row['firstName'];
        $lastName = $row['lastName'];
$fullName = $firstName . ' ' . $lastName;
$userType =$row['userType'];
$afref =$row['afref'];

}

// Close the database connection
$conn->close();

// Use the retrieved data outside the loop as needed
echo "<p>Full Name: <span>$fullName</span></p>"; 
echo "<p>User Type: <span>$userType</span></p>";
echo "<p>Bio: <span> $bio </span> </p>";
?>

</div>


            <?php if ($userType === 'affiliate') : ?>
            
            <p>Referral ID: <span><?php echo $afref; ?></span></p>
            <p>Referral Link: <span> <?php echo $refLink; ?>  <button onclick="copyToClipboard()" class="bluebutton">Copy Link</button>
</span></p>
<input type="text" id="refLink" value="<?php echo $refLink; ?>" hidden readonly>

<div class="socialbutton">
    <p> Share on Your Handles </p>
    <button onclick="shareOnFacebook()">
      <!--  <img src="facebook_icon.png" alt="Facebook"> -->
        Share on Facebook
    </button>
    <button onclick="shareOnLinkedIn()">
      <!--    <img src="linkedin_icon.png" alt="LinkedIn"> -->
        Share on LinkedIn
    </button>
    <button onclick="shareOnTwitter()">
           <!-- <img src="twitter_icon.png" alt="Twitter"> -->
        Share on Twitter
    </button>
    <button onclick="shareOnReddit()">
       <!--   <img src="reddit_icon.png" alt="Reddit"> -->
        Share on Reddit
    </button>
    
    
<script>
function copyToClipboard() {
    var copyText = document.getElementById("refLink");

    if (copyText) {
        var inputValue = copyText.value.trim();
        if (inputValue !== '') {
            navigator.clipboard.writeText(inputValue)
                .then(function() {
                    window.scrollTo(0, 0);
                    alert("Your referral link has been copied");
                })
                .catch(function(error) {
                    alert("Failed to copy: " + error);
                });
        } else {
            alert("Referral link is empty.");
        }
    } else {
        alert("Element with ID 'refLink' not found.");
    }
}

function shareOnFacebook() {
    var linkToShare = encodeURIComponent(document.getElementById("refLink").value);
    var facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + linkToShare;
    window.open(facebookShareUrl, "_blank");
}

function shareOnLinkedIn() {
    var linkToShare = encodeURIComponent(document.getElementById("refLink").value);
    var linkedInShareUrl = 'https://www.linkedin.com/sharing/share-offsite/?url=' + linkToShare;
    window.open(linkedInShareUrl, "_blank");
}


function shareOnTwitter() {
    var linkToShare = encodeURIComponent(document.getElementById("refLink").value);
    var twitterShareUrl = 'https://twitter.com/intent/tweet?url=' + linkToShare;
    window.open(twitterShareUrl, "_blank");
}

function shareOnReddit() {
    var linkToShare = encodeURIComponent(document.getElementById("refLink").value);
    var redditShareUrl = 'https://www.reddit.com/submit?url=' + linkToShare;
    window.open(redditShareUrl, "_blank");
}
</script>

</div>

        <?php endif; ?>
    <br>

            <a href="#" onclick="openForm()">Update Profile</a> 

     <div class="pop up-form" id="myForm" style="display:none">
        <form class="loginclassx" method="post" enctype="multipart/form-data" id="formsell"  action="growupdate.php">
         
         
    <div class="colomn111">
         <!-- Add your form fields here -->
        <div class="row223">
            
        <br>
        
         <label for="profile_picture">Profile Picture:</label><br>
         <input type="file" id="profilepicture" name="profilepicture[]"><br>
        
            <label>First Name:</label> <br>
            <input type="text" id="fname" name="firstName" required><br>
             
               <label>Last Name:</label> <br>
            <input type="text" id="lname" name="lastName" required><br>
             
        </div>
        
        <div class="row223">
            
            <label> Mobile Number: </label> <br>
            <input type="tel" name="telephone" placeholder="+234">

        </div> 

        <div class="row223"> 

            <label> Nationality: </label><br>
            <select id="countrypeople" name="userLocation"></select>
            
            <br>
            <label> Gender: </label> <br>

            <select id="gender" name="userGender"> 
                <option value="male"> Male </option>
                <option value="female"> Female </option>

            </select><br>
            
            
            <label> Add Bio </label><br>
<textarea id="bio" name="bio" placeholder="Write a short bio here..." rows="5" cols="50"></textarea>
</div>

                <div id="save9ja" class="save9ja">
                                       
                                        <p style="font-weight:bold;"> Add your Naira Account Details </p>

                     <label for="bank">Bank Name*</label> <br>
                     <select id="bank-code" name="local_bank"></select>
                    <br>
                      <label for="account-number">Account Number*</label><br>
                     <input type="text" id="account-number" value="" name="account_number" placeholder="e.g 2065970801">
                   <br>
                     <label> Account Type</label><br>
                    <select name="accountType" id="accounttype">
                       <option value="savings">Savings</option>
                       <option value="checking">Checking</option>
                     </select>
<br>
                     <label for="account-name">Account Name (Required)</label><br>
                     <input type="text" id="account-name" value="" name="account_name_within" class="accountnamenig" placeholder="" readonly>
                     <button type="button" id="verify-btno">Verify Account</button>

                     <p> Account name will be generated after the "Verify Account" button is pressed. </p>
                     <br>
 
        </div>
                     
        <div id="diaspora" class="diaspora">
                    
                    <p style="font-weight:bold;"> Add your Dollar Account Details </p>
                    
                    <label for="country"  style="display:none">Country</label>
                    <select id="country" name="bank_country" class="foreigncountry" hidden>
                    </select>
               

                <label> Account Name</label> <br>
                <input type="text" id="account-holder-name" name="account_holder_name" placeholder="Input Your Account Name">   
                <br>

                     <label for="bank-account">Bank Account Number:</label>  <br>
                     <input type="text" id="bank-account-number" value="" class="accountforeign" name="bank_account_number"  placeholder="Input Your Bank Account Name">
                     <br>
                     <label for="bank-number">Routing Number</label><br>
                      <!-- <label for="bank-number">Routing/Sort or IBAN number</label>-->
                   
                     <input type="text" id="bank-number" name="bank_number" placeholder="e.g 234345664">
                     
                     <br>
                     <label> Bank Name </label> <br>
                     <input type="text" name="foreign_bank_name" id="bank-name" placeholder="Generated Bank name" value="" readonly>   

                        
                     <button type="button" id="verify-btn">Verify Bank</button>
                     <p> Bank name will be generated after the "Verify Bank" button is pressed. </p>

                     <br>
            </div>
            

<br>
        </div>
                     <input type="email" name="email" hidden readonly value="<?php echo $email;?>">

<div class="signup">
            <button type="submit"> Submit </button>
            <button type="button" onclick="closeForm()">Close</button>
            </div>
        </form>
    </div>
</div>


<script>
    fetch('https://restcountries.com/v2/all')
    .then(response => response.json())
    .then(data => {
      const countries = data
        .filter(country => country.alpha2Code.toUpperCase() === 'US') // only include United States
        .map(country => {
          const countryCode = country.alpha2Code.toUpperCase();
          return {
            name: country.name,
            code: countryCode
          }
        });
      const select = document.getElementById('country');
      countries.sort((a, b) => a.name.localeCompare(b.name)).forEach(country => {
        const option = document.createElement('option');
        option.value = country.code;
        option.text = country.name;
        select.appendChild(option);
      });
    });
  </script>
  
  
  <script>
    fetch('https://restcountries.com/v2/all')
    .then(response => response.json())
    .then(data => {
      const countries = data
        .filter(country => country.alpha2Code.toUpperCase()) // only include United States
        .map(country => {
          const countryCode = country.alpha2Code.toUpperCase();
          return {
            name: country.name,
            code: countryCode
          }
        });
      const select = document.getElementById('countrypeople');
      countries.sort((a, b) => a.name.localeCompare(b.name)).forEach(country => {
        const option = document.createElement('option');
        option.value = country.name;
        option.text = country.name;
        select.appendChild(option);
      });
    });
  </script>
  
  
<script>
    const bankCodeDropdown = document.querySelector('#bank-code');
    const accountNumberInput = document.querySelector('#account-number');
    const accountNameInput = document.querySelector('#account-name');
    const verifyButton = document.querySelector('#verify-btno');
    
    // Make an AJAX request to retrieve a list of banks
    fetch('https://api.paystack.co/bank')
      .then(response => response.json())
      .then(banks => {
        // Add each bank as an option in the dropdown
        banks.data.forEach(bank => {
          const option = document.createElement('option');
          option.value = `${bank.code}-${bank.name}`;
          option.text = `${bank.name}`;
          bankCodeDropdown.appendChild(option);
        });
      });
    
    // Listen for a click event on the verify button
    verifyButton.addEventListener('click', () => {
      // Retrieve the selected bank code and account number
      const selectedValue = bankCodeDropdown.value;
      const [bankCode, bankName] = selectedValue.split('-');
      const accountNumber = accountNumberInput.value;
    
      // Make an AJAX request to verify the account number
    
    fetch(`https://api.paystack.co/bank/resolve?account_number=${accountNumber}&bank_code=${bankCode}`, {
      headers: {
        Authorization: 'Bearer sk_live_5117459ebcf8aec8db565b90b47da98c2ed700d9', // Replace with your own secret API key
      },
    })
      .then(response => response.json())
      .then(result => {
        if (result.status) {
          const accountName = result.data.account_name;
          accountNameInput.setAttribute('value', accountName); // Set the value of the account name input field
          accountNameInput.value = accountName; // Set the value property as well (backup option)
        } else {
          alert(result.message);
        }
      });
    
    });
    </script>
  
  
  <script>
  const countryInput = document.getElementById('country');
  const bankNumberInput = document.getElementById('bank-number');
  const bankAccountNumberInput = document.getElementById('bank-account-number');
  const accountHolderNameInput = document.getElementById('account-holder-name');
  const bankNameInput = document.getElementById('bank-name');
  const verifyBtn = document.getElementById('verify-btn');
  
  countryInput.addEventListener('change', () => {
    if (countryInput.value === 'US') {
      bankNumberInput.placeholder = 'Routing Number';
    } else if (countryInput.value === 'GB') {
      bankNumberInput.placeholder = 'Sort Code';
    } else if (countryInput.value === 'AU') {
      bankNumberInput.placeholder = 'BSB Number';
    }
  });
  
  verifyBtn.addEventListener('click', () => {
    const country = countryInput.value;
    let bankNumber = bankNumberInput.value.replace(/\s/g, ''); // Remove any spaces in the bank number input
    const bankAccountNumber = bankAccountNumberInput.value;
    
    let currency;
    let bankNumberField;
    
    if (country === 'US') {
      bankNumberField = 'routing_number';
      currency = 'USD';
    } else if (country === 'GB') {
      bankNumberField = 'sort_code';
      currency = 'GBP';
    } else if (country === 'AU') {
      bankNumberField = 'bsb_number';
      currency = 'AUD';
    }
    
    if (country === 'US' && bankNumber.length !== 9) {
      alert('Routing number must be 9 digits long');
      return;
    } else if (country === 'GB' && bankNumber.length !== 8 && bankNumber.length !== 6) {
      alert('Sort code must be 6 or 8 digits long');
      return;
    } else if (country === 'AU' && bankNumber.length !== 6) {
      alert('BSB number must be 6 digits long');
      return;
    }
    
  stripe.createToken('bank_account', {
    country: country,
    account_number: bankAccountNumber,
    currency: currency,
    account_holder_name: accountHolderNameInput.value,
    account_holder_type: 'individual',
    ...(bankNumberField && { [bankNumberField]: bankNumber }),
  }).then((result) => {
    // Handle the result from the Stripe API
    if (result.error) {
      console.error(result.error.message);
      // Display error message to user
    } else {
      // Update account holder name and bank name fields with API response
      bankNameInput.setAttribute('value', result.token.bank_account.bank_name);
      // Display success message to user
    }
  });
  
  });
  </script>


<div class="walletclassy" style="display:none"> 
  <div class="walletclassy">
    <h3>Wallets</h3>
    
    <div class="nairabalx">
        
        <style>
            
.nairabalx, .dollarbalx {
    background-color: #fafafa;
border-radius: 8px;
  
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 20px;
  width: 300px;
}

.nairabalx h2, .dollarbalx h2 {
  font-size: 24px;
  margin-bottom: 15px;
  color: #333;
}

.nairabalx p, .dollarbalx p {
  font-size: 16px;
  line-height: 1.5;
  color: #666;
}

button {
    margin-bottom: 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 5px 10px;
  font-size: 12px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.nairabalx button:hover {

}

.dolbutton {
    border:none;
    background-color: transparent;
}

.dolbutton img {
    
    width: 20px;
    height: 20px;
}

.nirfloat {
    
    
    display:flex;
    gap: 150px;
}
        </style>
<?php

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$sesemail = !empty($email) ? $email : $sesemail;

// Check if the session email is not empty
if (!empty($sesemail)) {
    // Query to get the total Naira balance for the specific user's email
$balanceQuery = "SELECT SUM(
                    CASE 
                        WHEN depositoremail = ? THEN amount 
                        WHEN recipientemail = ? THEN amount 
                        ELSE -amount 
                    END
                ) AS nairaBalance 
                FROM nairatransactions 
                WHERE depositoremail = ? OR withdrawalemail = ? OR recipientemail = ?";
    // Using prepared statements to prevent SQL injection
    $stmt = $conn->prepare($balanceQuery);

    if ($stmt) {
        $stmt->bind_param("sssss", $sesemail, $sesemail, $sesemail, $sesemail, $sesemail);

        // Execute the query
        if ($stmt->execute()) {
            // Get the result
            $result = $stmt->get_result();

            // Check if there are results
            if ($result->num_rows > 0) {
                // Fetch the consolidated balance
                $nairaBalance = $result->fetch_assoc()['nairaBalance'];

                // Display the consolidated balance
                echo '<div class="nirbal">';
                                echo '<div class="nirfloat">';

                echo '<p class="balbal">Naira Balance</p>';
                echo ' <button onclick="toggleDolbal();" class="dolbutton"> <img src="https://cdn2.iconfinder.com/data/icons/picons-basic-1/57/basic1-084_rotate_sync-64.png"> </button>';
                echo '</div>';

                echo '<h2>₦' . number_format($nairaBalance ?? 0, 2) . '</h2>';
                echo '</div>';
            } else {
                // Handle the case where there are no entries
                echo 'No entries found.';
            }
        } else {
            // Handle query execution failure
            echo 'Query execution failed.';
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Handle prepared statement creation failure
        echo 'Prepared statement creation failed.';
    }
} else {
    // Handle the case where the session email is empty
    echo 'Session email not found.';
}

// Close the database connection
$conn->close();

?>

<form id="nairadepositform" method="post" action="nairadeposits.php" style="display:none;" class="formclassy1 nairadep">
        
        <label> Currency</label> <br>
        <input type="text" id="currencyval" name="currencyval" value="NGN" readonly> 
         <br>
        <label> Deposit Amount </label>
        <br>
        <input type="number" id="amountvalnaira" name="amount" value="" step="100" min="100" max="200000">
        
        <input type="text" id="depositoremail" name="depositoremail" value="<?php echo $email;?>" hidden readonly>  
        <input type="text" id="depositorname" name="depositorname" value="<?php echo $firstName;?>"  hidden readonly>  
        <input type="text" id="companyname" name="companyname" value="Grow Social Inc."  hidden readonly>  
        <input type="text" id="paymentdescr" name="paymentdescr" value="Being Deposit for Charges on Grow Social Inc."  hidden readonly>  
        <br> <br>
       
        <button type="button" onclick="event.preventDefault(); makePayment('NGN')">Make Payment</button>
        <button type="button" onclick="event.preventDefault(); toggleNairaPaycancel();">Cancel Payment</button>

</form>
<form id="nairawithdrawform" method="post" action="nairawithdraw.php" style="display:none;" class="nairawit">
    <input type="text" id="nairaBalanceInput" value="<?= number_format($nairaBalance ?? 0, 2)  ?>" readonly hidden>

        <label> Currency</label>        <br>

        <input type="text" id="currencyval" name="currencyval" value="NGN" readonly>  
        <br>
        <label> Withdrawal Amount </label>
        <br>
        <input type="number" id="amountNairawithdraw" name="amount" value="" step="1" min="5" max=1000>
        
        <input type="text" id="withdrawalemail" name="withdrawalemail" value="<?php echo $email;?>"  hidden readonly>  
        <input type="text" id="withdrawalname" name="withdrawalname" value="<?php echo $firstName;?>"  hidden readonly>  
        <input type="text" id="paymentdescr" name="paymentdescr" value="Being Withdraw for Gains on Grow Social Inc." hidden readonly>  
<br>        <br>

    <button type="button" onclick="event.preventDefault(); withdrawNaira('NGN')">Withdraw</button>
        <button type="button" onclick="event.preventDefault(); toggleNairaWitcancel();">Cancel Withdraw</button>
        <br>


</form>

<script>
    
    function  toggleNairaPaycancel() {
        document.querySelector (".depositnaira").style.display="block";
                      document.querySelector(".nairadep").style.display = "none";

    }
    
     function  toggleDollarPaycancel() {
        document.querySelector (".depositdollar").style.display="block";
                      document.querySelector(".dollardep").style.display = "none";

    }
    
    function  toggleNairaWitcancel() {
                      document.querySelector(".nairawit").style.display = "none";
                      document.querySelector(".withdrawnaira").style.display = "block";

    }
    
     function  toggleDollarWitcancel() {
                      document.querySelector(".dollarwit").style.display = "none";
                      document.querySelector(".withdrawdollar").style.display = "block";

    }
    
</script>

<?php
$userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
?>

<?php if ($userType === 'curator') : ?>
    <!-- If the user is a curator, show the deposit button -->
    <button class="depositnaira" onclick="toggleNairadeposit();">
        Deposit Naira
    </button>
<?php else : ?>
    <!-- If the user is not a curator, show the withdrawal button -->
    <button class="withdrawnaira" onclick="toggleNairawithdraw();">
        Withdraw Naira
    </button>
<?php endif; ?>


</div>

<script>
    
    
    function toggleDolbal() { 

      document.querySelector(".dollarbalx").style.display = "block"; 
      document.querySelector(".nairabalx").style.display = "none";
      document.querySelector(".dollardep").style.display = "none"; 
      document.querySelector(".nairadep").style.display = "none";
                      document.querySelector(".nairawit").style.display = "none";
                    document.querySelector(".withdrawdollar").style.display = "block";

    }
    
     function toggleNaibal() {  
 
      document.querySelector(".dollarbalx").style.display = "none"; 
      document.querySelector(".nairabalx").style.display = "block"; 
      document.querySelector(".dollardep").style.display = "none";
      document.querySelector(".nairadep").style.display = "none";
                      document.querySelector(".dollarwit").style.display = "none";
                      document.querySelector(".nairawit").style.display = "none";
                    document.querySelector(".withdrawnaira").style.display = "block";

    } 
    
    
    function toggleDollardeposit() {
              document.querySelector(".dollardep").style.display = "block";
                    document.querySelector(".depositdollar").style.display = "none";


    }
    
    function toggleNairadeposit() {
              document.querySelector(".nairadep").style.display = "block";
                    document.querySelector(".depositnaira").style.display = "none";

    }
    
    
   function toggleNairawithdraw() {

                     document.querySelector(".nairawit").style.display = "block";
                    document.querySelector(".withdrawnaira").style.display = "none";

   }
   
    function toggleDollarwithdraw() {

              document.querySelector(".dollarwit").style.display = "block";
                    document.querySelector(".withdrawdollar").style.display = "none";

   }
</script>

<div class="dollarbalx" style="display:none;">
<?php

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$sesemail = !empty($email) ? $email : $sesemail;

$dollarBalance = 0.00;

// Check if the session email is not empty
if (!empty($sesemail)) {
    // Query to get the total Naira balance for the specific user's email
    $balanceQuery = "SELECT SUM(
                        CASE 
                            WHEN depositoremail = ? THEN amount 
                            WHEN recipientemail = ? THEN amount 
                            ELSE -amount 
                        END
                    ) AS dollarBalance 
                    FROM dollartransactions 
                    WHERE depositoremail = ? OR withdrawalemail = ? OR recipientemail = ?";
    // Using prepared statements to prevent SQL injection
    $stmt = $conn->prepare($balanceQuery);

    if ($stmt) {
        $stmt->bind_param("sssss", $sesemail, $sesemail, $sesemail, $sesemail, $sesemail);

        // Execute the query
        if ($stmt->execute()) {
            // Get the result
            $result = $stmt->get_result();

            // Check if there are results
            if ($result->num_rows > 0) {
                // Fetch the consolidated balance
                $row = $result->fetch_assoc(); // Fetch the result row
                $dollarBalance = $row['dollarBalance']; // Assign the balance

                // Display the consolidated balance
                
                
                echo '<div class="dorbal">';
                                                echo '<div class="nirfloat">';

                echo '<p class="balbal">Dollar Balance</p>';
                                echo ' <button onclick="toggleNaibal();" class="dolbutton"> <img src="https://cdn2.iconfinder.com/data/icons/picons-basic-1/57/basic1-084_rotate_sync-64.png"> </button>';
                echo '</div>';

                echo '<h2>$' . number_format($dollarBalance ?? 0, 4) . '</h2>';
                echo '</div>';
            } else {
                // Handle the case where there are no entries
                echo 'No entries found.';
            }
        } else {
            // Handle query execution failure
            echo 'Query execution failed.';
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Handle prepared statement creation failure
        echo 'Prepared statement creation failed.';
    }
} else {
    // Handle the case where the session email is empty
    echo 'Session email not found.';
}

// Close the database connection
$conn->close();

?>


<form id="dollardepositform" method="post" action="dollardeposits.php" style="display:none;" class="dollardep">

        <label> Currency</label>        <br>

        <input type="text" id="currencyval" name="currencyval" value="USD" readonly>  
        <br>
        <label> Deposit Amount </label>
        <br>
        <input type="number" id="amountvaldollar" name="amount" value="" step="1" min="5" max=1000>
        
        <input type="text" id="depositoremail" name="depositoremail" value="<?php echo $email;?>"  hidden readonly>  
        <input type="text" id="depositorname" name="depositorname" value="<?php echo $firstName;?>"  hidden readonly>  
        <input type="text" id="companyname" name="companyname" value="Grow Social Inc."  hidden readonly>  
        <input type="text" id="paymentdescr" name="paymentdescr" value="Being Deposit for Charges on Grow Social Inc." hidden readonly>  
<br>        <br>

    <button type="button" onclick="event.preventDefault(); makePayment('USD')">Make Payment</button>
        <button type="button" onclick="event.preventDefault(); toggleDollarPaycancel();">Cancel Payment</button>
        <br>


</form>
<form id="dollarwithdrawform" method="post" action="dollarwithdraw.php" style="display:none;" class="dollarwit">
    <input type="text" id="dollarBalanceInput" value="<?= number_format($dollarBalance ?? 0, 2)  ?>" readonly hidden>

        <label> Currency</label>        <br>

        <input type="text" id="currencyval" name="currencyval" value="USD" readonly>  
        <br>
        <label> Withdrawal Amount </label>
        <br>
        <input type="number" id="amountdollarwithdraw" name="amount" value="" step="1" min="5" max=1000>
        
        <input type="text" id="withdrawalemail" name="withdrawalemail" value="<?php echo $email;?>"  hidden readonly>  
        <input type="text" id="withdrawalname" name="withdrawalname" value="<?php echo $firstName;?>"  hidden readonly>  
        <input type="text" id="paymentdescr" name="paymentdescr" value="Being Withdraw for Gains on Grow Social Inc." hidden readonly>  
<br>        <br>

    <button type="button" value="submit" onclick="event.preventDefault(); withdrawDollar('USD')">Withdraw</button>
        <button type="button" onclick="event.preventDefault(); toggleDollarWitcancel();">Cancel Withdraw</button>
        <br>


</form>

<script>
   function withdrawNaira(currency) {
    // Get the Naira balance from the input
    var nairaBalance = parseFloat(document.getElementById('nairaBalanceInput').value.replace(',', ''));

    // Get the withdrawal amount
    var withdrawalAmount = parseFloat(document.getElementById('amountNairawithdraw').value);

    // Check if the withdrawal amount is 0 or blank
    if (isNaN(withdrawalAmount) || withdrawalAmount <= 0) {
        // Alert the user about invalid withdrawal amount
        alert('Invalid withdrawal amount. Please enter a valid amount.');

        // Prevent the form submission
        return;
    }

    // Check if the Naira balance is null, 0, or less than the withdrawal amount
    if (isNaN(nairaBalance) || nairaBalance <= 0 || nairaBalance < withdrawalAmount) {
        // Alert the user about insufficient balance
        alert('Insufficient Naira balance. Please check your balance.');

        // Prevent the form submission
        return;
    }
    
    // Check if the Naira balance is less than 200
    if (nairaBalance < 200) {
        // Alert the user about low balance
        alert('Your Naira balance is less than N200. Earn more so you can withdraw.');

        // Prevent the form submission
        return;
    }

    // If all checks pass, you can proceed with the form submission or any other logic
    document.getElementById('nairawithdrawform').submit();
}
</script>

<script>
   function withdrawDollar(currency) {
    // Get the dollar balance from the input
    var dollarBalance = parseFloat(document.getElementById('dollarBalanceInput').value.replace(',', ''));

    // Get the withdrawal amount
    var withdrawalAmount = parseFloat(document.getElementById('amountdollarwithdraw').value);

    // Check if the withdrawal amount is 0 or blank
    if (isNaN(withdrawalAmount) || withdrawalAmount <= 0) {
        // Alert the user about invalid withdrawal amount
        alert('Invalid withdrawal amount. Please enter a valid amount.');

        // Prevent the form submission
        return;
    }

    // Check if the dollar balance is null, 0, or less than the withdrawal amount
    if (isNaN(dollarBalance) || dollarBalance <= 0 || dollarBalance < withdrawalAmount) {
        // Alert the user about insufficient balance
        alert('Insufficient dollar balance. Please check your balance.');

        // Prevent the form submission
        return;
    }
    
    // Check if the dollar balance is less than 2
    if (dollarBalance < 2) {
        // Alert the user about low balance
        alert('Your dollar balance is less than $2. Earn more so you can withdraw.');

        // Prevent the form submission
        return;
    }

    // If all checks pass, you can proceed with the form submission or any other logic
    document.getElementById('dollarwithdrawform').submit();
}
</script>

<?php
$userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : '';
?>
 
<?php if ($userType === 'curator') : ?>
    <!-- If the user is a curator, show the deposit button -->
    <button class="depositdollar" onclick="toggleDollardeposit();">
        Deposit Dollar
    </button>
<?php elseif ($userType === 'follower') : ?>
    <!-- If the user is a follower, show the withdrawal button -->
    <button class="withdrawdollar" onclick="toggleDollarwithdraw();">
        Withdraw Dollar
    </button>
<?php elseif ($userType === 'affiliate') : ?>
    <!-- If the user is an affiliate, show the withdrawal button -->
    <button class="withdrawdollar" onclick="toggleDollarwithdraw();">
        Withdraw Dollar
    </button>
<?php endif; ?>




</div>
<style>
  .formclassy1 {
    display: flex;
    flex-direction: column;
  }

  
  .submittbutton {
    margin-top: 10px;
  }
  
  .formclassy1 select, .formclassy1 textarea, .formclassy1 input {
      margin-bottom: 10px;
  }
</style>


</div>


<script>
function makePayment(currency) {
 let amount;
    if (currency === "NGN") {
        amount = document.getElementById("amountvalnaira").value;
    } else {
        amount = document.getElementById("amountvaldollar").value;
    }
    const paymentOptions = "card, banktransfer, ussd";
    const metaSource = "docs-inline-test";
    const customerEmail = document.getElementById("depositoremail").value;
    const customerName = document.getElementById("depositorname").value;
    const title = document.getElementById("companyname").value;
    const description = document.getElementById("paymentdescr").value;
    const logo = "https://growsocial.com.ng/black_white_grow_logo__1_-removebg-preview.png";

    FlutterwaveCheckout({
       // public_key: "FLWPUBK_TEST-acc539ebd2b1244fcf32835b66301422-X" FLWPUBK-d4d43e900b43e64805dee3f214cf3500-X,
        public_key: "FLWPUBK-d4d43e900b43e64805dee3f214cf3500-X",
        tx_ref: generateReference(),
        amount: parseInt(amount),
        currency: currency,
        payment_options: paymentOptions,
        meta: {
            source: metaSource,
        },
        customer: {
            email: customerEmail,
            name: customerName,
        },
        customizations: {
            title: title,
            description: description,
            logo: logo,
        },
        callback: function (response) {
            console.log("Full response object:", response);

            // Handle successful payment
            if (response.status === "successful") {
                console.log("Payment successful");
                sendFormData(currency);
            } else {
                console.log("Payment failed with response:", response);
            } 
        },
    });
}

function generateReference() {
    return 'tx_' + Math.random().toString(36).substr(2, 9);
}

function sendFormData(currency) {
    const formId = currency === "NGN" ? "nairadepositform" : "dollarpaymentform";
    const form = document.getElementById(formId);
    const formData = new FormData(form);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", form.action, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // Handle the response from the server after form submission
                console.log("Form data sent successfully:", xhr.responseText);
                 window.location.href = "https://growsocial.com.ng/dashboard.php";

            } else {
                console.error("Error sending form data. Status code:", xhr.status);
            }
        }
    };

    xhr.send(formData);
    console.log(formData);
}
</script>

        <script src="https://checkout.flutterwave.com/v3.js"></script>

<script>
function payEscrow(currency) {
 let amount;
    if (currency === "NGN") {
        amount = document.getElementById("amountprice").value;
    } else {
        amount = document.getElementById("amountprice").value;
    } 
    const paymentOptions = "card, banktransfer, ussd";
    const metaSource = "docs-inline-test";
    const customerEmails = document.getElementById("buyeremail").value;
   // const customerName = document.getElementById("depositorname").value;
    const title = document.getElementById("companyname").value;
    const descriptions = document.getElementById("purchaseDescription").value;
    const logo = "https://growsocial.com.ng/black_white_grow_logo__1_-removebg-preview.png";

    FlutterwaveCheckout({
        public_key: "FLWPUBK_TEST-acc539ebd2b1244fcf32835b66301422-X",
       // public_key: "FLWPUBK-d4d43e900b43e64805dee3f214cf3500-X",
        tx_ref: generateReference(),
        amount: parseInt(amount),
        currency: currency,
        payment_options: paymentOptions,
        meta: {
            source: metaSource,
        },
        customer: {
            email: customerEmails,
        //    name: customerName,
        },
        customizations: {
            title: title,
            description: descriptions,
            logo: logo,
        },
        callback: function (response) {
            console.log("Full response object:", response);

            // Handle successful payment
            if (response.status === "completed") {
                console.log("Payment successful");
                sendPayEscrowFormData(currency);
            } else {
                console.log("Payment failed with response:", response);
            } 
        },
    });
}

function generateReference() {
    return 'tx_' + Math.random().toString(36).substr(2, 9);
}

function sendPayEscrowFormData(currency) {
    const form = document.getElementById("productformescrow"); // Get the form element
    if (!form) {
        console.log("Form element not found.");
        return;
    }

    const formData = new FormData(form);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", form.action, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // Handle the response from the server after form submission
                console.log("Form data sent successfully:", xhr.responseText);
                 window.location.href = "https://growsocial.com.ng/dashboard.php";

            } else {
                console.error("Error sending form data. Status code:", xhr.status);
            }
        }
    };

    xhr.send(formData);
    console.log(formData);
}
</script>


</div>



<div class="contactsgetter" style="display:none">
    
<h3> Request Contacts </h3>

<style>
  #contactform, #contactformescrow {
    display: flex;
    flex-direction: column;
  }

  #contactform label {
 }

 
  #contactform select, #contactform textarea, #contactform input, #contactform file{
      margin-bottom: 10px;
  }
  
  #contactformescrow select, #contactformescrow textarea, #contactformescrow input, #contactformescrow file {
      margin-bottom: 10px;
  } 
  
</style>
 
<form id="contactform" class="contactforma" action="contactgetter.php" method="post" onsubmit="return validateForm4 && validatecontactForm()"> 

<input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" hidden>


 <label>Contact Card Name</label>
    <input type="text" name="contactcardname" class="contactcardname" id="contactxxx" placeholder="Generate Contact Name" readonly required>
<button type="button" id="generaterefid">Generate</button>
   
 <label> Naira Wallet Balance </label>
<input type="text" class="nairaBalanceInput4" value="<?php echo isset($nairaBalance) ? number_format($nairaBalance, 2) : '0.00'; ?>" name="nairawalletbalance" readonly>

   <label> Dollar Wallet Balance </label>

<input type="text" class="dollarBalanceInput4" value="<?php echo isset($dollarBalance) ? number_format($dollarBalance, 4) : '0.0000'; ?>" name="dollarwalletbalance" readonly>


<label> Currency </label> 
  <select id="currency" class="currency4" name="currency" onchange="toggleRates4(); calculateAmountPayable4(); " required> 
    <option value=""> Select Currency </option>
    <option value="NGN"> Naira </option>
    <option value="USD"> Dollars </option>
</select>


<label id="nairalabel" class="nairalabel4" style="display: none;" >Rate in Naira </label>
<select id="nairarate" class="nairarate4" name="nairarate" style="display: none;" onchange="calculateAmountPayable4()">
    <option value="" selected disabled> Select Per-View Rate</option>
    <option value="50"> N50 per contacts </option>
    <option value="100"> N100 per contacts </option>
  </select>
 
  <label id="dollarlabel" class="dollarlabel4" style="display: none;" >Rate in Dollars </label>
  <select id="dollarrate" class="dollarrate4" name="dollarrate"  style="display: none;" onchange="calculateAmountPayable4()"> 
    <option value="" selected disabled > Select Per-View Rate</option>
    <option value="0.005"> $0.005 per contacts </option>
    <option value="0.01"> $0.01 per contacts </option>

  </select> 

    <label>Contacts Needed </label>
    <input type="number" class="totalview4" id="totalview" value="" name="viewsneeded" required  oninput="calculateAmountPayable4();" min="1" step="1">

    <label>Total Cost to Incur for Campaign</label>
    <input type="number" class="amountpayable4" value="" id="amountpayable" name="amountincurred" placeholder="" readonly required>
    
<p id="balanceError" class="balanceError4" style="color: red; display: none;">Insufficient Balance, Please recharge 
    <span id="rechargeCurrency" class="rechargeCurrency4"></span> balance
</p>

 
    <input type="submit" value="Submit" class="submittbutton ff">
</form>
  
  
<script>
function validatecontactForm() {
    console.log("Validating contact form...");
var inputValuex = document.getElementById("contactform").querySelector(".contactcardname").value;
    console.log("Input value:", inputValuex);
    if (inputValuex.trim() === "") {
        console.log("Contact card name is empty.");
        alert("Please generate a Contact Card Name before submitting.");
        return false; // Prevent form submission
    }
    console.log("Contact card name is not empty. Proceeding with form submission.");
    // Form submission will proceed if the input field has a value
    return true;
}
</script>


    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const generateButton = document.getElementById("generaterefid");
        const referralIdField = document.querySelector("input[name='contactcardname']");

        generateButton.addEventListener("click", function() {
            const referralID = generateReferralID();
            referralIdField.value = referralID;
        });

        function generateReferralID() {
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear().toString();
            const randomString = generateRandomString(2); // Generate an 8-character random string
            const referralID = `contactcard${currentYear}${randomString}`;
            return referralID;
        }

        function generateRandomString(length) {
            const characters = '123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            return result;
        }
    });
</script>


    
    
     </div>


<div class="marketclassy" style="display:none;">

<h3> Upload Product </h3>

<style>

.contactcarda {
    
    display: flex;
    flex-direction: row;
}



 #generaterefid {
     color: #ffffff;
      border: none;
      margin: 0px 0px 10px 0px;
         background-color: #e98c13;

 }
 
 
 
/*#generaterefid {
   padding: 10px 4px 10px 4px; 
    width: 90px;
    position: relative;
    cursor: pointer;
    top: -60px;
    left: 77%;
    font-size: 14px;
    color: #ffffff;
    font-weight:bold;
    border: none;
border-top-right-radius: 4px; 
border-bottom-right-radius: 4px; 
border: none;
} */
 
 
  #productform, #productformescrow {
    display: flex;
    flex-direction: column;
  }

  #productform label {
 }

  .submittbutton {
    margin-top: 10px;
  }
  
  #productform select, #productform textarea, #productform input, #productform file{
      margin-bottom: 10px;
  }
  
  #productformescrow select, #productformescrow textarea, #productformescrow input, #productformescrow file{
      margin-bottom: 10px;
  }
  
  
</style>


<form id="productform" class="prodforma" action="productuploads.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm1()"> 

<input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" hidden>
<input type="hidden" name="phonenumber" value="<?php echo $telephone; ?>">
<input type="hidden" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>">

  <label>Product Name </label>
  <input type="name" class="op" name="productname" placeholder="Name of product" required>
   
   <label>Quantity available </label>
  <input type="number" class="op" name="qtynumber" placeholder="Qty available for sale" required>
  
  <label>Image(s) of Product </label>
<input type="file" class="op" name="photographs[]" required multiple accept="image/jpeg, image/png, image/gif, image/webp">
   
    <label>Product Price </label>
<input type="number" class="op" step="any" name="productprice" id="productprice" placeholder="0.00" min="1" onblur="validateProductPrice();" required>
   <span id="priceError" style="color: red; display: none;">Price should not exceed maximum limit</span>

   <label> Naira Wallet Balance </label>
   
<input type="text" class="nairaBalanceInput1" value="<?php echo isset($nairaBalance) ? number_format($nairaBalance, 2) : '0.00'; ?>" name="nairawalletbalance" readonly>

   <label> Dollar Wallet Balance </label>

<input type="text" class="dollarBalanceInput1" value="<?php echo isset($dollarBalance) ? number_format($dollarBalance, 4) : '0.0000'; ?>" name="dollarwalletbalance" readonly>


<label> Currency </label> 
  <select id="currency" class="currency1" name="currency" onchange="toggleRates1(); calculateAmountPayable1(); " required> 
    <option value=""> Select Currency </option>
    <option value="NGN"> Naira </option>
    <option value="USD"> Dollars </option>
</select>


<label id="nairalabel" class="nairalabel1" style="display: none;" >Rate in Naira </label>
<select id="nairarate" class="nairarate1" name="nairarate" style="display: none;" onchange="calculateAmountPayable1()">
    <option value="" selected disabled> Select Per-View Rate</option>
    <option value="5"> N5 per view </option>
    <option value="6"> N6 per view </option>
    <option value="7"> N7 per view </option> 
    <option value="8"> N8 per view </option>
    <option value="9"> N9 per view </option>
    <option value="10"> N10 per view </option>
  </select>
 
  <label id="dollarlabel" class="dollarlabel1" style="display: none;" >Rate in Dollars </label>
  <select id="dollarrate" class="dollarrate1" name="dollarrate"  style="display: none;" onchange="calculateAmountPayable1()"> 
    <option value="" selected disabled > Select Per-View Rate</option>
    <option value="0.005"> $0.005 per view </option>
    <option value="0.006"> $0.006 per view </option> 
    <option value="0.007"> $0.007 per view </option>
    <option value="0.008"> $0.008 per view </option>
    <option value="0.009"> $0.009 per view </option>
    <option value="0.01"> $0.01 per view </option>

  </select> 

    <label>Views Needed </label>
    <input type="number" class="totalview1" id="totalview" value="" name="viewsneeded" required  oninput="calculateAmountPayable1();" min="1" step="1">

    <label>Total Cost to Incur for Campaign</label>
    <input type="number" class="amountpayable1" value="" id="amountpayable" name="amountincurred" placeholder="" readonly required>
    
<p id="balanceError" class="balanceError1" style="color: red; display: none;">Insufficient Balance, Please recharge 
    <span id="rechargeCurrency" class="rechargeCurrency1"></span> balance
</p>

<label>Description of Products</label>
<textarea cols="10" rows="5" name="descrproduct" class="op"></textarea>


<label>Product Category</label>
<select name="productcategory">
  <option value="Religion">Religion</option>
  <option value="Sports">Sports</option>
  <option value="Politics">Politics</option>
  <option value="Adventure">Adventure</option>
  <option value="Breaking News">Breaking News</option>
  <option value="Entertainment">Entertainment</option>
  <option value="Fashion">Fashion</option>
  <option value="Health">Health</option>
  <option value="Technology">Technology</option>
  <option value="Science">Science</option>
  <option value="Education">Education</option>
  <option value="Music">Music</option>
  <option value="Food">Food</option>
  <option value="Travel">Travel</option>
  <option value="Business">Business</option>
  <option value="Art">Art</option>
  <option value="Comedy">Comedy</option>
  <option value="History">History</option>
  <option value="Nature">Nature</option>
  <option value="Pets">Pets</option>
  <option value="Fitness">Fitness</option>
  <option value="Gaming">Gaming</option>
  <option value="Agriculture">Agriculture</option>
  <option value="Relationship">Relationship</option>
  <option value="Entreprenuership">Entreprenuership</option>

</select>

  <input type="submit" value="submit" class="submittbutton ff">
</form>
<script>
    document.getElementById('productprice').addEventListener('blur', function(event) {
        var productPrice = parseFloat(this.value);
        var currency = document.getElementById('currency').value;
        var maxPrice;

        // Set maximum price based on currency
        if (currency === 'NGN') {
            maxPrice = 500000; // Maximum price in NGN
        } else if (currency === 'USD') {
            maxPrice = 1000; // Maximum price in USD
        }

        // Validate product price
        if (productPrice > maxPrice) {
            document.getElementById('priceError').textContent = 'Price should not exceed ' + maxPrice;
            document.getElementById('priceError').style.display = 'block';
            // Clear price to 0
            this.value = "";
        } else {
            document.getElementById('priceError').style.display = 'none';
        }
    });
</script>

</div>


<div class="supportclassy" style="display:none;">
    
       <style>
   #supportform {
    display: flex;
    flex-direction: column;
  }

  #flol {
    width: 200px; 
  }
  
  #supportform select, #supportform textarea, #supportform input {
      margin-bottom: 10px;
  }
</style>
    <h3> Query Support </h3>
    
    
<form id="supportform" method="post" action="support.php" enctype="multipart/form-data">
    <label>Your query here</label>
    
    <input type="hidden" name="email" value="<?php echo $email;?>">
<textarea cols="10" rows="5" name="supportquery" class="op"></textarea>

<label for="relatedissue">Issue</label>
<select name="relatedissue" id="relatedissue" required>
        <option value="" selected disabled>Select an issue</option>
    <option value="payment">Payment</option>
    <option value="uploads">Uploads</option>
    <option value="technical">Technical Issues</option>
    <option value="content">Content Issues</option>
    <option value="ux">User Experience (UX) Issues</option>
    <option value="accessibility">Accessibility Issues</option>
    <option value="performance">Performance Issues</option>
    <option value="ecommerce">E-commerce Issues</option>
    <option value="legal">Legal and Compliance Issues</option>
    <option value="marketing">Marketing and Conversion Optimization Issues</option>
        <option value="others">Others</option>

</select>


<label> Priority </label>
<select name="priority" id="priority">  
<option> High </option>
<option> Medium </option>
<option> Low </option>
</select>

<label> Attachments </label>
<input type="file" name="attachments[]" multiple>

  <input type="submit" id="flol"  value="submit" class="submittbutton ff">
    
    </form>
    
    
</div>

<div class="webclassy" style="display:none;">
    <style>
  #webform {
    display: flex;
    flex-direction: column;
  }

  #webform label {
 }

  .submittbutton {
    margin-top: 10px;
  }
  
  #webform select, #webform textarea, #webform input {
      margin-bottom: 10px;
  }
</style>

    <h3>Upload Web Content</h3>

<form id="webform" action="webuploads.php" method="post" onsubmit="return validateForm2()"> 


<input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" hidden>

  <label>Website Name </label>
  <input type="name" class="op" name="websitename" placeholder=" Name of website" required> 
   
     <label>Website Url </label>
   <input type="url" class="op" name="websiteurl" placeholder="https://example.com" required>
   
   <label> Naira Wallet Balance </label>
   
<input type="text" id="nairaBalanceInput" class="nairaBalanceInput2" value="<?php echo isset($nairaBalance) ? number_format($nairaBalance, 2) : '0.00'; ?>" name="nairawalletbalance" readonly>

   <label> Dollar Wallet Balance </label>

<input type="text" id="dollarBalanceInput" class="dollarBalanceInput2" value="<?php echo isset($dollarBalance) ? number_format($dollarBalance, 4) : '0.0000'; ?>" name="dollarwalletbalance" readonly>




<label> Currency </label> 
  <select id="currency" class="currency2" name="currency" onchange="toggleRates2(); calculateAmountPayable2(); " required> 
    <option value=""> Select Currency </option>
    <option value="NGN"> Naira </option>
    <option value="USD"> Dollars </option>
</select>
 
<label id="nairalabel" style="display: none;" class="nairalabel2" >Rate in Naira </label>
<select id="nairarate" name="nairarate" class="nairarate2" style="display: none;" onchange="calculateAmountPayable2()">
    <option value="" selected disabled> Select Per-View Rate</option>
     <!--   <option value="3"> N3 per click </option>
        <option value="4"> N4 per click </option> --> 
    <option value="50"> N50 per click </option> 
    <option value="60"> N60 per click </option>
    <option value="70"> N70 per click </option> 
    <option value="80"> N80 per click </option>
    <option value="90"> N90 per click </option>
    <option value="100"> N100 per click </option>
  </select>
 
  <label id="dollarlabel" class="dollarlabel2" style="display: none;" >Rate in Dollars </label>
  <select id="dollarrate" name="dollarrate" class="dollarrate2"  style="display: none;" onchange="calculateAmountPayable2()"> 
    <option value="" selected disabled > Select Per-View Rate</option>
    <option value="0.005"> $0.005 per click </option>
    <option value="0.006"> $0.006 per click </option> 
    <option value="0.007"> $0.007 per click </option>
    <option value="0.008"> $0.008 per click </option>
    <option value="0.009"> $0.009 per click </option>
    <option value="0.01"> $0.01 per click </option>

  </select> 

    <label>Views Needed </label>
    <input type="number" id="totalview" value="" class="totalview2" name="viewsneeded" required  oninput="calculateAmountPayable2();" min="1" step="1">
    
    <label> Target Location (optional) </label>
<select id="selectcountry" multiple>
    <option value="Nigeria">Nigeria</option>
    <option value="United States">United States</option>
    <option value="United Kingdom">United Kingdom</option>
    <option value="Canada">Canada</option>
</select>

    <label>Total Cost to Incur for Campaign</label>
    <input type="number" value="" id="amountpayable" class="amountpayable2" name="amountincurred" placeholder="" readonly required>
    
<p id="balanceError" class="balanceError2" style="color: red; display: none;">Insufficient Balance, Please recharge 
    <span id="rechargeCurrency" class="rechargeCurrency2"></span> balance
</p>

<label>Description of Website Content</label>
<textarea cols="10" rows="5" name="descrweb" class="op"></textarea>



<label>Category</label>
<select name="webcategory">
  <option value="Religion">Religion</option>
  <option value="Sports">Sports</option>
  <option value="Politics">Politics</option>
  <option value="Adventure">Adventure</option>
  <option value="Breaking News">Breaking News</option>
  <option value="Entertainment">Entertainment</option>
  <option value="Fashion">Fashion</option>
  <option value="Health">Health</option>
  <option value="Technology">Technology</option>
  <option value="Science">Science</option>
  <option value="Education">Education</option>
  <option value="Music">Music</option>
  <option value="Food">Food</option>
  <option value="Travel">Travel</option>
  <option value="Business">Business</option>
  <option value="Art">Art</option>
  <option value="Comedy">Comedy</option>
  <option value="History">History</option>
  <option value="Nature">Nature</option>
  <option value="Pets">Pets</option>
  <option value="Fitness">Fitness</option>
  <option value="Gaming">Gaming</option>
  <option value="Agriculture">Agriculture</option>
  <option value="Relationship">Relationship</option>
  <option value="Entreprenuership">Entreprenuership</option>

</select>

  <input type="submit" value="submit" class="submittbutton ff">
</form> 
</div>


<div class="contactclassy" style="display:none;">
    
    <h3> Contact Gain</h3>
    
    
    

<?php

// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the session or set it to an empty string
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Prepare the first SQL statement with a placeholder for email
$sql = "SELECT *, (viewsneeded - views) AS difference
        FROM contactgain 
        WHERE email = ? 
        AND viewsneeded != views
        ORDER BY email;";

// Prepare and bind the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);

// Execute the query
$stmt->execute();
$result = $stmt->get_result();
// Initialize offset for LIMIT
$offset = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $viewsneeded = $row['difference'];
        $contactcardname = $row['contactcardname'];

        // Construct the SQL query for growsignup using the viewsneeded value
      $sql_growsignup = "SELECT gs.* 
                   FROM growsignup gs
                   LEFT JOIN (
                        SELECT 
                            poster_email,
                            GROUP_CONCAT(user_email) AS exlusivemail
                        FROM 
                            contactgainpayments
                        GROUP BY 
                            poster_email
                   ) AS cp ON FIND_IN_SET(gs.email, cp.exlusivemail) > 0
                   WHERE gs.email != ? 
                   AND gs.contacts_shared = 'no'
                   AND gs.userType = 'follower'
                   AND cp.poster_email IS NULL
                   ORDER BY gs.id
                   LIMIT ?, ?";

        // Prepare and bind the statement for growsignup
        $stmt_growsignup = $conn->prepare($sql_growsignup);
        $stmt_growsignup->bind_param("sii", $email, $offset, $viewsneeded);
        
        // Execute the statement
        $stmt_growsignup->execute();
        
        // Fetch the result
        $result_growsignup = $stmt_growsignup->get_result();
        
        // Process the result for contactcardname
        if ($result_growsignup->num_rows > 0) {
            echo '<table>'; 
            echo '<thead>';
            echo '<tr>';
            echo '<th class="colorclassy"> Contact Card Name </th>';
            echo '<th class="colorclassy"> Email </th>';
            echo '<th class="colorclassy"> Telephone </th>';
            echo '<th class="colorclassy"> Action </th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Initialize an array to store contact information
            $all_contacts = array();
            $all_contactsmethod = array();
            
            while ($row_growsignup = $result_growsignup->fetch_assoc()) {
                echo '<tr>';
               // echo '<td>' . $row['contactcardname'] . '</td>';

              echo '<td data-contactcardname="' . $contactcardname . '" 
              data-currency="' . $row['currency'] . '" 
              data-email="' . $row['email'] . '" 
              data-emailclient = "'. $row_growsignup['email']. '"
              data-contactrate="' . $row['perViewRate'] . '" 
              data-contactviews="' . $row['views'] . '" 
              data-uploaddate="' . $row['uploaddate'] . '" 
              data-contact-id="' . $row['id'] . '" 
              class="">' .  $row['contactcardname'] . '</td>';

                $emailgrow = $row_growsignup['email'];
                $telephonegrow = $row_growsignup["telephone"]; 
                $partial_email = substr($emailgrow, 0, 3) . "xxxxxxx"; // Display only first 3 digits
                $partial_telephone = substr($telephonegrow, 0, 3) . "xxxxxxx"; // Display only first 3 digits

                echo '<td id="full_email">' .  $partial_email . '</td>';
                echo '<td id="full_telephone">' . $partial_telephone . '</td>';
                echo '<td><button data-contactcardname="' . $contactcardname . '" 
              data-currency="' . $row['currency'] . '" 
              data-email="' . $row['email'] . '" 
              data-emailclient="' . $row_growsignup['email'] . '"
              data-contactrate="' . $row['perViewRate'] . '" 
              data-contactviews="' . $row['views'] . '" 
              data-uploaddate="' . $row['uploaddate'] . '" 
              data-contact-id="' . $row['id'] . '" 
              class="contactgainer reveal-button" 
              data-full-email="' . $emailgrow . '" 
              data-full-telephone="' . $telephonegrow . '" hidden>Reveal Contacts</button></td>';
                echo '</tr>';



  $all_contacts[$row['contactcardname']][] = array(
            'contactId' => $row['id'],
            'clientEmail' => $row_growsignup['email'],
            'posterEmail' => $row['email'],
            'contactRate' => $row['perViewRate'],
            'currency' => $row['currency'],
            'telephone' => $telephonegrow,
            'email' => $emailgrow,
            
            );
           
             $all_contactsmethod[] = array(
            'contactId' => $row['id'],
            'clientEmail' => $row_growsignup['email'],
            'posterEmail' => $row['email'],
            'contactRate' => $row['perViewRate'],
            'currency' => $row['currency'] 
            // Add other fields as needed
        );
   
                // Store contact information in the array
            }

            echo '</tbody>';
            echo '</table>';
    
            // Output the download link for all contacts
          //  echo '<p><a href="downloads_all_contacts.php?contacts=' . urlencode(json_encode($all_contacts)) . '" class="contactgain">Download All Contacts</a></p>';
echo '<p><a target="_blank" href="downloads_all_contacts.php?contacts=' . urlencode(json_encode($all_contacts)) . '" class="contactgain" data-contacts="' . htmlspecialchars(json_encode($all_contactsmethod), ENT_QUOTES, 'UTF-8') . '">Download All Contacts</a></p>';

            echo '<br>';
        } else {
            echo '<p>No contacts yet, please come back soon!</p>';
        } 

        // Close the result set for growsignup
        $stmt_growsignup->close();

        // Update offset for next iteration
        $offset += $viewsneeded;
        
      //  echo "viewsneeded: $viewsneeded, offset: $offset<br>";
    }
} else {
    echo '<p>You are yet to request for contacts, kindly use Contact Gain tool.</p>';
}

$stmt->close();

// Close the database connection
$conn->close();
?>

<script>
// Function to convert array of objects to a URL-encoded string
function arrayToUrlEncodedString(array) {
    // Map each object in the array to a URL-encoded string
    var encodedStrings = array.map(obj => {
        // Map each key-value pair in the object to a URL-encoded string
        var keyValuePairs = Object.entries(obj).map(([key, value]) => {
            // Encode the key and value
            var encodedKey = encodeURIComponent(key);
            var encodedValue = encodeURIComponent(value);
            // Concatenate the encoded key and value with '='
            return `${encodedKey}=${encodedValue}`;
        });
        // Join the key-value pairs with '&'
        return keyValuePairs.join('&');
    });
    // Join the encoded strings with '&'
    return encodedStrings.join('&');
}

// Function to send contact data to the 'payforcontacts.php' script
function sendContactScript(requestData) { 
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'payforcontacts.php', true); 
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.withCredentials = true;
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            console.log('Request to the contact script successful:', xhr.responseText);
        } else {
            console.error('Request to the contact script failed:', xhr.statusText);
        }
    };
    xhr.onerror = function () {
        console.error('Network error occurred');
    };
    xhr.send(requestData);
}

// Function to send data to the 'update_viewed_contacts.php' script
function sendUpdateGain(requestData) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_viewed_contacts.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.withCredentials = true;
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            console.log('Request to the updategain script successful:', xhr.responseText);
            
            // Redirect to confirmation.php after sending data and reloading the page
        window.location.href = 'confirmation.php';
        } else {
            console.error('Request to the updategain script failed:', xhr.statusText);
        }
    };
    xhr.onerror = function () {
        console.error('Network error occurred');
    };
    xhr.send(requestData);
}

$(document).ready(function () {
    $('.contactgain').on('click', function () {
        var dataArray = $(this).data('contacts');
        
        // Iterate over the array of data
        dataArray.forEach(function(data) {
            // Convert each set of data to a URL-encoded string
            var requestData = arrayToUrlEncodedString([data]);
            
            // Call the functions to send data to PHP scripts for each set of data
            sendContactScript(requestData);
            sendUpdateGain(requestData);
        });

    });
});
</script>



    
  <script>
    var revealButtons = document.querySelectorAll('.reveal-button');

    function revealContacts(event) {
        var button = event.currentTarget;
        var fullTelephone = button.getAttribute('data-full-telephone');
        var fullEmail = button.getAttribute('data-full-email');

        var row = button.closest('tr');
        var emailCell = row.querySelector('#full_email');
        var telephoneCell = row.querySelector('#full_telephone');

        emailCell.textContent = fullEmail;
        telephoneCell.textContent = fullTelephone;

        button.style.display = 'none'; // Hide the reveal button
    }

    revealButtons.forEach(function(button) {
        button.addEventListener('click', revealContacts);
    });
</script>

    <script>
    // Function to send data using XHR to the first PHP script
function sendDataToContactScript(contactId, clientEmail, posterEmail, contactRate, currency) { 
    var xhr = new XMLHttpRequest();
    var requestData = 'contactId=' + encodeURIComponent(contactId) +
          '&clientEmail=' + encodeURIComponent(clientEmail) +
          '&posterEmail=' + encodeURIComponent(posterEmail) +
          '&contactRate=' + encodeURIComponent(contactRate) +
          '&currency=' + encodeURIComponent(currency);

    xhr.open('POST', 'payforcontacts.php', true); 
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.withCredentials = true;

    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            console.log('Request to the contact script successful:', xhr.responseText);
        } else {
            console.error('Request to the contact script failed:', xhr.statusText);
        }
    };

    xhr.onerror = function () {
        console.error('Network error occurred');
    };

    xhr.send(requestData);
}

// Function to send data using XHR to the second PHP script
function sendDataToUpdateGain(contactId, clientEmail) {
    var xhr = new XMLHttpRequest();
    var requestData = 'contactId=' + encodeURIComponent(contactId) +
                      '&clientEmail=' + encodeURIComponent(clientEmail);

    xhr.open('POST', 'update_viewed_contacts.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.withCredentials = true;

    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            console.log('Request to the updategain script successful:', xhr.responseText);
        } else {
            console.error('Request to the updategain script failed:', xhr.statusText);
        }
    };

    xhr.onerror = function () {
        console.error('Network error occurred');
    };

    xhr.send(requestData);
}

$(document).ready(function () {
    $('.contactgainer').one('click', function () {    
        var contactId = $(this).data('contact-id');
        var clientEmail = $(this).data('full-email');
        var posterEmail = $(this).data('email');
        var contactRate = $(this).data('contactrate');
        var currency = $(this).data('currency');
        
        // Call the functions to send data to PHP scripts
        sendDataToContactScript(contactId, clientEmail, posterEmail, contactRate, currency);
        sendDataToUpdateGain(contactId, clientEmail);
    });
});

</script>

</div>


<div class="followthemall" style="display:none;">
    
   <!-- 
    
    <h3>Your Follow List </h3>
    -->

    
<div class="followfeeder"></div>
<div class="followfeed"></div>
</div>



<div class="followclassy" style="display:none;">
<h3>Follower Request </h3>

<style>
    
  #followform {
    display: flex;
    flex-direction: column;
  }

  #followform select, #followform textarea, #followform input, #followform file{
      margin-bottom: 10px;
  }
</style>

<form id="followform" action="followerrequest.php" method="post" onsubmit="return validateForm5()"> 


<input type="text" name="followname" value="Request to follow <?php echo $fullname; ?>" readonly>

<input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" hidden>



<label> Naira Wallet Balance </label>
   
<input type="text" id="nairaBalanceInput" class="nairaBalanceInput5" value="<?php echo isset($nairaBalance) ? number_format($nairaBalance, 2) : '0.00'; ?>" name="nairawalletbalance" readonly>

   <label> Dollar Wallet Balance </label>

<input type="text" class="dollarBalanceInput5" value="<?php echo isset($dollarBalance) ? number_format($dollarBalance, 4) : '0.0000'; ?>" name="dollarwalletbalance" readonly>




<label> Currency </label> 
<select class="currency5" name="currency" onchange="toggleRates5(); calculateAmountPayable5();" required>
    <option value=""> Select Currency </option>
    <option value="NGN"> Naira </option>
    <option value="USD"> Dollars </option>
</select>

<label class="nairalabel5" style="display: none;" >Rate in Naira </label>
<select class="nairarate5" name="nairarate" style="display: none;" onchange="calculateAmountPayable5();">
    <option value="" selected disabled> Select Per-View Rate</option>
    <option value="3"> N3 per view </option>
    <option value="4"> N4 per view </option>
    <option value="5"> N5 per view </option>
    <option value="6"> N6 per view </option>
    <option value="7"> N7 per view </option> 
    <option value="8"> N8 per view </option>
    <option value="9"> N9 per view </option>
    <option value="10"> N10 per view </option>
  </select>
 
  <label class="dollarlabel5" style="display: none;" >Rate in Dollars </label>
  <select class="dollarrate5" name="dollarrate"  style="display: none;" onchange="calculateAmountPayable5();"> 
    <option value="" selected disabled > Select Per-View Rate</option>
    <option value="0.005"> $0.005 per view </option>
    <option value="0.006"> $0.006 per view </option> 
    <option value="0.007"> $0.007 per view </option>
    <option value="0.008"> $0.008 per view </option>
    <option value="0.009"> $0.009 per view </option>
    <option value="0.01"> $0.01 per view </option>

  </select> 

    <label>Views Needed </label>
    <input type="number" class="totalview5" value="" name="viewsneeded" required  oninput="calculateAmountPayable5();" min="1" step="1">

    <label>Total Cost to Incur for Campaign</label>
    <input type="number" value="" class="amountpayable5" name="amountincurred" placeholder="" readonly required>
    
<p id="balanceError" class="balanceError5" style="color: red; display: none;">Insufficient Balance, Please recharge 
    <span id="rechargeCurrency" class="rechargeCurrency5"></span> balance
</p>

 <input type="submit" value="submit" class="submittbutton ff">
  


</form>
</div>


<div class="uploadblog" style="display: none;">
    <h2>Upload Blog Post</h2>

    <style>
      
       form input,
form select, textarea {
    width: 100% !important;
    padding: 10px !important;
    margin-bottom: 10px !important;
    border: 1px solid #ccc !important;
    border-radius: 5px !important;
    box-sizing: border-box !important;
}


        
         #file{
            width: 30%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .classysubmit {
            background-color: #4caf50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
 
        .classysubmit:hover {
            background-color: #45a049;
        }
    </style>


   <form action="uploadblog.php" id="blogform" method="post" enctype="multipart/form-data">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required><br>
    <!-- Assuming $fullname and $email are set earlier in your PHP code -->
    <input type="text" id="author" value="<?php echo $fullname; ?>" name="author" required hidden>
    <input type="email" id="email" value="<?php echo $email; ?>" name="email" required hidden>
    <label for="kkk">Content:</label><br>
    <textarea id="vidcontent1" name="content" rows="10"></textarea> <br>
    <label for="tags">Tags:</label><br>
    <input type="text" id="tags" name="tags" placeholder="Enter tags separated by commas"><br>
    
    

<label> Naira Wallet Balance </label><br>
   
<input type="text" id="nairaBalanceInput" class="nairaBalanceInput6" value="<?php echo isset($nairaBalance) ? number_format($nairaBalance, 2) : '0.00'; ?>" name="nairawalletbalance" readonly>

   <label> Dollar Wallet Balance </label>

<input type="text" class="dollarBalanceInput6" value="<?php echo isset($dollarBalance) ? number_format($dollarBalance, 4) : '0.0000'; ?>" name="dollarwalletbalance" readonly>

<label> Currency </label> <br>
<select class="currency6" name="currency" onchange="toggleRates6(); calculateAmountPayable6();" required>
    <option value=""> Select Currency </option>
    <option value="NGN"> Naira </option>
    <option value="USD"> Dollars </option>
</select>

<label class="nairalabel6" style="display: none;" >Rate in Naira </label><br>
<select class="nairarate6" name="nairarate" style="display: none;" onchange="calculateAmountPayable6();">
    <option value="" selected disabled> Select Per-View Rate</option>
    <option value="3"> N3 per view </option>
    <option value="4"> N4 per view </option>
    <option value="5"> N5 per view </option>
    <option value="6"> N6 per view </option>
    <option value="7"> N7 per view </option> 
    <option value="8"> N8 per view </option>
    <option value="9"> N9 per view </option>
    <option value="10"> N10 per view </option>
  </select>
 
  <label class="dollarlabel6" style="display:none;" >Rate in Dollars </label><br>
  <select class="dollarrate6" name="dollarrate"  style="display: none;" onchange="calculateAmountPayable6();"> 
    <option value="" selected disabled > Select Per-View Rate</option> 
    <option value="0.005"> $0.005 per view </option>
    <option value="0.006"> $0.006 per view </option> 
    <option value="0.007"> $0.007 per view </option>
    <option value="0.008"> $0.008 per view </option>
    <option value="0.009"> $0.009 per view </option>
    <option value="0.01"> $0.01 per view </option>
 
  </select> 

    <label>Views Needed </label><br>
    <input type="number" class="totalview6" value="" name="viewsneeded" required  oninput="calculateAmountPayable6();" min="1" step="1">

    <label>Total Cost to Incur for Campaign</label><br>
    <input type="number" value="" class="amountpayable6" name="amountincurred" placeholder="" readonly required>
    
<p id="balanceError" class="balanceError6" style="color: red; display: none;">Insufficient Balance, Please recharge 
    <span id="rechargeCurrency" class="rechargeCurrency6"></span> balance
</p>

    <label for="content">Photographs:</label><br>
    <input type="file" id="file" name="photographs[]" multiple>
    <input type="submit" value="Upload" class="classysubmit">
</form>



</div>




<div class="uploadclassy" style="display:none">

<h3>Upload Content</h3>

<form id="uploadform" action="videouploads.php" method="post" onsubmit="return validateForm3()"> 


<input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" hidden>



  <label>Title of uploaded video </label>
  <input type="name" class="op" name="videotitle" placeholder="Title of video uploaded" required> 
   
   
   <label> Naira Wallet Balance </label>
   
<input type="text" id="nairaBalanceInput" class="nairaBalanceInput3" value="<?php echo isset($nairaBalance) ? number_format($nairaBalance, 2) : '0.00'; ?>" name="nairawalletbalance" readonly>

   <label> Dollar Wallet Balance </label>

<input type="text" class="dollarBalanceInput3" value="<?php echo isset($dollarBalance) ? number_format($dollarBalance, 4) : '0.0000'; ?>" name="dollarwalletbalance" readonly>




<label> Currency </label> 
<select class="currency3" name="currency" onchange="toggleRates3(); calculateAmountPayable3();" required>
    <option value=""> Select Currency </option>
    <option value="NGN"> Naira </option>
    <option value="USD"> Dollars </option>
</select>

<label class="nairalabel3" style="display: none;" >Rate in Naira </label>
<select class="nairarate3" name="nairarate" style="display: none;" onchange="calculateAmountPayable3()">
    <option value="" selected disabled> Select Per-View Rate</option>
    <option value="3"> N3 per view </option>
    <option value="4"> N4 per view </option>
    <option value="5"> N5 per view </option>
    <option value="6"> N6 per view </option>
    <option value="7"> N7 per view </option> 
    <option value="8"> N8 per view </option>
    <option value="9"> N9 per view </option>
    <option value="10"> N10 per view </option>
  </select>
 
  <label class="dollarlabel3" style="display: none;" >Rate in Dollars </label>
  <select class="dollarrate3" name="dollarrate"  style="display: none;" onchange="calculateAmountPayable3()"> 
    <option value="" selected disabled > Select Per-View Rate</option>
    <option value="0.005"> $0.005 per view </option>
    <option value="0.006"> $0.006 per view </option> 
    <option value="0.007"> $0.007 per view </option>
    <option value="0.008"> $0.008 per view </option>
    <option value="0.009"> $0.009 per view </option>
    <option value="0.01"> $0.01 per view </option>

  </select> 

    <label>Views Needed </label>
    <input type="number" class="totalview3" value="" name="viewsneeded" required  oninput="calculateAmountPayable3();" min="1" step="1">

    <label>Total Cost to Incur for Campaign</label>
    <input type="number" value="" class="amountpayable3" name="amountincurred" placeholder="" readonly required>
    
<p id="balanceError" class="balanceError3" style="color: red; display: none;">Insufficient Balance, Please recharge 
    <span id="rechargeCurrency" class="rechargeCurrency3"></span> balance
</p>


<input type="number" name="splitdollar" value="" id="splitdollar" readonly hidden>
<input type="number" name="splitnaira" value="" id="splitnaira" readonly hidden>

<script>
    function toggleRates1() {
    var currency = document.querySelector('.currency1').value;
    var nairaLabel = document.querySelector('.nairalabel1');
    var nairaRate = document.querySelector('.nairarate1');
    var dollarLabel = document.querySelector('.dollarlabel1');
    var dollarRate = document.querySelector('.dollarrate1');

    if (currency === '') {
        dollarRate.value = ''; // Assuming dollarRate is an input element
        nairaRate.value = ''; // Assuming nairaRate is an input element
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        
    } else if (currency === 'NGN') {
        nairaLabel.style.display = 'block';
        nairaRate.style.display = 'block';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        nairaRate.setAttribute('required', 'required');
        dollarRate.removeAttribute('required');

        // Reset dollar rate to default
        dollarRate.value = ''; // Assuming dollarRate is an input element
    } else if (currency === 'USD') {
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'block';
        dollarRate.style.display = 'block';
        nairaRate.removeAttribute('required');
        dollarRate.setAttribute('required', 'required');

        // Reset naira rate to default
        nairaRate.value = ''; // Assuming nairaRate is an input element
    }

    calculateAmountPayable1();
}

    function calculateAmountPayable1() {
            console.log("calculateAmountPayable1 function triggered");

        var currency = document.querySelector('.currency1').value;
        var rateElement = (currency === 'NGN') ? document.querySelector('.nairarate1') : document.querySelector('.dollarrate1');
        var views = document.querySelector('.totalview1').value;
        var amountPayable = views * rateElement.value;

var balanceInput = (currency === 'NGN') ? document.querySelector('.nairaBalanceInput1') : document.querySelector('.dollarBalanceInput1');
var balanceString = balanceInput.value.replace(/,/g, ''); // Remove all commas
var balance = parseFloat(balanceString);

// Alternatively, if you want to handle larger numbers:
var cleanedBalanceString = balanceString.replace(/[^0-9.-]/g, ''); // Remove non-numeric characters
var balance = parseFloat(cleanedBalanceString);

        var balanceError = document.querySelector('.balanceError1');
    var rechargeCurrency = document.querySelector('.rechargeCurrency1');

if (amountPayable <= balance) {
    balanceError.style.display = 'none';
    document.querySelector('.amountpayable1').value = amountPayable.toFixed(4);
} else {
    balanceError.style.display = 'block';

    // Set the rechargeCurrency span content based on the selected currency
    rechargeCurrency.textContent = (currency === 'NGN') ? 'NGN' : 'USD';

    document.querySelector('.amountpayable1').value = '';
}
    }
</script> 

<script>
    function toggleRates2() {
    var currency = document.querySelector('.currency2').value;
    var nairaLabel = document.querySelector('.nairalabel2');
    var nairaRate = document.querySelector('.nairarate2');
    var dollarLabel = document.querySelector('.dollarlabel2');
    var dollarRate = document.querySelector('.dollarrate2');

    if (currency === '') {
        dollarRate.value = ''; // Assuming dollarRate is an input element
        nairaRate.value = ''; // Assuming nairaRate is an input element
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        
    } else if (currency === 'NGN') {
        nairaLabel.style.display = 'block';
        nairaRate.style.display = 'block';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        nairaRate.setAttribute('required', 'required');
        dollarRate.removeAttribute('required');

        // Reset dollar rate to default
        dollarRate.value = ''; // Assuming dollarRate is an input element
    } else if (currency === 'USD') {
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'block';
        dollarRate.style.display = 'block';
        nairaRate.removeAttribute('required');
        dollarRate.setAttribute('required', 'required');

        // Reset naira rate to default
        nairaRate.value = ''; // Assuming nairaRate is an input element
    }

    calculateAmountPayable2();
}

    function calculateAmountPayable2() {
            console.log("calculateAmountPayable2 function triggered");

        var currency = document.querySelector('.currency2').value;
        var rateElement = (currency === 'NGN') ? document.querySelector('.nairarate2') : document.querySelector('.dollarrate2');
        var views = document.querySelector('.totalview2').value;
        var amountPayable = views * rateElement.value;

var balanceInput = (currency === 'NGN') ? document.querySelector('.nairaBalanceInput2') : document.querySelector('.dollarBalanceInput2');
var balanceString = balanceInput.value.replace(/,/g, ''); // Remove all commas
var balance = parseFloat(balanceString);

// Alternatively, if you want to handle larger numbers:
var cleanedBalanceString = balanceString.replace(/[^0-9.-]/g, ''); // Remove non-numeric characters
var balance = parseFloat(cleanedBalanceString);

        var balanceError = document.querySelector('.balanceError2');
    var rechargeCurrency = document.querySelector('.rechargeCurrency2');

if (amountPayable <= balance) {
    balanceError.style.display = 'none';
    document.querySelector('.amountpayable2').value = amountPayable.toFixed(4);
} else {
    balanceError.style.display = 'block';

    // Set the rechargeCurrency span content based on the selected currency
    rechargeCurrency.textContent = (currency === 'NGN') ? 'NGN' : 'USD';

    document.querySelector('.amountpayable2').value = '';
}
    }
</script> 


<script>
    function toggleRates3() {
    var currency = document.querySelector('.currency3').value;
    var nairaLabel = document.querySelector('.nairalabel3');
    var nairaRate = document.querySelector('.nairarate3');
    var dollarLabel = document.querySelector('.dollarlabel3');
    var dollarRate = document.querySelector('.dollarrate3');

    if (currency === '') {
        dollarRate.value = ''; // Assuming dollarRate is an input element
        nairaRate.value = ''; // Assuming nairaRate is an input element
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        
    } else if (currency === 'NGN') {
        nairaLabel.style.display = 'block';
        nairaRate.style.display = 'block';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        nairaRate.setAttribute('required', 'required');
        dollarRate.removeAttribute('required');

        // Reset dollar rate to default
        dollarRate.value = ''; // Assuming dollarRate is an input element
    } else if (currency === 'USD') {
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'block';
        dollarRate.style.display = 'block';
        nairaRate.removeAttribute('required');
        dollarRate.setAttribute('required', 'required');

        // Reset naira rate to default
        nairaRate.value = ''; // Assuming nairaRate is an input element
    }

    calculateAmountPayable3();
}

    function calculateAmountPayable3() {
            console.log("calculateAmountPayable3 function triggered");

        var currency = document.querySelector('.currency3').value;
        var rateElement = (currency === 'NGN') ? document.querySelector('.nairarate3') : document.querySelector('.dollarrate3');
        var views = document.querySelector('.totalview3').value;
        var amountPayable = views * rateElement.value;

var balanceInput = (currency === 'NGN') ? document.querySelector('.nairaBalanceInput3') : document.querySelector('.dollarBalanceInput3');
var balanceString = balanceInput.value.replace(/,/g, ''); // Remove all commas
var balance = parseFloat(balanceString);

// Alternatively, if you want to handle larger numbers:
var cleanedBalanceString = balanceString.replace(/[^0-9.-]/g, ''); // Remove non-numeric characters
var balance = parseFloat(cleanedBalanceString);

        var balanceError = document.querySelector('.balanceError3');
    var rechargeCurrency = document.querySelector('.rechargeCurrency3');

if (amountPayable <= balance) {
    balanceError.style.display = 'none';
    document.querySelector('.amountpayable3').value = amountPayable.toFixed(4);
} else {
    balanceError.style.display = 'block';

    // Set the rechargeCurrency span content based on the selected currency
    rechargeCurrency.textContent = (currency === 'NGN') ? 'NGN' : 'USD';

    document.querySelector('.amountpayable3').value = '';
}
    }
</script> 


<script>
    function toggleRates4() {
    var currency = document.querySelector('.currency4').value;
    var nairaLabel = document.querySelector('.nairalabel4');
    var nairaRate = document.querySelector('.nairarate4');
    var dollarLabel = document.querySelector('.dollarlabel4');
    var dollarRate = document.querySelector('.dollarrate4');

    if (currency === '') {
        dollarRate.value = ''; // Assuming dollarRate is an input element
        nairaRate.value = ''; // Assuming nairaRate is an input element
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        
    } else if (currency === 'NGN') {
        nairaLabel.style.display = 'block';
        nairaRate.style.display = 'block';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        nairaRate.setAttribute('required', 'required');
        dollarRate.removeAttribute('required');

        // Reset dollar rate to default
        dollarRate.value = ''; // Assuming dollarRate is an input element
    } else if (currency === 'USD') {
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'block';
        dollarRate.style.display = 'block';
        nairaRate.removeAttribute('required');
        dollarRate.setAttribute('required', 'required');

        // Reset naira rate to default
        nairaRate.value = ''; // Assuming nairaRate is an input element
    }

    calculateAmountPayable4();
}

    function calculateAmountPayable4() {
            console.log("calculateAmountPayable4 function triggered");

        var currency = document.querySelector('.currency4').value;
        var rateElement = (currency === 'NGN') ? document.querySelector('.nairarate4') : document.querySelector('.dollarrate4');
        var views = document.querySelector('.totalview4').value;
        var amountPayable = views * rateElement.value;

var balanceInput = (currency === 'NGN') ? document.querySelector('.nairaBalanceInput4') : document.querySelector('.dollarBalanceInput4');
var balanceString = balanceInput.value.replace(/,/g, ''); // Remove all commas
var balance = parseFloat(balanceString);

// Alternatively, if you want to handle larger numbers:
var cleanedBalanceString = balanceString.replace(/[^0-9.-]/g, ''); // Remove non-numeric characters
var balance = parseFloat(cleanedBalanceString);

        var balanceError = document.querySelector('.balanceError4');
    var rechargeCurrency = document.querySelector('.rechargeCurrency4');

if (amountPayable <= balance) {
    balanceError.style.display = 'none';
    document.querySelector('.amountpayable4').value = amountPayable.toFixed(4);
} else {
    balanceError.style.display = 'block';

    // Set the rechargeCurrency span content based on the selected currency
    rechargeCurrency.textContent = (currency === 'NGN') ? 'NGN' : 'USD';

    document.querySelector('.amountpayable4').value = '';
}
    }
</script>

<script>
    function toggleRates5() {
    var currency = document.querySelector('.currency5').value;
    var nairaLabel = document.querySelector('.nairalabel5');
    var nairaRate = document.querySelector('.nairarate5');
    var dollarLabel = document.querySelector('.dollarlabel5');
    var dollarRate = document.querySelector('.dollarrate5');

    if (currency === '') {
        dollarRate.value = ''; // Assuming dollarRate is an input element
        nairaRate.value = ''; // Assuming nairaRate is an input element
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        
    } else if (currency === 'NGN') {
        nairaLabel.style.display = 'block';
        nairaRate.style.display = 'block';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        nairaRate.setAttribute('required', 'required');
        dollarRate.removeAttribute('required');

        // Reset dollar rate to default
        dollarRate.value = ''; // Assuming dollarRate is an input element
    } else if (currency === 'USD') {
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'block';
        dollarRate.style.display = 'block';
        nairaRate.removeAttribute('required');
        dollarRate.setAttribute('required', 'required');

        // Reset naira rate to default
        nairaRate.value = ''; // Assuming nairaRate is an input element
    }

    calculateAmountPayable5();
}

    function calculateAmountPayable5() {
            console.log("calculateAmountPayable5 function triggered");

        var currency = document.querySelector('.currency5').value;
        var rateElement = (currency === 'NGN') ? document.querySelector('.nairarate5') : document.querySelector('.dollarrate5');
        var views = document.querySelector('.totalview5').value;
        var amountPayable = views * rateElement.value;

var balanceInput = (currency === 'NGN') ? document.querySelector('.nairaBalanceInput5') : document.querySelector('.dollarBalanceInput5');
var balanceString = balanceInput.value.replace(/,/g, ''); // Remove all commas
var balance = parseFloat(balanceString);

// Alternatively, if you want to handle larger numbers:
var cleanedBalanceString = balanceString.replace(/[^0-9.-]/g, ''); // Remove non-numeric characters
var balance = parseFloat(cleanedBalanceString);

        var balanceError = document.querySelector('.balanceError5');
    var rechargeCurrency = document.querySelector('.rechargeCurrency5');

if (amountPayable <= balance) {
    balanceError.style.display = 'none';
    document.querySelector('.amountpayable5').value = amountPayable.toFixed(4);
} else {
    balanceError.style.display = 'block';

    // Set the rechargeCurrency span content based on the selected currency
    rechargeCurrency.textContent = (currency === 'NGN') ? 'NGN' : 'USD';

    document.querySelector('.amountpayable5').value = '';
}
    }

</script> 


<script>
    function toggleRates6() {
    var currency = document.querySelector('.currency6').value;
    var nairaLabel = document.querySelector('.nairalabel6');
    var nairaRate = document.querySelector('.nairarate6');
    var dollarLabel = document.querySelector('.dollarlabel6');
    var dollarRate = document.querySelector('.dollarrate6');

    if (currency === '') {
        dollarRate.value = ''; // Assuming dollarRate is an input element
        nairaRate.value = ''; // Assuming nairaRate is an input element
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        
    } else if (currency === 'NGN') {
        nairaLabel.style.display = 'block';
        nairaRate.style.display = 'block';
        dollarLabel.style.display = 'none';
        dollarRate.style.display = 'none';
        nairaRate.setAttribute('required', 'required');
        dollarRate.removeAttribute('required');

        // Reset dollar rate to default
        dollarRate.value = ''; // Assuming dollarRate is an input element
    } else if (currency === 'USD') {
        nairaLabel.style.display = 'none';
        nairaRate.style.display = 'none';
        dollarLabel.style.display = 'block';
        dollarRate.style.display = 'block';
        nairaRate.removeAttribute('required');
        dollarRate.setAttribute('required', 'required');

        // Reset naira rate to default
        nairaRate.value = ''; // Assuming nairaRate is an input element
    }

    calculateAmountPayable6();
}

    function calculateAmountPayable6() {
            console.log("calculateAmountPayable6 function triggered");

        var currency = document.querySelector('.currency6').value;
        var rateElement = (currency === 'NGN') ? document.querySelector('.nairarate6') : document.querySelector('.dollarrate6');
        var views = document.querySelector('.totalview6').value;
        var amountPayable = views * rateElement.value;

var balanceInput = (currency === 'NGN') ? document.querySelector('.nairaBalanceInput6') : document.querySelector('.dollarBalanceInput6');
var balanceString = balanceInput.value.replace(/,/g, ''); // Remove all commas
var balance = parseFloat(balanceString);

// Alternatively, if you want to handle larger numbers:
var cleanedBalanceString = balanceString.replace(/[^0-9.-]/g, ''); // Remove non-numeric characters
var balance = parseFloat(cleanedBalanceString);

        var balanceError = document.querySelector('.balanceError6');
    var rechargeCurrency = document.querySelector('.rechargeCurrency6');

if (amountPayable <= balance) {
    balanceError.style.display = 'none';
    document.querySelector('.amountpayable6').value = amountPayable.toFixed(4);
} else {
    balanceError.style.display = 'block';

    // Set the rechargeCurrency span content based on the selected currency
    rechargeCurrency.textContent = (currency === 'NGN') ? 'NGN' : 'USD';

    document.querySelector('.amountpayable6').value = '';
}
    }

</script> 


    <script>
        function openForm() {
            document.getElementById("myForm").style.display = "block";
        }

        function closeForm() {
            document.getElementById("myForm").style.display = "none";
        }
    </script>
    
<script>
  function validateForm1() {
    var amountPayable = document.querySelector("amountpayable1").value;

    // Check if the value is 0.00 or blank
    if (amountPayable === "" || parseFloat(amountPayable) === 0.00) {
      alert("You cannot proceed with product upload, recharge balance, or set a lower rate.");
      return false; // Prevent form submission
    }

    // Allow form submission if the value is valid
    return true;
  }
  
   function validateForm5() {
    var amountPayable = document.querySelector("amountpayable5").value;

    // Check if the value is 0.00 or blank
    if (amountPayable === "" || parseFloat(amountPayable) === 0.00) {
      alert("You cannot proceed with product upload, recharge balance, or set a lower rate.");
      return false; // Prevent form submission
    }

    // Allow form submission if the value is valid
    return true;
  }
</script>

<script>
  function validateForm2() {
    var amountPayable = document.querySelector("amountpayable2").value;

    // Check if the value is 0.00 or blank
    if (amountPayable === "" || parseFloat(amountPayable) === 0.00) {
      alert("You cannot proceed with weblink upload, recharge balance, or set a lower rate.");
      return false; // Prevent form submission
    }

    // Allow form submission if the value is valid
    return true;
  }
</script>
<script>
  function validateForm3() {
    var amountPayable = document.querySelector("amountpayable3").value;

    // Check if the value is 0.00 or blank
    if (amountPayable === "" || parseFloat(amountPayable) === 0.00) {
      alert("You cannot proceed with video upload, recharge balance, or set a lower rate.");
      return false; // Prevent form submission
    }

    // Allow form submission if the value is valid
    return true;
  }
</script>



<script>
  function validateForm4() {
    var amountPayable = document.querySelector("amountpayable4").value;
console.log(amountPayable);
    // Check if the value is 0.00 or blank
    if (amountPayable === "" || parseFloat(amountPayable) === 0.00) {
      alert("You cannot proceed with contact requests, recharge balance, or set a lower rate.");
      return false; // Prevent form submission
    }

    // Allow form submission if the value is valid
    return true;
  }
</script>
<script>
    $(document).ready(function(){
        // Event listener for Naira select change
        $('#nairarate').change(function(){
            var selectedNairaValue = $(this).val();
            $('#splitnaira').val(selectedNairaValue / 2);
        });

        // Event listener for Dollar select change
        $('#dollarrate').change(function(){
            var selectedDollarValue = $(this).val();
            $('#splitdollar').val(selectedDollarValue / 2);
        });
    });
</script>


<!--<label>Youtube, Tiktok, Facebook or Video Embedded Url</label>-->
<label>Youtube Embedded Url</label> 
<textarea cols="10" rows="5" name="embededurl" class="op" required></textarea>

<label>Description of Video Content</label>
<textarea cols="10" rows="5" name="descrvideo" class="op" id="vidcontent2"></textarea>

<label>Duration of Video in minutes </label>
  <input type="number"  name="videoduration" class="op" required>


<label>Category</label>
<select name="vidcategory">
  <option value="Religion">Religion</option>
  <option value="Sports">Sports</option>
  <option value="Politics">Politics</option>
  <option value="Adventure">Adventure</option>
  <option value="Breaking News">Breaking News</option>
  <option value="Entertainment">Entertainment</option>
  <option value="Movies"> Movies </option>
  <option value="Fashion">Fashion</option>
  <option value="Health">Health</option>
  <option value="Self Help"> Self Help </option>
  <!-- Add more options below -->
  <option value="Technology">Technology</option>
  <option value="Science">Science</option>
  <option value="Education">Education</option>
  <option value="Music">Music</option>
  <option value="Food">Food</option>
  <option value="Travel">Travel</option>
  <option value="Business">Business</option>
  <!-- Add 35 more options below -->
  <option value="Art">Art</option>
  <option value="Comedy">Comedy</option>
  <option value="History">History</option>
  <option value="Nature">Nature</option>
  <option value="Pets">Pets</option>
  <option value="Fitness">Fitness</option>
  <option value="Gaming">Gaming</option>
  <option value="Agriculture">Agriculture</option>
  <option value="Relationship">Relationship</option>
  <option value="Entreprenuership">Entreprenuership</option>

  <!-- Add more options as needed -->
</select>


</option></select>
  <input type="submit" value="submit" class="submittbutton ff">
</form>

<style>
  #uploadform {
    display: flex;
    flex-direction: column;
  }

  #uploadform label {
 }

  .submittbutton {
    margin-top: 10px;
  }
  
  #uploadform select, #uploadform textarea, #uploadform input {
      margin-bottom: 10px;
  }
</style>
<style>

label {
  font-weight: bold;
  color: #a7a6a6;
}
.opk {
    background-color: #f0f8ff; /* aliceblue */
    border: 2px solid #000000; /* gray */
    border-radius: 10px;
    padding: 10px 20px; /* Add padding for better appearance */
    display: block; /* Make it a block element */
    text-align: center; /* Center the text within the button */
    cursor: pointer; /* Add a pointer cursor for better UX */
    transition: background-color 0.3s, transform 0.3s; /* Add transition effects */
}

.opmm {
  margin-top: 5px;
  margin-bottom: 5px;
    border-radius: 3px;
width: 70%;
height: 30px;
color:black;
    display: block; /* Make it a block element */
    cursor: pointer; /* Add a pointer cursor for better UX */
}

  
</style>
</div>



<div class="viewsclassy" style="display:none;">
                        <?php if ($userType === 'curator') : ?>

                <h3> Video Analytics </h3>

    

    <?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the session or set it to an empty string
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// SQL to select data from the videouploads table, excluding videos from the current session email and watched videos
$sql = "SELECT * FROM videouploads 
        WHERE email = '$email' 
        AND id NOT IN (
            SELECT video_id 
            FROM watched_videos 
            WHERE user_email = '$email'
        )
        ";

$result = $conn->query($sql);

echo '<table>'; 
echo '<thead>';
echo '<tr>';
echo '<th class="colorclassy"> Uploaded Videos </th>';
echo '<th>Per-view Rate</th>';
echo '<th>Duration</th>';
echo '<th>Category</th>';
echo '<th>Views</th>';
echo '<th>Views Needed</th>';
echo '<th>Campaign Budget</th>';
echo '<th>Amount Spent</th>';
echo '<th>Balance Left</th>';

echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Include the video title in the data-video-url attribute 

        echo '<tr>';
        
         echo '<td style="pointer-events: none;">' . $row['videotitle'] . '</td>';
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . ($row['perViewRate']) . '</td>';
echo '<td style="pointer-events: none;">' . $row['videoduration'] . ' minutes</td>';
echo '<td style="pointer-events: none;">' . $row['vidcategory'] . '</td>';
echo '<td style="pointer-events: none;">' . $row['views'] . '</td>';
echo '<td style="pointer-events: none;">' . $row['viewsneeded'] . '</td>';

// Calculate the campaign amount
$campaignAmount = $row['viewsneeded'] * $row['perViewRate'];

// Calculate the amount spent
$amountSpent = $row['views'] * $row['perViewRate'];

// Calculate the balance left
$balanceLeft = $campaignAmount - $amountSpent;


// Output the calculated values in new columns
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $campaignAmount . '</td>';
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $amountSpent . '</td>';
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $balanceLeft . '</td>';
        echo '</tr>'; 
    }
} else {
    echo '<tr><td colspan="9">You are yet to uploaded videos, please do that soon!</td></tr>';
}

echo '</tbody>';
echo '</table>';

$conn->close(); 
?>



<h3> Web Analytics </h3>


    <?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the session or set it to an empty string
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// SQL to select data from the videouploads table, excluding videos from the current session email and watched videos
$sql = "SELECT * FROM webuploads 
        WHERE email = '$email' 
        AND id NOT IN (
            SELECT web_id 
            FROM viewed_web 
            WHERE user_email = '$email'
        )
        ";

$result = $conn->query($sql);

echo '<table>'; 
echo '<thead>';
echo '<tr>';
echo '<th class="colorclassy"> Uploaded Website </th>';
echo '<th>Per-view Rate</th>';
echo '<th>Category</th>';
echo '<th>Views</th>';
echo '<th>Views Needed</th>';
echo '<th>Campaign Budget</th>';
echo '<th>Amount Spent</th>';
echo '<th>Balance Left</th>';

echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Include the video title in the data-video-url attribute 

        echo '<tr>';
    
         echo '<td style="pointer-events: none;">' . $row['websitename'] . '</td>';
 // Output the existing table data
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . ($row['perViewRate']) . '</td>';
echo '<td style="pointer-events: none;">' . $row['webcategory'] . '</td>';
echo '<td style="pointer-events: none;">' . $row['views'] . '</td>';
echo '<td style="pointer-events: none;">' . $row['viewsneeded'] . '</td>';

// Calculate the campaign amount
$campaignAmount = $row['viewsneeded'] * $row['perViewRate'];

// Calculate the amount spent
$amountSpent = $row['views'] * $row['perViewRate'];

// Calculate the balance left
$balanceLeft = $campaignAmount - $amountSpent;

// Output the calculated values in new columns
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $campaignAmount . '</td>';
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $amountSpent . '</td>';
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $balanceLeft . '</td>';

        echo '</tr>'; 
    }
} else {
    echo '<tr><td colspan="8">You have not uploaded a website link yet, please do that soon!</td></tr>';
}

echo '</tbody>';
echo '</table>';

$conn->close(); 
?>



<h3> Marketplace Analytics </h3>
    <?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the session or set it to an empty string
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// SQL to select data from the videouploads table, excluding videos from the current session email and watched videos
$sql = "SELECT * FROM productuploads 
        WHERE email = '$email' 
        AND id NOT IN (
            SELECT product_id 
            FROM viewed_products 
            WHERE user_email = '$email'
        )
        ";

$result = $conn->query($sql);

echo '<table>'; 
echo '<thead>';
echo '<tr>';
echo '<th class="colorclassy"> Listed Products </th>';
echo '<th>Per-view Rate</th>';
echo '<th>Category</th>';
echo '<th>Views</th>';
echo '<th>Views Needed</th>';
echo '<th>Campaign Budget</th>';
echo '<th>Amount Spent</th>';
echo '<th>Balance Left</th>';

echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Include the video title in the data-video-url attribute 

        echo '<tr>';
    
         echo '<td style="pointer-events: none;">' . $row['productname'] . '</td>';
 // Output the existing table data
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . ($row['perViewRate']) . '</td>';
echo '<td style="pointer-events: none;">' . $row['productcategory'] . '</td>';
echo '<td style="pointer-events: none;">' . $row['views'] . '</td>';
echo '<td style="pointer-events: none;">' . $row['viewsneeded'] . '</td>';

// Calculate the campaign amount
$campaignAmount = $row['viewsneeded'] * $row['perViewRate'];

// Calculate the amount spent
$amountSpent = $row['views'] * $row['perViewRate'];

// Calculate the balance left
$balanceLeft = $campaignAmount - $amountSpent;

// Output the calculated values in new columns
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $campaignAmount . '</td>';
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $amountSpent . '</td>';
echo '<td style="pointer-events: none;">' . ($row['currency'] == 'NGN' ? '₦' : '$') . $balanceLeft . '</td>';

        echo '</tr>'; 
    }
} else {
    echo '<tr><td colspan="7">You have not listed an item yet, please do that soon!</td></tr>';
}

echo '</tbody>';
echo '</table>';

$conn->close(); 
?>

            <?php endif; ?>

</div>



<div class="onboardaff" style="display:none;">
    
    
    <h2> Affiliate Commission</h2>
    
    
    <?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch email from session
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Initialize variables
$affiliate_afref = "";
$curator_deposit_total = 0;

// Prepare and execute SQL query to get affiliate's afref
$sql_affiliate = "SELECT afref FROM growsignup WHERE email = ?";
$stmt_affiliate = $conn->prepare($sql_affiliate);
$stmt_affiliate->bind_param("s", $email);
$stmt_affiliate->execute();
$result_affiliate = $stmt_affiliate->get_result();

if ($result_affiliate->num_rows > 0) {
    $row_affiliate = $result_affiliate->fetch_assoc();
    $affiliate_afref = $row_affiliate['afref'];

    // Prepare and execute SQL query to get list of curators with matching afref
    $sql_curators = "SELECT * FROM growsignup WHERE userType = 'curator' AND afref = ?";
    $stmt_curators = $conn->prepare($sql_curators);
    $stmt_curators->bind_param("s", $affiliate_afref);
    $stmt_curators->execute();
    $result_curators = $stmt_curators->get_result();

    if ($result_curators->num_rows > 0) {
        // Start HTML table for deposits
        echo "<table>
                <thead>
                    <tr>
                        <th class='colorclassy'>Fullname</th>
                        <th>Commission Earned (Naira)</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";

        // Loop through each curator
        while ($row_curator = $result_curators->fetch_assoc()) {
            $curator_email = htmlspecialchars($row_curator['email']);

            // Fetch deposits for the current curator from nairatransactions table
            $sql_naira_deposits = "SELECT id, amount, timestamp FROM nairatransactions WHERE depositoremail = ? AND commissionpaid = 'no'";
            $stmt_naira_deposits = $conn->prepare($sql_naira_deposits);
            $stmt_naira_deposits->bind_param("s", $curator_email);
            $stmt_naira_deposits->execute();
            $result_naira_deposits = $stmt_naira_deposits->get_result();

            // Display each deposit in a table row for Naira deposits
            while ($row_deposit = $result_naira_deposits->fetch_assoc()) {
                $commission_earned = $row_deposit['amount'] * 0.2; // 20% commission rate

                // Output table row for Naira deposits
                echo "<tr>
                        <td>" . htmlspecialchars($row_curator['firstName']) . " " . htmlspecialchars($row_curator['lastName']) . "</td>
                        <td hidden>" . $curator_email . "</td>
                        <td hidden>" . $row_deposit['amount'] . "</td>
                        <td>" . $commission_earned . "</td>
                        <td>" . htmlspecialchars($row_deposit['timestamp']) . "</td>
                        <td><button onclick='receivenairaCommission(this, \"$curator_email\", \"$email\", $commission_earned, ".$row_deposit['id'].")'>Receive</button></td>
                    </tr>";
            }
        }

        // Close the tbody and table tags
        echo "</tbody></table>";
    } else {
        echo "No curators found matching the affiliate's afref.";
    }
} else {
    echo "Affiliate not found.";
}

// Close database connections
$stmt_affiliate->close();
$stmt_curators->close();
$conn->close();
?>
<script>
function receivenairaCommission(button, curatorEmail, affiliateEmail, commissionAmount, depositId) {
    // Disable the button to prevent multiple clicks and provide visual feedback
    $(button).prop('disabled', true).text('Processing...');

    // Prepare data to send in the AJAX request
    var data = {
        curator_email: curatorEmail,
        affiliate_email: affiliateEmail,
        commission_amount: commissionAmount,
        deposit_id: depositId  // Pass depositId parameter
    };

    // Make the AJAX request
    $.ajax({
        type: "POST",
        url: "receive_naira_commission.php", // Change the URL to handle Naira transactions
        data: data,
        success: function(response) {
            // Handle the response from the server
            if (response.success) {
                // Display a success message or update the UI accordingly
                alert("Commission credited to wallet successfully!");
                // Reload the page after successful commission receipt
                location.reload();
            } else {
                // Display an error message or handle the error condition
                alert("Failed to receive commission. Please try again later.");
                // Re-enable the button in case of failure
                $(button).prop('disabled', false).text('Receive Commission');
            }
        },
        error: function(xhr, status, error) {
            // Handle AJAX errors
            console.error("AJAX request failed:", error);
            alert("Failed to process commission. Please try again later.");
            // Re-enable the button in case of error
            $(button).prop('disabled', false).text('Receive Commission');
        }
    });
}
</script>

<br> <br>
<?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch email from session
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Initialize variables
$affiliate_afref = "";
$curator_deposit_total = 0;

// Prepare and execute SQL query to get affiliate's afref
$sql_affiliate = "SELECT afref FROM growsignup WHERE email = ?";
$stmt_affiliate = $conn->prepare($sql_affiliate);
$stmt_affiliate->bind_param("s", $email);
$stmt_affiliate->execute();
$result_affiliate = $stmt_affiliate->get_result();

if ($result_affiliate->num_rows > 0) {
    $row_affiliate = $result_affiliate->fetch_assoc();
    $affiliate_afref = $row_affiliate['afref'];

    // Prepare and execute SQL query to get list of curators with matching afref
    $sql_curators = "SELECT * FROM growsignup WHERE userType = 'curator' AND afref = ?";
    $stmt_curators = $conn->prepare($sql_curators);
    $stmt_curators->bind_param("s", $affiliate_afref);
    $stmt_curators->execute();
    $result_curators = $stmt_curators->get_result();

    if ($result_curators->num_rows > 0) {
        // Start HTML table for deposits
        echo "<table>
                <thead>
                    <tr>
                        <th class='colorclassy'> Fullname</th>
                        <th>Commission Earned (Dollar)</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";

        // Loop through each curator
        while ($row_curator = $result_curators->fetch_assoc()) {
            $curator_email = htmlspecialchars($row_curator['email']);

            // Fetch deposits for the current curator from dollartransactions table
            $sql_dollar_deposits = "SELECT id, amount, timestamp FROM dollartransactions WHERE depositoremail = ? AND commissionpaid = 'no'";
            $stmt_dollar_deposits = $conn->prepare($sql_dollar_deposits);
            $stmt_dollar_deposits->bind_param("s", $curator_email);
            $stmt_dollar_deposits->execute();
            $result_dollar_deposits = $stmt_dollar_deposits->get_result();

            // Display each deposit in a table row for Dollar deposits
            while ($row_deposit = $result_dollar_deposits->fetch_assoc()) {
                $commission_earned = $row_deposit['amount'] * 0.2; // 20% commission rate

                // Output table row for Dollar deposits
                echo "<tr>
<td>" . htmlspecialchars($row_curator['firstName']) . " " . htmlspecialchars($row_curator['lastName']) . "</td>
                        <td hidden>" . $curator_email . "</td>
                        <td hidden>" . $row_deposit['amount'] . "</td>
                        <td>" . $commission_earned . "</td>
                        <td>" . htmlspecialchars($row_deposit['timestamp']) . "</td>
                        <td><button onclick='receiveCommission(this, \"$curator_email\", \"$email\", $commission_earned, ".$row_deposit['id'].")'>Receive</button></td>
                    </tr>";
            }
        }

        // Close the tbody and table tags
        echo "</tbody></table>";
    } else {
        echo "No curators found matching the affiliate's afref.";
    }
} else {
    echo "Affiliate not found.";
}

// Close database connections
$stmt_affiliate->close();
$stmt_curators->close();
$conn->close();
?>
<script>
function receiveCommission(button, curatorEmail, affiliateEmail, commissionAmount, depositId) {
    // Disable the button to prevent multiple clicks and provide visual feedback
    $(button).prop('disabled', true).text('Processing...');

    // Prepare data to send in the AJAX request
    var data = {
        curator_email: curatorEmail,
        affiliate_email: affiliateEmail,
        commission_amount: commissionAmount,
        deposit_id: depositId  // Pass depositId parameter
    };

    // Make the AJAX request
    $.ajax({
        type: "POST",
        url: "receive_dollar_commission.php",
        data: data,
        success: function(response) {
            // Handle the response from the server
            if (response.success) {
                // Display a success message or update the UI accordingly
                alert("Commission credited to wallet successfully!");
                // Reload the page after successful commission receipt
                location.reload();
            } else {
                // Display an error message or handle the error condition
                alert("Failed to receive commission. Please try again later.");
                // Re-enable the button in case of failure
                $(button).prop('disabled', false).text('Receive Commission');
            }
        },
        error: function(xhr, status, error) {
            // Handle AJAX errors
            console.error("AJAX request failed:", error);
            alert("Failed to process commission. Please try again later.");
            // Re-enable the button in case of error
            $(button).prop('disabled', false).text('Receive Commission');
        }
    });
}
</script>

</div>

<div class="videoclassy">     


            <h3> Video Feed</h3>


    <div class="vidfeed">
        

    </div>

<script>
    $(document).ready(function () {
        var startTime; // Variable to store the start time
        var isVideoWatched = false; // Flag to track if the video has been watched

        // Function to send data using XHR to the first PHP script
        function sendDataFirstScript(videoId, userEmail, posterEmail, videoRate, currency) { 
            var xhr = new XMLHttpRequest();
           var requestData = 'videoId=' + encodeURIComponent(videoId) +
                  '&userEmail=' + encodeURIComponent(userEmail) +
                  '&posterEmail=' + encodeURIComponent(posterEmail) +
                  '&videoRate=' + encodeURIComponent(videoRate) +
                  '&currency=' + encodeURIComponent(currency);

// console.log(requestData);

            xhr.open('POST', 'payforvideos.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.withCredentials = true;

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                   console.log('Request to the first script successful:', xhr.responseText);
                } else {
             //       console.error('Request to the first script failed:', xhr.statusText);
                }
            };

            xhr.onerror = function () {
                console.error('Network error occurred');
            };

            xhr.send(requestData);
        }

        // Function to send data using XHR to the second PHP script
        function sendDataSecondScript(videoId, userEmail) {
            var xhr = new XMLHttpRequest();
            var requestData = 'videoId=' + encodeURIComponent(videoId) +
                              '&userEmail=' + encodeURIComponent(userEmail);

            xhr.open('POST', 'update_watched_video.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.withCredentials = true;

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                 //   console.log('Request to the second script successful:', xhr.responseText);
                } else {
                 //   console.error('Request to the second script failed:', xhr.statusText);
                }
            };

            xhr.onerror = function () {
                console.error('Network error occurred');
            };

            xhr.send(requestData);
        }

        // Function to embed the video and log the time
        function embedVideo(videoUrl, videoTitle, views, description, videoId, userEmail, posterEmail, videoRate, currency) {
             startTime = performance.now();

   if (videoUrl.includes('youtube')) {
    var width = '560'; // Initial width
    var height = '315'; // Initial height

    // Create the iframe element
    var iframe = $('<iframe>', {
        width: width,
        height: height,
        src: videoUrl,
        frameborder: 0,
        allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
        allowfullscreen: true
    });

    // Find all elements with class 'vidfeed' and clear their content
var vidfeedElements = document.getElementsByClassName('vidfeed');
for (var i = 0; i < vidfeedElements.length; i++) {
    vidfeedElements[i].innerHTML = '';
}

// Append the iframe to the desired container
$('.vidfeed').append(iframe);

    // Add a class to the iframe for easier styling
    iframe.addClass('video-iframe');
} else if (videoUrl.includes('tiktok')) {
    function extractTikTokVideoId(videoUrl) {
        var parts = videoUrl.split('/');
        var videoID = parts[parts.length - 1];
        return videoID;
    }

    // Extract video ID for TikTok video
    var videoID = extractTikTokVideoId(videoUrl);

    // Create blockquote element for TikTok video embedding
    var blockquote = document.createElement('blockquote');
    blockquote.className = 'tiktok-embed';
    blockquote.setAttribute('cite', videoUrl);
    blockquote.setAttribute('data-video-id', videoID);
    blockquote.style.cssText = 'max-width: 560px; min-width: 400px; min-height:560px;';

    var section = document.createElement('section');

    var a = document.createElement('a');
    a.setAttribute('target', '_blank');
    a.setAttribute('title', 'TikTok User');
    a.setAttribute('href', videoUrl);
    a.textContent = 'TikTok User';
    section.appendChild(a);

    blockquote.appendChild(section);

    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.tiktok.com/embed.js';

    var vidfeedElements = document.getElementsByClassName('vidfeed');
    for (var i = 0; i < vidfeedElements.length; i++) {
        vidfeedElements[i].innerHTML = '';
        vidfeedElements[i].appendChild(blockquote);
        vidfeedElements[i].appendChild(script);
    }
} else if (videoUrl.includes('instagram')) {
    // Create the blockquote element
    var blockquote = document.createElement('blockquote');
    blockquote.className = 'instagram-media';
    blockquote.setAttribute('data-instgrm-permalink', videoUrl);
    blockquote.setAttribute('data-instgrm-version', '14');
    blockquote.style.cssText = 'background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);';

    // Create the inner div
    var innerDiv = document.createElement('div');
    innerDiv.style.padding = '8px';

    // Create the image container
    var imageDiv = document.createElement('div');
    imageDiv.style.cssText = 'background:#F8F8F8; line-height:0; margin-top:40px; padding:50% 0; text-align:center; width:100%;';

    // Create the image element
    var image = document.createElement('div');
    image.style.background = 'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAMUExURczMzPf399fX1+bm5mzY9AMAAADiSURBVDjLvZXbEsMgCES5/P8/t9FuRVCRmU73JWlzosgSIIZURCjo/ad+EQJJB4Hv8BFt+IDpQoCx1wjOSBFhh2XssxEIYn3ulI/6MNReE07UIWJEv8UEOWDS88LY97kqyTliJKKtuYBbruAyVh5wOHiXmpi5we58Ek028czwyuQdLKPG1Bkb4NnM+VeAnfHqn1k4+GPT6uGQcvu2h2OVuIf/gWUFyy8OWEpdyZSa3aVCqpVoVvzZZ2VTnn2wU8qzVjDDetO90GSy9mVLqtgYSy231MxrY6I2gGqjrTY0L8fxCxfCBbhWrsYYAAAAAElFTkSuQmCC)';
    image.style.display = 'block';
    image.style.height = '44px';
    image.style.margin = '0 auto -44px';
    image.style.position = 'relative';
    image.style.top = '-22px';
    image.style.width = '44px';

    // Append the image to the image container
    imageDiv.appendChild(image);

    // Append the image container to the inner div
    innerDiv.appendChild(imageDiv);

    // Create the paragraph element
    var paragraph = document.createElement('p');
    paragraph.style.margin = '8px 0 0 0';
    paragraph.style.padding = '0 4px';

    // Create the anchor element
    var anchor = document.createElement('a');
    anchor.href = videoUrl;
    anchor.style.cssText = 'color:#000; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none; word-wrap:break-word;';
    anchor.target = '_blank';
    anchor.textContent = 'A post shared by Instagram (@instagram)';

    // Append the anchor to the paragraph
    paragraph.appendChild(anchor);

    // Append the inner div and paragraph to the blockquote
    blockquote.appendChild(innerDiv);
    blockquote.appendChild(paragraph);

    // Find all elements with class 'vidfeed' and append the blockquote
    
    
    
   var vidfeedElements = document.getElementsByClassName('vidfeed');
for (var i = 0; i < vidfeedElements.length; i++) {
    // Clear the existing content of the vidfeed element
    vidfeedElements[i].innerHTML = '';
    
    // Append the blockquote element to the vidfeed element
    vidfeedElements[i].appendChild(blockquote);
}


    // Create the script element for the Instagram embed script
    var script = document.createElement('script');
    script.async = true;
    script.src = '//www.instagram.com/embed.js';

    // Append the script to the document body
    document.body.appendChild(script);
    
} else if (videoUrl.includes('facebook')) {
    // Create the iframe element for Facebook video embedding 
      var width = '560'; // Initial width 
    var height = '315'; // Initial height
    
    var iframe = document.createElement('iframe');
    iframe.setAttribute('src', videoUrl);
   iframe.setAttribute('width', 'auto');
    iframe.setAttribute('height', '560');
    iframe.setAttribute('style', 'border:none;overflow:hidden');
    iframe.setAttribute('scrolling', 'no');
    
// Find all elements with class 'vidfeed' and clear their content
var vidfeedElements = document.getElementsByClassName('vidfeed');
for (var i = 0; i < vidfeedElements.length; i++) {
    vidfeedElements[i].innerHTML = '';
}

// Append the iframe to the desired container
$('.vidfeed').append(iframe);

}




            $('.vidtitle').text(videoTitle);
            $('.viewcount').text(views);
            $('.viddescr').text(description);
           // $('.vidfeed').html(iframe);

            isVideoWatched = false;

            // Update the elapsed time every second
            var intervalId = setInterval(function () {
                if (startTime && !isVideoWatched) {
                    var currentTime = performance.now();
                    var elapsedSeconds = (currentTime - startTime) / 1000;

              //      console.log('Time since video embedded: ' + elapsedSeconds.toFixed(2) + ' seconds');

                    if (elapsedSeconds >= 36) {
                       // alert('Video watched for 20 seconds!');
                        isVideoWatched = true; 
                        clearInterval(intervalId);

                        // Call the function to send data using XHR to the first script after video is watched
                        sendDataFirstScript(videoId, userEmail, posterEmail, videoRate, currency);

                        // Call the function to send data using XHR to the second script after video is watched
                        sendDataSecondScript(videoId, userEmail);
                    }
                }
            }, 1000);
        }

       // Attach click event to the table cell
$('.videoclass').one('click', function () {
    var videoUrl = $(this).data('video-url');
    var videoTitle = $(this).data('title');
    var views = $(this).data('views');
    var description = $(this).data('description');
    var videoId = $(this).data('video-id');
    var userEmail = "<?php echo $email; ?>";
    var posterEmail = $(this).data('email');
    var videoRate = $(this).data('rate');
    var currency = $(this).data('currency');

    $('.iflo').hide();

    // Call the function to embed the video
    embedVideo(videoUrl, videoTitle, views, description, videoId, userEmail, posterEmail, videoRate, currency);
           // console.log('Encoded Currency:', encodeURIComponent(currency));
        });
    });
</script>



<?php if ($userType === 'affiliate') : ?>


    <div class="empor">
    
    <iframe width="560" height="315" src="https://www.youtube.com/embed/2S5fZFPCoXE?si=JTm2H3DFrVs07Lb9" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="iflo"></iframe>
    <h2 class="vidtitle">How to earn affiliate commission on Grow Social Platform</h2>
    <div class="sjsj">
        <span class="views sjdjnc" style="font-weight: bold;"> Total Views: <span class="viewcount">Unlimited</span> views </span>
        <br>
        
        <span style="font-weight: bold;"> Description: </span>
        
<span class="viddescr">To earn affiliate commission on Grow Social, you can follow these steps:

1. Sign Up and Create an Account: Begin by signing up on the Grow Social website to create your account. If you haven't already, become an affiliate by joining the Grow Social affiliate program.

2. Get Your Affiliate Link: After signing up, you'll receive a unique affiliate link. This link will be used to track referrals and commissions earned from your promotional efforts.

3. Promote Grow Social: Start promoting Grow Social using your affiliate link. You can share it on your website, blog, social media channels, or through other marketing channels.

4. Refer Users: Encourage people to sign up for Grow Social using your affiliate link. When someone clicks on your link and signs up for an account, you'll earn a commission on their purchases or activities on the platform.

5. Track Your Referrals: Keep track of your referrals and commissions earned through the Grow Social affiliate dashboard. You'll be able to monitor your performance and earnings in real-time.

6. Earn Commission: As your referrals make purchases or engage with Grow Social, you'll earn a commission on their activities. The more referrals you bring in, the more commission you can earn.

7. Withdraw Your Earnings: Once you've accumulated enough commission, you can withdraw your earnings through the payment method provided by Grow Social. You may be able to receive your earnings via bank transfer, PayPal, or other payment options.

8. Stay Informed: Stay updated on the latest promotions, offers, and updates from Grow Social. By staying informed, you can optimize your promotional efforts and maximize your earnings as an affiliate.

9. Review Terms and Conditions: Always review the terms and conditions of the Grow Social affiliate program to ensure compliance with the program's policies and guidelines.

By following these steps, you can start earning affiliate commission on Grow Social. Happy promoting!</span></div>
    </div> 
    
        <?php endif; ?>
        
            <?php if ($userType === 'follower') : ?>


    <div class="empor">
    
    <iframe width="560" height="315" src="https://www.youtube.com/embed/BUbkid871HM?si=1_fyiJNIPW9Z-3jf" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="iflo"></iframe>
    <h2 class="vidtitle">How to earn watching videos on Grow Social Platform</h2>
    <div class="sjsj">
        <span class="views sjdjnc" style="font-weight: bold;"> Total Views: <span class="viewcount">Unlimited</span> views </span>
        <br>
        
        <span style="font-weight: bold;"> Description: </span>
        
 <span class="viddescr">To earn by watching videos on Grow Social, follow these steps: 1. Sign Up and Create an Account: Begin by signing up on the Grow Social website to create your account. Once logged in, navigate to the section where you can earn rewards by watching videos. 2. Explore Available Opportunities: Once you're logged in, explore the various videos available for viewing. These might include promotional content, advertisements, or sponsored videos. 3. Watch Videos: Start watching the available videos. Make sure to watch them in full, as some platforms require you to watch the entire video to earn rewards. 4. Earn Rewards: As you watch videos, you'll start earning rewards. These rewards could be in the form of points, virtual currency, or other incentives offered by Grow Social. 5. Redeem Rewards: Once you've accumulated enough rewards, you can redeem them for various benefits offered by the platform. These could include discounts on products or services, gift cards, or other rewards. 6. Stay Engaged and Informed: Keep an eye out for new videos and opportunities to earn rewards. Stay engaged with the platform to maximize your earning potential. 7. Review Terms and Conditions: Always review the terms and conditions associated with earning rewards through watching videos on Grow Social. Make sure you understand the platform's policies and any potential risks involved. By following these steps, you can earn rewards by watching videos on Grow Social. Happy earning! </span>
</div>
    </div> 
    
        <?php endif; ?>

          <?php if ($userType === 'curator') : ?>

<iframe width="560" height="315" src="https://www.youtube.com/embed/EmIbfWCSIuo?si=GxxvLheM1YJ3CaPn" title="YouTube video player" frameborder="0" all
ow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>

    <h2 class="vidtitle">How to upload videos on Grow Social Platform</h2>
    <div class="sjsj">
        <span class="views sjdjnc" style="font-weight: bold;"> Total Views: <span class="viewcount">Unlimited</span> views </span>
        <br>
        
        <span style="font-weight: bold;"> Description: </span>
        <span class="viddescr"> First, sign up on the Grow Social website to create your account. Once logged in, explore the various plans offered and choose the one that best fits your needs and budget. Complete your account setup by providing essential details about your business and social media accounts.

    Next, pinpoint the specific services you're interested in—whether it's boosting video views, increasing follower count, enhancing conversions, generating leads, handling social tasks, or gaining valuable contacts. Tailor the settings to align with your preferences, specifying demographics, interests, or locations for targeted campaigns.

If your chosen plan involves a cost, proceed with the payment to activate your account. Once set up, leverage the analytics tools provided by Grow Social to monitor and assess the performance of your campaigns. And, in case you encounter any challenges or have inquiries, don't hesitate to reach out to their customer support, known for its impressive assistance. Always remember to review the terms and conditions for a clear understanding of the platform's policies and potential risks. Happy growing! </span>
    </div> 
        <?php endif; ?>

   
</div>

</div>

<style>
.adroll {
    display: flex;
    gap: 10px;
}


.sjsj {
    margin-right: 20px;
        text-align: justify;

}
</style>






<div class="section3">












  <div class="categoryclassy">  
             <?php if ($userType === 'follower') : ?>

<div class="tasker">
    <h3> Video Category </h3>
<form id="searchForm">
    <input type="text" id="searchInput" placeholder="Search video">
   <!-- <input type="submit">-->
</form>

<style> 

.tasker {
    display: flex; 
    flex-direction: row;
    gap: 50px;
}
 
#searchInput {
     
    width:300px !important;
    height: 6px !important;
    margin-top: 18px;
}  

#arrowb {
    background-color: grey;
    border-radius: 5px;
    color: white;
    border: none;
} 

.arrowb:hover {
        scale: 1.05;

}

</style>
</div>
<div class="adinfo">
<img src="">

</div>
   <div class="buttonlist">
    <button id="leftArrow" class="arrowb"> <img src="2849832_arrows_navigation_arrow_left_back_icon.png" class="jskdj"></button>
    
    <!-- Container for dynamically generated buttons --> 
    <span id="buttonContainer"></span>

    <button id="rightArrow" class="arrowb"><img src="2849833_arrows_navigation_forward_arrow_right_icon.png" class="jskdj"></button>
</div>  
 
<script>
// Get the select element
const selectElement = document.querySelector('select[name="productcategory"]');

// Initialize an empty array to store the button labels
const buttonLabels = ["All"];

// Iterate over the options and extract their values
selectElement.querySelectorAll('option').forEach(option => {
    buttonLabels.push(option.value);
});

// Add additional buttons to the array if needed
//buttonLabels.push("Button 1", "Button 2", "Button 3");

// Output the generated array 
//console.log(buttonLabels);

    
    // Define an array of button labels
  //  const buttonLabels = ["All", "Sports", "Politics", "Adventure", "Breaking News", "Entertainment", "Fashion", "Health", "Button 1", "Button 2", "Button 3"];

    // Get the button container
    const buttonContainer = document.getElementById('buttonContainer');

    // Set the initial index for the visible buttons
    let visibleIndex = 0;
    const numVisibleButtons = 7; // Number of main buttons visible initially

    // Function to update the visibility of buttons
    function updateButtonVisibility() {
        buttonContainer.innerHTML = ''; // Clear previous buttons

        for (let i = visibleIndex; i < visibleIndex + numVisibleButtons && i < buttonLabels.length; i++) {
            const button = document.createElement('button');
            button.textContent = buttonLabels[i];
            buttonContainer.appendChild(button);
            button.addEventListener('click', function() {
                changeCategory(button.textContent);
            });
        }
    }

    // Function to handle left arrow click
    document.getElementById('leftArrow').addEventListener('click', function() {
        if (visibleIndex > 0) {
            visibleIndex--;
            updateButtonVisibility();
        }
    });

    // Function to handle right arrow click
    document.getElementById('rightArrow').addEventListener('click', function() {
        if (visibleIndex + numVisibleButtons < buttonLabels.length) {
            visibleIndex++;
            updateButtonVisibility();
        }
    });

    // Initial setup for button visibility
    updateButtonVisibility();

    // Function to handle button clicks
    function changeCategory(category) {
        // Define the URL with the category as a query parameter
        var url = 'dashboard.php?category=' + encodeURIComponent(category);

        // Redirect the browser to the URL
        window.location.href = url;
    }
</script>

<script>
    // Function to handle form submission
    function handleSubmit(event) {
        event.preventDefault(); // Prevent the default form submission
        
        // Get the search query from the input field
        var searchQuery = document.getElementById("searchInput").value.trim();
        
        // Perform the search if the search query is not empty
        if (searchQuery !== "") {
            // Define the URL with the search query as a query parameter
            var url = 'dashboard.php?search=' + encodeURIComponent(searchQuery);
    
            // Redirect the browser to the URL
            window.location.href = url;
        }
    }
    
    // Add event listener to the form for submission
    document.getElementById("searchForm").addEventListener("submit", handleSubmit);
</script>

<style> 
#buttonContainer button {
    margin-right: 2px;
    background-color: grey;
    border-radius: 5px;
    color: white;
    margin-bottom: 5px;
    border: none;
}

.productname, .websitename {
    
    font-weight:bold;
        margin-top: 10px;

    margin-bottom: 10px;
}



.qtynumbers, .prodcurrency, .productdescr {
     margin-top: 5px;
overflow:none;
margin-bottom: 5px;
}

.productdescr {
    /* Allow long words to break and wrap onto the next line */
    word-wrap: break-word; /* or overflow-wrap: break-word; */
}

.productfeed, .webfeed {
    
width: 550px;
}

.productqty {
    display: flex; 
    flex-direction: row;
    gap: 20px;
}

.product-image {
    width: 600px;
    background-color:pink;
    height: 400px;
}

.arrayed-image {
    width: 300px; 
    height: 100px;
}


    .buttonlist {
        width: 120%;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    } 

    th {
      background-color: #f2f2f2;
    }
  </style>

<?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the session or set it to an empty string
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Get the category parameter from the request
$category = isset($_GET['category']) ? $_GET['category'] : '';

$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

if (preg_match('/<\?php|<script|<\?xml|<\?html|function\s*\(|console\.[a-zA-Z]+\s*\(|document\.[a-zA-Z]+\s*\(|<\w+\s*>|<\/\w+\s*>/', $email . $category . $searchQuery)) {
    die("Potential code injection detected.");
}

// SQL to select data from the videouploads table based on the category
$sql = "SELECT * FROM videouploads 
        WHERE email != '$email' 
        AND id NOT IN (
            SELECT video_id 
            FROM watched_videos 
            WHERE user_email = '$email'
        )
        AND viewsneeded != views";

// If a search query is provided, add it to the SQL query
if (!empty($searchQuery)) {
    // Assuming you want to search in video title and description fields
   $sql .= " AND (
    videotitle LIKE '%$searchQuery%' OR 
    descrvideo LIKE '%$searchQuery%' OR 
    email LIKE '%$searchQuery%' OR 
    currency LIKE '%$searchQuery%' OR 
    vidcategory LIKE '%$searchQuery%' OR 
    embededurl LIKE '%$searchQuery%'
)";

}
// If a category is specified and it's not "All", add it to the SQL query
if (!empty($category) && $category !== 'All') {
    $sql .= " AND vidcategory = '$category'";
}

$sql .= " ORDER BY RAND()
          LIMIT 10";


$result = $conn->query($sql);

echo '<table>'; 
echo '<thead>';
echo '<tr>';
echo '<th class="colorclassy"> Suggested Videos </th>';
echo '<th>Per-view Rate</th>';
echo '<th>Duration</th>';
echo '<th>Category</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Include the video title in the data-video-url attribute

        echo '<tr>';
        echo '<td data-video-url="' . $row['embededurl'] . '" data-currency="' . $row['currency'] . '" data-email="' . $row['email'] . '" data-rate="' . $row['perViewRate'] . '" data-title="' . $row['videotitle'] . '" data-views="' . $row['views'] . '" data-uploaddate="' . $row['uploaddate'] . '" data-description="' . htmlspecialchars($row['descrvideo'], ENT_QUOTES, 'UTF-8') . '" data-video-id="' . $row['id'] . '" class="videoclass">' . $row['videotitle'] . '</td>';

        echo '<td>' . ($row['currency'] == 'NGN' ? '₦' : '$') . ($row['perViewRate'] / 2) . '</td>';
        echo '<td>' . $row['videoduration'] . ' minutes</td>';
        echo '<td>' . $row['vidcategory'] . '</td>';
        echo '</tr>';
    }
} else {
     if (!empty($searchQuery)) {
        echo '<tr><td colspan="4">No videos found for your search query! Try another search.</td></tr>';
    } else if (!empty($category) && $category !== 'All') {
        echo '<tr><td colspan="4">No videos found for the ' . $category . ' category. Please come back soon!</td></tr>';
    } else {
        echo '<tr><td colspan="4">No videos yet, please come back soon!</td></tr>';
    }
}

echo '</tbody>';
echo '</table>';

$conn->close();
?>

</div>
        <?php endif; ?>




<div class="productheader" style="display:none;">
    
    
    <h3> Products </h3>
<?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the session or set it to an empty string
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// SQL to select data from the videouploads table, excluding videos from the current session email and watched videos
$sql = "SELECT * FROM productuploads 
        WHERE email != '$email' 
        AND id NOT IN (
            SELECT product_id 
            FROM viewed_products 
            WHERE user_email = '$email'
        )
        AND viewsneeded != views
        ORDER BY RAND()
        LIMIT 10";

$result = $conn->query($sql);

echo '<table>'; 
echo '<thead>';
echo '<tr>';
echo '<th class="colorclassy"> Product of the day </th>';
echo '<th>Per-view Rate</th>';
echo '<th>Product Price</th>';
echo '<th>Qty Available</th>';
echo '<th>Category</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Include the video title in the data-video-url attribute

        echo '<tr>';
        echo '<td data-photographs="' . htmlspecialchars(json_encode($row['photographs']), ENT_QUOTES, 'UTF-8') . '" data-qty="' . $row['qtynumber'] . '" data-price="' . $row['productprice'] . '" 
      data-currency="' . $row['currency'] .'" 
      data-email="' . $row['email'] . '" 
      data-rate="' . $row['perViewRate'] . '" 
      data-title="' . $row['productname'] . '" 
      data-views="' . $row['views'] . '" 
      data-uploaddate="' . $row['uploaddate'] . '" 
      data-description="' . htmlspecialchars($row['descrproduct'], ENT_QUOTES, 'UTF-8') . '" 
      data-product-id="' . $row['id'] . '"  data-phonenumber = "'. $row['phonenumber']. '" data-fullname = "'. $row['fullname'] . '"
      class="productclass">' . $row['productname'] . '</td>';

        echo '<td>' . ($row['currency'] == 'NGN' ? '₦' : '$') . ($row['perViewRate'] / 2) . '</td>';
        echo '<td>' . ($row['currency'] == 'NGN' ? '₦' : '$') . ($row['productprice']) . '</td>';
        echo '<td>' . $row['qtynumber'] . '</td>';
                echo '<td>' . $row['phonenumber'] . '</td>';
                echo '<td>' . $row['fullname'] . '</td>';

        echo '<td>' . $row['productcategory'] . '</td>';
            

        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7">No products yet, please come back soon!</td></tr>';
}

echo '</tbody>';
echo '</table>';

$conn->close();
?>
</div>


<div class="followheader" style="display:none;">
    
    
    <h3> Follow Tasks </h3>
<?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the session or set it to an empty string
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// SQL to select data from the videouploads table, excluding videos from the current session email and watched videos
$sql = "SELECT * FROM followerrequest 
        WHERE email != '$email' 
        AND viewsneeded != views
        ORDER BY RAND()
        LIMIT 10";

$result = $conn->query($sql);

echo '<table>'; 
echo '<thead>';
echo '<tr>';
echo '<th class="colorclassy"> Follower Request </th>';
echo '<th>Per-follow Rate</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Include the video title in the data-video-url attribute

       echo '<tr>';
echo '<td data-currency="' . $row['currency'] . '" data-email="' . $row['email'] . '" data-rate="' . $row['perViewRate'] . '" data-followname="' . $row['followname'] . '" data-views="' . $row['views'] . '" data-uploaddate="' . $row['uploaddate'] . '" data-follow-id="' . $row['id'] . '" class="followclass">' . $row['followname'] . '</td>';

$currencySymbol = $row['currency'] == 'NGN' ? '₦' : '$';
$halfRate = $row['perViewRate'] / 2;

echo '<td>' . $currencySymbol . $halfRate . '</td>';
echo '</tr>';

    }
} else {
    echo '<tr><td colspan="7">No request to follow yet, please come back soon!</td></tr>';
}

echo '</tbody>';
echo '</table>';

$conn->close();
?>
</div>

<div class="webheader" style="display:none;">
    
    
    <h3> Web Tasks </h3>
<?php
// Database connection parameters
$servername = "localhost";
$username = "growsoci_admin";
$password = "uiHEEmAELay9";
$database = "growsoci_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the session or set it to an empty string
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// SQL to select data from the videouploads table, excluding videos from the current session email and watched videos
$sql = "SELECT * FROM webuploads 
        WHERE email != '$email' 
        AND id NOT IN (
            SELECT web_id 
            FROM viewed_web 
            WHERE user_email = '$email'
        )
        AND viewsneeded != views
        ORDER BY RAND()
        LIMIT 10";

$result = $conn->query($sql);

echo '<table>'; 
echo '<thead>';
echo '<tr>';
echo '<th class="colorclassy"> Website lookup </th>';
echo '<th>Per-view Rate</th>';
echo '<th>Category</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Include the video title in the data-video-url attribute

       echo '<tr>';
echo '<td data-currency="' . $row['currency'] . '" data-websiteurl="' . $row['websiteurl'] . '" data-email="' . $row['email'] . '" data-rate="' . $row['perViewRate'] . '" data-websitename="' . $row['websitename'] . '" data-views="' . $row['views'] . '" data-uploaddate="' . $row['uploaddate'] . '" data-description="' . htmlspecialchars($row['descrweb'], ENT_QUOTES, 'UTF-8') . '" data-web-id="' . $row['id'] . '" class="webclass">' . $row['websitename'] . '</td>';

$currencySymbol = $row['currency'] == 'NGN' ? '₦' : '$';
$halfRate = $row['perViewRate'] / 2;

echo '<td>' . $currencySymbol . $halfRate . '</td>';
echo '<td>' . $row['webcategory'] . '</td>';
echo '</tr>';

    }
} else {
    echo '<tr><td colspan="7">No products yet, please come back soon!</td></tr>';
}

echo '</tbody>';
echo '</table>';

$conn->close();
?>
</div>
</div>








</div>





<script>
        var startTime; // Variable to store the start time
        var isWebViewed = false; // Flag to track if the product has been viewed

        // Function to send data using XHR to the first PHP script
        function sendDataSixthScript(webId, userEmail, posterEmail, webRate, currency) { 
            var xhr = new XMLHttpRequest();
            var requestData = 'webId=' + encodeURIComponent(webId) +
                  '&userEmail=' + encodeURIComponent(userEmail) +
                  '&posterEmail=' + encodeURIComponent(posterEmail) +
                  '&webRate=' + encodeURIComponent(webRate) +
                  '&currency=' + encodeURIComponent(currency);
		console.log(requestData);

            xhr.open('POST', 'payforweb.php', true); 
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.withCredentials = true;

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                   console.log('Request to the sixth script successful:', xhr.responseText);
                } else {
                    console.error('Request to the sixth script failed:', xhr.statusText);
                }
            };

            xhr.onerror = function () {
                console.error('Network error occurred');
            };

            xhr.send(requestData);
        }

        // Function to send data using XHR to the second PHP script
        function sendDataSeventhScript(webId, userEmail) {
            var xhr = new XMLHttpRequest();
            var requestData = 'webId=' + encodeURIComponent(webId) +
                              '&userEmail=' + encodeURIComponent(userEmail);
		console.log(requestData);
	 xhr.open('POST', 'update_viewed_web.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.withCredentials = true;

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    console.log('Request to the seventh script successful:', xhr.responseText);
                } else {
                    console.error('Request to the seventh script failed:', xhr.statusText);
                }
            };

            xhr.onerror = function () {
                console.error('Network error occurred');
            };

            xhr.send(requestData);
        }
    $(document).ready(function () {

        // Function to embed the product and log the time
// Function to embed the product and log the time
function embedWeb(websitename, views, description, websiteurl, webId, userEmail, posterEmail, webRate, currency) {
    startTime = performance.now();

var webDiv = $('<div>', { 
    class: 'web-container'
});

$('.webfeeder').append(webDiv);
    
    var webHeader = $('<h3>', {
    class: 'webplaces',
    text: 'Web Uploads'
});

webHeader.appendTo('.webfeeder');

$('<div>', { class: 'websitename', text: websitename}).appendTo('.webfeeder');
alert("web feed appended successfully");

// Append quantity information to the '.webfeed' container
$('<div>', { 
    class: 'qtynumbers', 
    html: 'Url: <a href="' + websiteurl + '">' + websiteurl + '</a>' 
}).appendTo('.webfeed').find('a').addClass('sendDataSixthScript').data({
    webId: webId,
    userEmail: userEmail,
    posterEmail: posterEmail,
    webRate: webRate,
    currency: currency
});

// Bind click event to elements with class 'sendDataSixthScript'
$('.sendDataSixthScript').one('click', function (event) {
    event.preventDefault(); // Prevent default link behavior
    var data = $(this).data(); // Retrieve stored data
    sendDataSixthScript(data.webId, data.userEmail, data.posterEmail, data.webRate, data.currency);
});


// Append quantity information to the '.productfeed' container
$('<div>', { class: 'qtynumbers', html: "<b>Web Description: </b>" + description }).appendTo('.webfeed');

// Determine currency symbol based on the currency
var currencySymbol;
if (currency === "NGN") {
    currencySymbol = "₦"; // Naira symbol
} else if (currency === "USD") {
    currencySymbol = "$"; // Dollar symbol
} else {
    currencySymbol = currency; // Use currency code if not NGN or USD
}

    isWebViewed = false;
 
    var intervalId = setInterval(function () {
        if (startTime && !isWebViewed) { 
            var currentTime = performance.now();
            var elapsedSeconds = (currentTime - startTime) / 1000; 

            if (elapsedSeconds >= 5) { // If 100 seconds have passed
                isWebViewed = true; // Set product viewed flag to true
                clearInterval(intervalId); // Stop the timer

                // Call functions to send data using XHR to the third and fourth scripts after product is viewed
               // sendDataSixthScript(webId, userEmail, posterEmail, webRate, currency);
                sendDataSeventhScript(webId, userEmail);
            }
        }
    }, 1000); // Update every second
}



 // Attach click event to the anchor element with the class 'web-link'
        $('.web-link').on('click', function (e) {
            e.preventDefault(); // Prevent the default behavior of the anchor element
            
            // Retrieve data attributes from the anchor element
            var webId = $(this).data('web-id');
            var userEmail = "<?php echo $email; ?>"; // PHP variable for user email
            var posterEmail = $(this).data('email');
            var webRate = $(this).data('rate');
            var currency = $(this).data('currency');
            
            // Call the sendDataSixthScript function
            sendDataSixthScript(webId, userEmail, posterEmail, webRate, currency);
        });
        
// Attach click event to the table cell with class 'productclass'
$('.webclass').one('click', function () {
    // Retrieve data attributes from the clicked element
        $('.webfeeder').empty();
    $('.webfeed').empty();

    var views = $(this).data('views');
    var websitename = $(this).data('websitename');
    var description = $(this).data('description');
        var websiteurl = $(this).data('websiteurl');
    var webId = $(this).data('web-id');
    var userEmail = "<?php echo $email; ?>"; // PHP variable for user email
    var posterEmail = $(this).data('email');
    var webRate = $(this).data('rate');
    var currency = $(this).data('currency');
 
    $('.emporlm').hide(); // Hide elements with class 'iflo'

    // Call the function to embed the product
    embedWeb(websitename, views, description, websiteurl, webId, userEmail, posterEmail, webRate, currency) ;
    
});

    });
</script>
<script>
        var startTime; // Variable to store the start time
        var isViewed = false; // Flag to track if the product has been viewed

        // Function to send data using XHR to the first PHP script
        function sendDataEighthScript(followId, userEmail, posterEmail, followRate, currency) { 
            var xhr = new XMLHttpRequest();
            var requestData = 'followId=' + encodeURIComponent(followId) +
                  '&userEmail=' + encodeURIComponent(userEmail) +
                  '&posterEmail=' + encodeURIComponent(posterEmail) +
                  '&followRate=' + encodeURIComponent(followRate) +
                  '&currency=' + encodeURIComponent(currency);
		console.log(requestData);

            xhr.open('POST', 'payforfollow.php', true); 
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.withCredentials = true;

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                   console.log('Request to the eight script successful:', xhr.responseText);
                } else {
                    console.error('Request to the eight script failed:', xhr.statusText);
                }
            };

            xhr.onerror = function () {
                console.error('Network error occurred');
            };

            xhr.send(requestData);
        }

        // Function to send data using XHR to the second PHP script
        function sendDataNinthScript(followId, userEmail, posterEmail) {
            var xhr = new XMLHttpRequest();
             var requestData = 'followId=' + encodeURIComponent(followId) +
                      '&userEmail=' + encodeURIComponent(userEmail) +
                      '&posterEmail=' + encodeURIComponent(posterEmail);
    
    console.log(requestData);
	 xhr.open('POST', 'update_viewed_follow.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.withCredentials = true;

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    console.log('Request to the ninth script successful:', xhr.responseText);
                } else {
                    console.error('Request to the ninth script failed:', xhr.statusText);
                }
            };

            xhr.onerror = function () {
                console.error('Network error occurred');
            };

            xhr.send(requestData);
        }
    $(document).ready(function () {

        // Function to embed the product and log the time
// Function to embed the product and log the time
function embedFollow(followname, views, description, followId, userEmail, posterEmail, followRate, currency) {
    startTime = performance.now();

var followDiv = $('<div>', { 
    class: 'follow-container'
});

$('.followfeeder').append(followDiv);
    
    var followHeader = $('<h3>', {
    class: 'followplaces',
    text: 'Follow Uploads'
});

followHeader.appendTo('.followfeeder');

$('<div>', { class: 'followname', text: followname}).appendTo('.followfeeder');
alert("follow feed appended successfully");

$('<div>', { 
    class: 'qtynumbers', 
    html: '<button onclick="' + followId + '">Follow</button>' 
}).appendTo('.followfeed').find('button').addClass('sendDataEighthScript').data({
    followId: followId,
    userEmail: userEmail,
    posterEmail: posterEmail,
    followRate: followRate, 
    currency: currency
});

// Bind click event to elements with class 'sendDataSixthScript'
$('.sendDataEighthScript').one('click', function (event) {
    event.preventDefault(); // Prevent default link behavior
    var data = $(this).data(); // Retrieve stored data
    sendDataEighthScript(data.followId, data.userEmail, data.posterEmail, data.followRate, data.currency);
});


// Append quantity information to the '.productfeed' container
//$('<div>', { class: 'qtynumbers', html: "<b>Follow Description: </b>" + description }).appendTo('.followfeed');

// Determine currency symbol based on the currency
var currencySymbol;
if (currency === "NGN") {
    currencySymbol = "₦"; // Naira symbol
} else if (currency === "USD") {
    currencySymbol = "$"; // Dollar symbol
} else {
    currencySymbol = currency; // Use currency code if not NGN or USD
}

    isViewed = false;
 
    var intervalId = setInterval(function () {
        if (startTime && !isViewed) { 
            var currentTime = performance.now();
            var elapsedSeconds = (currentTime - startTime) / 1000; 

            if (elapsedSeconds >= 5) { // If 100 seconds have passed
                isViewed = true; // Set product viewed flag to true
                clearInterval(intervalId); // Stop the timer

                // Call functions to send data using XHR to the third and fourth scripts after product is viewed
               // sendDataSixthScript(followId, userEmail, posterEmail, followRate, currency);
                sendDataNinthScript(followId, userEmail);
            }
        }
    }, 1000); // Update every second
}



 // Attach click event to the anchor element with the class 'follow-link'
        $('.follow-link').on('click', function (e) {
            e.preventDefault(); // Prevent the default behavior of the anchor element
            
            // Retrieve data attributes from the anchor element
            var followId = $(this).data('follow-id');
            var userEmail = "<?php echo $email; ?>"; // PHP variable for user email
            var posterEmail = $(this).data('email');
            var followRate = $(this).data('rate');
            var currency = $(this).data('currency');
            
            // Call the sendDataSixthScript function
            sendDataEighthScript(followId, userEmail, posterEmail, followRate, currency);
        });
        
// Attach click event to the table cell with class 'followclass'
$('.followclass').one('click', function () {
    // Retrieve data attributes from the clicked element
        $('.followfeeder').empty();
    $('.followfeed').empty();

    var views = $(this).data('views');
    var followname = $(this).data('followname');
    var description = $(this).data('description');
    var followId = $(this).data('follow-id');
    var userEmail = "<?php echo $email; ?>"; // PHP variable for user email
    var posterEmail = $(this).data('email');
    var followRate = $(this).data('rate');
    var currency = $(this).data('currency');
 
    $('.emporlm').hide(); // Hide elements with class 'iflo'

    // Call the function to embed the product
    embedFollow(followname, views, description, followId, userEmail, posterEmail, followRate, currency) ;
    
});

    });
</script>




<script>
var modalInterval;
var remainingDisplays = 6; // Number of times to display the modal after the first decline
var delayTimes = [300000, 3600000, 2592000000, 15552000000, 31536000000]; // Delay times in milliseconds: 5 minutes, 1 hour, 30 days, 6 months, 1 year

// Function to create or display the modal dynamically
function createOrDisplayModal() {
    // Create modal elements
    var modal = document.createElement('div');
    modal.className = 'modal';

    var modalContent = document.createElement('div');
    modalContent.className = 'modal-content';

    var message = document.createElement('p');
    message.innerHTML = '<b> Would you love to have your contacts shared? </b> <br><br> If you agree to share your contacts, you will automatically enroll in the Contact Gain Program with our partner companies and earn rewards. Please note that participation in this program is subject to our <a href="#" target="your_terms_and_conditions_link">terms and conditions</a>.';

    var yesBtn = document.createElement('button');
    yesBtn.textContent = 'Agree';
    yesBtn.classList.add('yesbutton');

    var noBtn = document.createElement('button');
    noBtn.textContent = 'Decline';
    noBtn.classList.add('nobutton');

    // Append elements to modal content
    modalContent.appendChild(message);
    modalContent.appendChild(yesBtn);
    modalContent.appendChild(noBtn);

    // Append modal content to modal
    modal.appendChild(modalContent);

    // Append modal to the document body
    document.body.appendChild(modal);

    // Show the modal
    modal.style.display = 'block';

    // Add event listeners
    yesBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        updateContactsShared('Yes'); // Pass 'Yes' as the status
    });

    noBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        updateContactsShared('No'); // Pass 'No' as the status
        remainingDisplays--; // Decrement the remaining displays
//        console.log("Remaining displays left:", remainingDisplays); // Log the remaining displays
        if (remainingDisplays > 0) {
            // Start displaying the modal again after a delay
            var delay = delayTimes.shift() || 30000; // Default to 30 seconds if delayTimes array is empty
           // console.log("Starting countdown for next display. Delay:", delay + "ms"); // Log the delay time
            modalInterval = setTimeout(createOrDisplayModal, delay);
        }
    });

}

window.onload = function() {
    // Send an AJAX request to check if the user has already agreed to share contacts
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'check_contacts_shared.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = xhr.responseText;
       //     console.log("Response from check_contacts_shared.php:", response);

            // If the user has already agreed to share contacts, don't show the prompt
            if (response === 'Yes') {
          //      console.log("User has already agreed to share contacts. Skipping the prompt.");
            } else {
                // Dynamically create or display the modal
                createOrDisplayModal();
            }
        }
    };
    xhr.send();

    // Update the last prompt time 
    var currentTime = new Date().getTime(); 
    localStorage.setItem('lastPromptTime', currentTime.toString());
}


// Function to update contacts shared status
function updateContactsShared(status) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_contacts_shared.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var updateResponse = xhr.responseText;
         //   console.log("Response from update_contacts_shared.php:", updateResponse);
            // Handle the response from the server if needed
        }
    };
    xhr.send('contacts_shared=' + status);
}
// Clear the modalInterval when the page is unloaded
window.onbeforeunload = function() {
    clearTimeout(modalInterval);
}
</script>

</div>

<!-- Bidvertiser2092704 -->

 <!-- JavaScript code to create CKEditor instances -->
    <script>
        ClassicEditor
            .create(document.querySelector('#vidcontent1'))
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#vidcontent2'))
            .catch(error => {
                console.error(error);
            });
        // Create more instances for additional textarea elements if needed
    </script>
            </body>
            </html>