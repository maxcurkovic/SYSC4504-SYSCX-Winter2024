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
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <title>SYSCX - Main</title>
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
                  <a class="selectedPage" href="index.php">Home</a>
                  <a href="profile.php">Profile</a>
                  <a href="logout.php">Log out</a>
                  <?php if ($_SESSION["account_type"] == 0): // Show option if account is admin?>
                     <a href="user_list.php">User List</a>
                  <?php endif; ?>
            </nav>
         </td>
         <td class="main">
            <div>
               <section>
                  <h2>New Post</h2>
                  <form name="syscxpost" id="syscxpost" action = "" method="post">
                     <fieldset>
                        <textarea name="new_post" form="syscxpost" maxlength="280" placeholder="What is happening?! (max 280 char)"></textarea>
                        <br>
                     </fieldset>
                     <input class="button" type="submit" name="submit" value="Post">
                     <input class="button" type="reset" value="Reset">
                  </form>
               </section>
               <section class="postSection">
                   <?php include("connection.php");
                   // This script selects all posts from all users and displays the 10 most recent ones. I changed the SQL query to match the A3 description

$conn = new mysqli($server_name, $username, $password, $database_name);

                           if($conn->connect_error){
                              die("Error: Couldn't connect. " . $conn -> connect_error);
                           }
                            $student_id = $_SESSION["student_id"];
                               $sql = "SELECT * FROM users_posts ORDER BY post_date DESC LIMIT 10";
                               $statement = $conn->prepare($sql);
                               $statement->execute();
                               $result = $statement->get_result();
                   
                               $row_num = 1;
                               if ($result->num_rows > 0) {
                                  while ($row = $result->fetch_assoc()) {
                                     echo "<details><summary>Post {$row_num}</summary><p>{$row['new_post']}</p></details>";
                                     $row_num++;
                                  }
                               } else {
                                     echo "No posts found.";
                                  }        
                               header("index.php");   
                                  
                     ?>
               </section>
            </div>
            <div></div>
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
    <?php
        include("connection.php");

        $conn = new mysqli($server_name, $username, $password, $database_name);

        if($conn->connect_error){
            die("Error: Couldn't connect. " . $conn -> connect_error);
        }

        
        if(isset($_POST["submit"])){

         $student_id = $_SESSION["student_id"];
         $new_post = $_POST['new_post'];
            
            $sql = "INSERT INTO users_posts (student_id, new_post) VALUES (?, ?)";
            $statement = $conn->prepare($sql);
            $statement->bind_param("is", $student_id, $new_post);

            if ($statement->execute()) {
                echo "<p>Connected Successfully</p>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        
        $conn -> close();
      }
    ?>
    
</div>
</body>

</html>