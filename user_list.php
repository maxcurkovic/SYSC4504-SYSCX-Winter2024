<?php

   include("connection.php");

   $conn = new mysqli($server_name, $username, $password, $database_name);
   
   session_start();

   if (!isset($_SESSION["student_id"])) {
      header("Location: login.php");
      exit();
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
        $avatar_index = $row_avatar['avatar'];
        $userInfo['avatar'] = "images/img_avatar$avatar_index.png";
    }
}}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <title>SYSCX - User List</title>
   <link rel="stylesheet" href="assets/css/reset.css">
   <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
   <header>
      <h1>SYSCX</h1>
      <p>Social media for SYSC students in Carleton University</p>
   </header>
   <main>
      <table>
         <tr>
            <td class="nav">
               <nav>
                  <a href="index.php">Home</a>
                  <a href="profile.php">Profile</a>
                  <a href="logout.php">Log out</a>
                  <?php if ($_SESSION['account_type'] == 0):?>
                  <a href="user_list.php" class="selectedPage">User List</a>
                  <?php endif; ?>
               </nav>
            </td>
            <td class="main">
               <?php if ($_SESSION['account_type'] == 0):?>
               <section>
                  <?php
                  // This is nearly the exact same as Lab 5. Echoes a table based on an SQL query for all users in user_info 

                    $conn = new mysqli($server_name, $username, $password, $database_name);

                    if($conn->connect_error){
                    die("Error: Couldn't connect. " . $conn -> connect_error);
                    }

                    echo "<table>
                            <tr>
                                <th>Student Id</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Student Email</th>
                                <th>Student Program</th>
                                <th>Account Type</th>
                            </tr>";

                    $sql = "SELECT "." student_id, first_name, last_name, student_email FROM users_info";
                    $statement = $conn->prepare($sql);
                    $statement->execute();
                    $result = $statement->get_result();

                    if ($result->num_rows > 0) {
                    
                    while($row = $result->fetch_assoc()) {

                        $student_id = $row['student_id'];
                        $first_name = $row['first_name'];
                        $last_name = $row['last_name'];
                        $student_email = $row['student_email'];

                         // Users_program
                        $sql = "SELECT "."program FROM users_program WHERE student_id = ?";
                        $statement = $conn->prepare($sql);
                        $statement->bind_param("i", $student_id);
                        $statement->execute();
                        $rst = $statement->get_result();

                        if ($rst->num_rows > 0) {
                            $program = $rst->fetch_assoc()['program'];
                        }

                        // Users_permissions
                        $sql = "SELECT "."account_type FROM users_permissions WHERE student_id = ?";
                        $statement = $conn->prepare($sql);
                        $statement->bind_param("i", $student_id);
                        $statement->execute();
                        $rst = $statement->get_result();

                        if ($rst->num_rows > 0) {
                            $account_type = $rst->fetch_assoc()['account_type'];
                        }
                        // Echo the table rows, again, very similar to Lab 5
                        echo "<tr>
                                <td>".$student_id."</td>
                                <td>".$first_name."</td>
                                <td>".$last_name."</td>
                                <td>".$student_email."</td>
                                <td>".$program."</td>
                                <td>".$account_type."</td>
                              </tr>";
                    }
                    } else {
                    echo "No users available"; // Error failsafe
                    }
                    echo "</table>";
                  ?>
                  
               </section>
               <?php endif; ?>
               <?php if ($_SESSION['account_type'] == 1): // If the user trying to access is NOT an admin, do not show them the user list and show the link to return home as per requirements?>
                  <section>
                     <p> Error: Permission denied. </p>
                     <p> Click <a href="index.php">here</a> to return home.</p>
                  </section>
               <?php endif; ?>
               
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
         </tr>
      </table>
   </main>

   <?php $conn -> close(); ?>
   
</body>

</html>