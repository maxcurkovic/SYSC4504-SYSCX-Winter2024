<?php
   include("connection.php");

   $conn = new mysqli($server_name, $username, $password, $database_name);
   session_start();

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
           $avatar_index = $row_avatar['avatar'];
           $userInfo['avatar'] = "images/img_avatar$avatar_index.png";
       }
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <title>SYSCX - Register Profile</title>
   <link rel="stylesheet" href="assets/css/reset.css">
   <link rel="stylesheet" href="assets/css/style.css">
   <link rel = "icon" href ="images/xicon.png" type = "image/x-icon">
   <script>
      // For password validation in register, checking if they're equal
      function validate() {
          var password = document.getElementById("student_password").value;
          var confirmPassword = document.getElementById("student_confirm_password").value;
          if (password != confirmPassword) {
              alert("Passwords do not match, please try again!");
              return false;
          }
          else {
            return true;
          }       
      }
   </script>
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
               <?php if (isset($_SESSION["student_id"])): // Login check for navbar?>
                  <a class="selectedPage" href="index.php">Home</a>
                  <a href="profile.php">Profile</a>
                  <a href="logout.php">Log out</a>
                  <a href="user_list.php">User List</a>
               <?php else: ?>
                  <a href="login.php">Login</a>
                  <a class="selectedPage" href="register.php">Register</a>
               <?php endif; ?>
            </nav>
         </td>
         <td class="main">
            <div>
               <section>
                  <h2>Update Profile Information</h2>
                  <form action="profile.php" method="post" onsubmit="return validate()">
                  <fieldset>   
                     <legend>
                        <span>
                        Personal Information
                        </span>
                     </legend>
                        <label for="first_name">First Name: </label>
                        <input type="text" id="first_name" name="first_name" placeholder="ex. John Snow">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name">
                        <label for="DOB">Date of Birth: </label>
                        <input type="date" id="DOB" name="DOB">
                  </fieldset>
                  <fieldset>
                     <legend>
                        <span>
                        Profile Information
                        </span>
                     </legend>
                     <br>
                        <label for="student_email">Email Address:</label>
                        <input type="email" id="student_email" name="student_email"><br>
                        <label for="program">Program:</label>
                        <select name="program" id="program" >
                           <option value="Choose Program">Choose Program</option>
                           <option value="Software Engineering">Software Engineering</option>
                           <option value="Computer Systems Engineering">Computer Systems Engineering</option>
                           <option value="Biomedical & Electrical Engineering">Biomedical & Electrical Engineering</option>
                           <option value="Electrical Engineering">Electrical Engineering</option>
                           <option value="Communications Engineering">Communications Engineering</option>
                           <option value="Special">Special</option>
                        </select><br>
                        <label for="student_password">Password:</label>
                        <input type="password" id="student_password" name="student_password"><br>
                        <label for="student_confirm_password">Confirm Password:</label>
                        <input type="password" id="student_confirm_password" name="student_confirm_password"><br>
                  </fieldset>
                  <br>
                  <input class="button" type="submit" name="register" value="Register">
                  <input class="button" type="reset" value="Reset">
               </form>
               <p>If you already have an account, you can login <a href="login.php">here</a>.</p>
               <br>
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

   <div>

</div>
      
</body>
      
</html>