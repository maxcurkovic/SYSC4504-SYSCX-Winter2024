
<?php
   include("connection.php");

   $conn = new mysqli($server_name, $username, $password, $database_name);
      session_start();
      
      if (!isset($_SESSION["student_id"]) && !isset($_POST["register"])) {
         header("Location: login.php");
         exit();
     }

     // I had to move the register functionality up here, as I was having an issue where the sidebar would not update unless you refresh
     if(isset($_POST["register"])){

      $student_email = $_POST['student_email'];
   
      // Check if the email already exists in the database
      $sql_check_email = "SELECT * FROM users_info WHERE student_email = ?";
      $statement_student_email = $conn->prepare($sql_check_email);
      $statement_student_email->bind_param("s", $student_email);
      $statement_student_email->execute();
      $result_check_email = $statement_student_email->get_result();
   
      // Checking if the email is in the database via the query. If no results, then proceed with the error message
      if($result_check_email->num_rows > 0) {
         echo "<script> location.href='register.php'; </script>";
         echo "<script> alert(\"This email already exists within the database! Please enter another email.\")</script>";
      }
      else { // Exactly as A02 with the addition of password and permissions

         // These session variables are to ensure that the fields are ALWAYS populated when you return to profile after logging in.
         $_SESSION['student_email'] = $_POST['student_email'];
         $_SESSION['first_name'] = $_POST['first_name'];
         $_SESSION['last_name'] = $_POST['last_name'];
         $_SESSION['dob'] = $_POST['DOB'];
         $_SESSION['program'] = $_POST['program'];
         $_SESSION['street_number'] = null;
         $_SESSION['street_name'] = null;
         $_SESSION['city'] = null;
         $_SESSION['province'] = null;
         $_SESSION['postal_code'] = null;
         
         // Users_info
         $sql = "INSERT INTO users_info (student_email, first_name, last_name, dob) VALUES (?, ?, ?, ?)";
         $statement = $conn->prepare($sql);
         $student_email = $_SESSION['student_email'];
         $first_name = $_SESSION['first_name'] ;
         $last_name = $_SESSION['last_name'];
         $dob = $_SESSION['dob'];
         $statement->bind_param("ssss", $student_email, $first_name, $last_name, $dob);
   
         if ($statement->execute()) {
            echo "<p>Connected Successfully</p>";
         } else {
            echo "Error: " . $sql . "<br>" . $statement->error;
         }
   
         $student_id = $conn->insert_id;
         $_SESSION["student_id"] = $student_id;
         echo $_SESSION["student_id"];
   
         // Users_program
         $sql = "INSERT INTO users_program (student_id, program) VALUES (?, NULL)";
         $statement = $conn->prepare($sql);
         $statement->bind_param("i", $student_id);
   
         if ($statement->execute()) {
            echo "<p>Connected Successfully</p>";
         } else {
            echo "Error: " . $sql . "<br>" . $statement->error;
         }
   
         // Users_avatar
         $sql = "INSERT INTO users_avatar (student_id, avatar) VALUES (?, 0)";
         $statement = $conn->prepare($sql);
         $statement->bind_param("i", $student_id);
   
         if ($statement->execute()) {
            echo "<p>Connected Successfully</p>";
         } else {
            echo "Error: " . $sql . "<br>" . $statement->error;
         }
   
         // Users_address
         $sql = "INSERT INTO users_address (student_id, street_number, street_name, city, province, postal_code) VALUES (?, 0, NULL, NULL, NULL, NULL)";
         $statement = $conn->prepare($sql);
         $statement->bind_param("i", $student_id);
      
         if ($statement->execute()) {
               echo "<p>Connected Successfully</p>";
         } else {
               echo "Error: " . $sql . "<br>" . $statement->error;
         }
   
         $password = $_POST['student_password'];
         
         // Users_passwords
         $sql = "INSERT INTO users_passwords (student_id, password) VALUES (?, ?)";;
         $statement = $conn->prepare($sql);
         $hashed_password = password_hash($password, PASSWORD_BCRYPT);
         $statement->bind_param("is", $student_id, $hashed_password);
         
         if ($statement->execute()) {
               echo "<p>Connected Successfully</p>";
         } else {
               echo "Error: " . $sql . "<br>" . $statement->error;
         }
   
         // Users_permissions
         $sql = "INSERT INTO users_permissions (student_id, account_type) VALUES (?, 1)";
         $statement = $conn->prepare($sql);
         $statement->bind_param("i", $student_id);
   
         $_SESSION["account_type"] = 1;
         
         if ($statement->execute()) {
               echo "<p>Connected Successfully</p>";
         } else {
               echo "Error: " . $sql . "<br>" . $statement->error;
         }
   
      }
      
   }
   // This functionality is all for the right information bar
   $userInfo = array(
      'first_name' => '',
      'last_name' => '',
      'email' => '',
      'program' => '',
      'avatar' => 'images/img_avatar1.png'
   );

   if (isset($_SESSION["student_id"])) {
      $student_id = $_SESSION["student_id"];
      $sql_user_info = "SELECT * FROM users_info WHERE student_id = ?";
      $statement_user_info = $conn->prepare($sql_user_info);
      $statement_user_info->bind_param("i", $student_id);
      $statement_user_info->execute();
      $result_user_info = $statement_user_info->get_result();

   if ($result_user_info->num_rows > 0) {
       $row_user_info = $result_user_info->fetch_assoc();
       $userInfo['first_name'] = $row_user_info['first_name'];
       $userInfo['last_name'] = $row_user_info['last_name'];
       $userInfo['email'] = $row_user_info['student_email'];
        $sql_program = "SELECT program FROM users_program WHERE student_id = ?";
        $statement_program = $conn->prepare($sql_program);
        $statement_program->bind_param("i", $student_id);
        $statement_program->execute();
        $result_program = $statement_program->get_result();
        if ($result_program->num_rows > 0) {
            $row_program = $result_program->fetch_assoc();
            $userInfo['program'] = $row_program['program'];
        }
       $sql_avatar = "SELECT avatar FROM users_avatar WHERE student_id = ?";
       $statement_avatar = $conn->prepare($sql_avatar);
       $statement_avatar->bind_param("i", $student_id);
       $statement_avatar->execute();
       $result_avatar = $statement_avatar->get_result();
       if ($result_avatar->num_rows > 0) {
           $row_avatar = $result_avatar->fetch_assoc();
           $avatar_index = $row_avatar['avatar'] + 1; // Need + 1 because images are from [1,2,...,5]
           $userInfo['avatar'] = "images/img_avatar$avatar_index.png";
       }
   }
   ?>
   <script>
// Unchanged from A02 to fill fields upon register
document.addEventListener("DOMContentLoaded", function () {

   first_name = document.querySelector("#first_name");
   last_name = document.querySelector("#last_name");
   dob = document.querySelector("#DOB");
   student_email = document.querySelector("#student_email");
   program = document.querySelector("#program");

   first_name.value = '<?php echo $_POST["first_name"]; ?>';
   last_name.value = '<?php echo $_POST["last_name"]; ?>';
   dob.value = '<?php echo $_POST["DOB"]; ?>';
   student_email.value = '<?php echo $_POST["student_email"]; ?>';
   program.value = '<?php echo $_POST["program"]; ?>';
});

</script>

   <?php
   // Moved up here to remain consistent with existing PHP code. This is all the same as A02, just switched to SQL prepare statements
     if(isset($_POST["submit"])){

      $first_name = $_POST['first_name'];
      $last_name = $_POST['last_name'];
      $dob = $_POST['DOB'];
      $student_email = $_POST['student_email'];
      $student_id = $_SESSION['student_id'];
      $program = $_POST['program'];
      $avatar = $_POST['avatar'];
      $street_number = $_POST['street_number'];
      $street_name = $_POST['street_name'];
      $city = $_POST['city'];
      $province = $_POST['province'];
      $postal_code = $_POST['postal_code'];

      // Again, session variables are used when updating the fields to ensure they stay there when returning to profile
      $_SESSION['first_name'] = $first_name;
      $_SESSION['last_name'] = $last_name;
      $_SESSION['dob'] = $dob;
      $_SESSION['student_email'] = $student_email;
      $_SESSION['program'] = $program;
      $_SESSION['street_number'] = $street_number;
      $_SESSION['street_name'] = $street_name;
      $_SESSION['city'] = $city;
      $_SESSION['province'] = $province;
      $_SESSION['postal_code'] = $postal_code;
   
      // Users_info
      $sql = "UPDATE users_info SET first_name "."= ?, last_name = ?, dob = ?, student_email = ? WHERE student_id = ?";
      $statement = $conn->prepare($sql);
      $statement->bind_param("ssssi", $first_name, $last_name, $dob, $student_email, $student_id);
   
       if ($statement->execute()) {
          echo "<p>Connected Successfully</p>";
       } else {
          echo "Error: " . $sql . "<br>" . $statement->error;
       }
   
       // Users_program
       $sql = "UPDATE users_program SET Program "."= ? WHERE student_id = ?";
       $statement = $conn->prepare($sql);
       $statement->bind_param("si", $program, $student_id);
       if ($statement->execute()) {
         echo "<p>Connected Successfully</p>";
      } else {
         echo "Error: " . $sql . "<br>" . $statement->error;
      }
   
      // Users_avatar
      $sql = "UPDATE users_avatar SET avatar "."= ? WHERE student_id = ?";
         
      $statement = $conn->prepare($sql);
      $statement->bind_param("si", $avatar, $student_id);
      if ($statement->execute()) {
         echo "<p>Connected Successfully</p>";
      } else {
         echo "Error: " . $sql . "<br>" . $statement->error;
      }
   
       // Users_address
       $sql = "UPDATE users_address SET street_number "."= ?, street_name = ?, city = ?, province = ?, postal_code = ? WHERE student_id = ?";
      $statement = $conn->prepare($sql);
      $statement->bind_param("issssi", $street_number, $street_name, $city, $province, $postal_code, $student_id);
       if ($statement->execute()) {
         echo "<p>Connected Successfully</p>";
      } else {
         echo "Error: " . $sql . "<br>" . $statement->error;
      }
      

   }
   


}
?>

<script>
// Unchanged from A02 for updating fields
document.addEventListener("DOMContentLoaded", function () {

   first_name = document.querySelector("#first_name");
   last_name = document.querySelector("#last_name");
   dob = document.querySelector("#DOB");
   student_email = document.querySelector("#student_email");
   program = document.querySelector("#program");
   streetnum = document.querySelector("#street_number");
   streetname = document.querySelector("#street_name");
   city = document.querySelector("#city");
   province = document.querySelector("#province");
   postalcode = document.querySelector("#postal_code");
   switch(<?php echo $_POST["avatar"]; ?>) {
      case 0:
         avatar = document.querySelector("#avatar1");
         avatar.checked = true;
         break;
      case 1:
         avatar = document.querySelector("#avatar2");
         avatar.checked = true;
         break;
      case 2:
         avatar = document.querySelector("#avatar3");
         avatar.checked = true;
         break;
      case 3:
         avatar = document.querySelector("#avatar4");
         avatar.checked = true;
         break;
      case 4:
         avatar = document.querySelector("#avatar5");
         avatar.checked = true;
         break;
   }

   first_name.value = '<?php echo $_POST["first_name"]; ?>';
   last_name.value = '<?php echo $_POST["last_name"]; ?>';
   dob.value = '<?php echo $_POST["DOB"]; ?>';
   student_email.value = '<?php echo $_POST["student_email"]; ?>';
   program.value = '<?php echo $_POST["program"]; ?>';
   streetnum.value = '<?php echo $_POST["street_number"]; ?>';
   streetname.value = '<?php echo $_POST["street_name"]; ?>';
   city.value = '<?php echo $_POST["city"]; ?>';
   province.value = '<?php echo $_POST["province"]; ?>';
   postalcode.value = '<?php echo $_POST["postal_code"]; ?>';
});

</script>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <title>SYSCX - Profile Information</title>
   <link rel="stylesheet" href="assets/css/reset.css">
   <link rel="stylesheet" href="assets/css/style.css">
   <link rel = "icon" href ="images/xicon.png" type = "image/x-icon">
</head>

<body>
   <header>
      <h1>SYSCX</h1>
      <p>Social media for SYSC students in Carleton University</p>
   </header>
   <table>
      <tr>
         <td class="nav">
         <nav>
                  <a href="index.php">Home</a>
                  <a class="selectedPage" href="profile.php">Profile</a>
                  <a href="logout.php">Log out</a>
                  <?php if ($_SESSION["account_type"] == 0): // Admins can see this?>
                     <a href="user_list.php">User List</a>
                     <?php endif; ?>
            </nav>
         </td>
         <td class="main">
            <div>
               <section>
                  <h2>Update Profile Information</h2>
                  <form action="" method="post">
                  <fieldset>   
                     <legend>
                        <span>
                        Personal Information
                        </span>
                     </legend>
                        <label for="first_name">First Name: </label>
                        <input type="text" id="first_name" value='<?php if(isset($_SESSION["first_name"])){ echo $_SESSION["first_name"];}?>' name="first_name" placeholder="ex. John Snow">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" value='<?php if(isset($_SESSION["last_name"])){ echo $_SESSION["last_name"];}?>' name="last_name">
                        <label for="DOB">Date of Birth: </label>
                        <input type="date" id="DOB" value='<?php if(isset($_SESSION["dob"])){ echo $_SESSION["dob"];}?>' name="DOB">
                  
                  </fieldset>
                  <fieldset>
                     <legend>
                        <span>
                        Address
                        </span>
                     </legend>
                     <br>
                        <label for="street_number">Street Number:</label>
                        <input type="number" id="street_number" value='<?php if(isset($_SESSION["street_number"])){ echo $_SESSION["street_number"];} ?>' name="street_number">
                        <label for="street_name">Street Name:</label>
                        <input type="text" id="street_name" value='<?php if(isset($_SESSION["street_name"])){ echo $_SESSION["street_name"];} ?>' name="street_name"><br>
                        <br>
                        <label for="city">City:</label>
                        <input type="text" id="city" value='<?php if(isset($_SESSION["city"])){ echo $_SESSION["city"];}?>' name="city">
                        <label for="province">Province:</label>
                        <input type="text" id="province" value='<?php if(isset($_SESSION["province"])){ echo $_SESSION["province"];}?>'name="province">
                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" value='<?php if(isset($_SESSION["postal_code"])){ echo $_SESSION["postal_code"];}?>' name="postal_code">
                      
                  </fieldset>
                  <fieldset>
                     <legend>
                        <span>
                        Profile Information
                        </span>
                     </legend>
                        <label for="student_email">Email Address:</label>
                        <input type="email" id="student_email" value='<?php if(isset($_SESSION["student_email"])){ echo $_SESSION["student_email"];} ?>' name="student_email"><br>
                        <label for="program">Program:</label>
                        <select name="program" id="program" value='<?php if(isset($_SESSION["program"])){ echo $_SESSION["program"];} ?>' >
                           <option value="Choose Program">Choose Program</option>
                           <option value="Software Engineering">Software Engineering</option>
                           <option value="Computer Systems Engineering">Computer Systems Engineering</option>
                           <option value="Biomedical & Electrical Engineering">Biomedical & Electrical Engineering</option>
                           <option value="Electrical Engineering">Electrical Engineering</option>
                           <option value="Communications Engineering">Communications Engineering</option>
                           <option value="Special">Special</option>
                        </select>
                        <br>
                        <label>Choose your Avatar:</label>
                        <br>
                        <input type="radio" id="avatar1" name="avatar" value="0">
                        <label>
                           <img alt="avatar1" src="images/img_avatar1.png">
                        </label>
                        <input type="radio" id="avatar2" name="avatar" value="1">
                        <label>
                           <img alt="avatar2" src="images/img_avatar2.png">
                        </label>
                        <input type="radio" id="avatar3" name="avatar" value="2">
                        <label>
                           <img alt="avatar3" src="images/img_avatar3.png">
                        </label>
                        <input type="radio" id="avatar4" name="avatar" value="3">
                        <label>
                           <img alt="avatar4" src="images/img_avatar4.png">
                        </label>
                        <input type="radio" id="avatar5" name="avatar" value="4">
                        <label>
                           <img alt="avatar5" src="images/img_avatar5.png">
                        </label>
                        <br>
                        
            
                  </fieldset>
                  <input class="button" type="submit" name="submit" value="Submit">
                     <input class="button" type="reset" value="Reset">
               </form>
               </section>
            </div>
         </td>
         <td class="userInfo">
                <?php if(isset($_SESSION["student_id"])): // Using the userInfo array to fill in the fields on the right bar?>
                <p><?php echo $userInfo['first_name'] . " " . $userInfo['last_name']; ?></p>
                <img alt="indexImg" class="indexImg" src="<?php echo $userInfo['avatar']; ?>">
                <p>Email:</p>
                <p><a href="mailto:<?php echo $userInfo['email']; ?>"><?php echo $userInfo['email']; ?></a></p>
                <br>
                <p>Program:</p>
                <p><?php echo $userInfo['program']; ?></p>
                <?php endif; ?>
            </td>
         </td>
      </tr>
   </table>

                  </body>
</html>