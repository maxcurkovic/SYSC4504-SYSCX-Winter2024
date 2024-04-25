<?php
   include("connection.php");

   $conn = new mysqli($server_name, $username, $password, $database_name);
   session_start();

   // This is the redirection if you ARE logged in
   if (isset($_SESSION["student_id"])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <title>SYSCX - Login</title>
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
            <a class="selectedPage" href="login.php">Login</a>
            <a href="register.php">Register</a>
            </nav>
         </td>
         <td class="main">
            <div>
               <section>
                  <h2>Login Account</h2>
                  <form action="login.php" method="post" onsubmit="return validate()">
                  <fieldset>   
                     <legend>
                        <span>
                        Login Information
                        </span>
                     </legend>
                        <label for="student_email">Email Address:</label>
                        <input type="email" id="student_email" name="student_email"><br>
                        <label for="student_password">Password:</label>
                        <input type="password" id="student_password" name="student_password"><br>
                  </fieldset>
                  <br>
                  <input class="button" type="submit" name="login" value="Login">
                  <input class="button" type="reset" value="Reset">
               </form>
               <br>
               </section>
               <!--This will redirect you to register -->
               <p>If you do not have an account, click <a href="register.php">here</a>.</p>
            </div>
         </td>
         <td class="userInfo">
            </td>
         </td>
      </tr>
   </table>
   <?php
        include("connection.php");
        // All login functionality is written here

        $conn = new mysqli($server_name, $username, $password, $database_name);

        if($conn->connect_error){
            die("Error: Couldn't connect. " . $conn -> connect_error);
        }

        if(isset($_POST["login"])){
         $email = $_POST['student_email'];
         $password = $_POST['student_password'];

    // Query to fetch the student ID from the given email when logging in
    $sql_student_id = "SELECT student_id FROM users_info WHERE student_email = ?";
    $statement_student_id = $conn->prepare($sql_student_id);
    $statement_student_id->bind_param("s", $email);
    $statement_student_id->execute();
    $result_student_id = $statement_student_id->get_result();

    // Check if student exists, if so, then continue with check
    if ($result_student_id->num_rows == 1) {
        $row_student_id = $result_student_id->fetch_assoc();
        $student_id = $row_student_id['student_id'];

        // Query for password for session variable purposes
        $sql_password = "SELECT password FROM users_passwords WHERE student_id = ?";
        $statement_password = $conn->prepare($sql_password);
        $statement_password->bind_param("i", $student_id);
        $statement_password->execute();
        $result_password = $statement_password->get_result();

        // Query for account type for session variable purposes
        $sql_permissions = "SELECT account_type FROM users_permissions WHERE student_id = ?";
        $statement_permissions = $conn->prepare($sql_permissions);
        $statement_permissions->bind_param("i", $student_id);
        $statement_permissions->execute();
        $result_permissions = $statement_permissions->get_result();

        // If password and permission was found, continue with check
        if ($result_password->num_rows == 1 && $result_permissions->num_rows == 1) {
            $row_password = $result_password->fetch_assoc();
            $hashed_password = $row_password['password']; // Recall: in profile we hashed this when it was added to the table
            
            // Fetch account type
            $row_permissions = $result_permissions->fetch_assoc();
            $account_type = $row_permissions['account_type'];

            // Using password_verify, check password with the hashed one in the DB
            if (password_verify($password, $hashed_password)) {
                echo $student_id;
                $_SESSION["student_id"] = $student_id;
                $_SESSION["account_type"] = $account_type;
                header("Location: index.php");
                exit();
            } else {
                // Passwords do not match
                echo "<p> Invalid email or password. Click <a href=\"register.php\">here</a> to make an account.</p>";
            }
        } else {
            // No password or account type was found
            echo "<p> No account found. Click <a href=\"register.php\">here</a> to make an account.</p>";
        }
    } else {
        // Student does not exist
        echo "<p> No account found. Click <a href=\"register.php\">here</a> to make an account.</p>";
    }
}

    ?>
    </body>
      
</html>