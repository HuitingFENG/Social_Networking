<?php 

session_start(); 

try{ // ##### database connection #####
    $mysqlClient = new PDO(
        'mysql:host=localhost;dbname=social_networking;charset=utf8',
        'root',
        'root'
    );
}

catch(Exception $e){ // ##### error - database connection #####
    die('Error : '.$e->getMessage());
}

// ##### Get the whole user table #####
$sqlQuery = 'SELECT * FROM user';
$usersStatement = $mysqlClient->prepare($sqlQuery);
$usersStatement->execute();
$users = $usersStatement->fetchAll();


// ##### Get the whole relationships table #####
$sqlQuery = 'SELECT * FROM relationships';
$relationshipsStatement = $mysqlClient->prepare($sqlQuery);
$relationshipsStatement->execute();
$relationships = $relationshipsStatement->fetchAll();


// ##### Get the whole public_post table #####
$sqlQuery = 'SELECT * FROM public_post ORDER BY `date` DESC';
$publicPostsStatement = $mysqlClient->prepare($sqlQuery);
$publicPostsStatement->execute();
$publicPosts = $publicPostsStatement->fetchAll();


include('functions.php');


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Title of the web page</title>
        <link rel="stylesheet" href="style.css" type="text/css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <!-- Responsive design -->
        <meta name="viewport" content="width=device-width">
        
    </head>
    <body>
        
        <header>  <!-- ##### HEADER ##### -->
            <?php 
            include('header.php'); 
            if(isset($_SESSION['LOGGED_USER_fname'])){
                echo $_SESSION['LOGGED_USER_fname'];
            }
            ?>
        </header> <!-- ##### end - HEADER ##### -->
        
        <div id="page">
            <?php
            if(isset($_POST['login'])){
                if($_POST['login'] == "Signup"){
                    include('signup.php');
                }
                else if ($_POST['login'] == "Disconnect"){
                    session_destroy();
                    header("Refresh:0"); // Refresh the page to actualise the disconnection
                }
                else if ($_POST['login'] == "Signin"){
                    include('login.php');
                }
            }
            ?>     

            <form action="index.php" method="post">
                <?php if(!isset($_SESSION['LOGGED_USER_fname'])) : ?>
                    <?php if (isset($_POST['login'])) : ?>
                        <?php if ($_POST['login'] != "Signup") : ?>
                            <button type="submit" name="login" value="Signup">Sign up</button> <!-- Create an account -->
                        <?php elseif ($_POST['login'] != "Signin") : ?>
                            <button type="submit" name="login" value="Signin">Sign in</button> <!-- Login -->
                        <?php endif ?>
                    <?php else : ?>
                        <button type="submit" name="login" value="Signup">Sign up</button> <!-- Create an account -->
                        <button type="submit" name="login" value="Signin">Sign in</button> <!-- Login -->
                    <?php endif ?>
                <?php else : ?>
                    <button type="submit" name="login" value="Disconnect">Disconnect</button> <!-- Disconnect -->
                <?php endif ?>
            </form>

            <?php 
            if(isset($_POST['menu'])){
                if($_POST['menu'] == "Members"){
                    if(isset($_SESSION['LOGGED_USER_fname'])){
                        include('members.php');
                    }
                    else{
                        echo 'To access the site, you must log in';
                    }
                }
                else if($_POST['menu'] == "Messaging"){
                    if(isset($_SESSION['LOGGED_USER_fname'])){
                        include('messaging.php');
                    }
                    else{
                        echo 'To access the site, you must log in';
                    }
                }
                else if($_POST['menu'] == "Myprofile"){
                    if(isset($_SESSION['LOGGED_USER_fname'])){
                        include('profile.php');
                    }
                    else{
                        echo 'To access the site, you must log in';
                    }
                }
                else{
                    if(isset($_SESSION['LOGGED_USER_fname'])){
                        include('home.php');
                    }
                    else{
                        echo 'To access the site, you must log in';
                    }
                }
            }
            ?>

        </div>

        <footer>  <!-- ##### FOOTER ##### -->
            <?php include('footer.php'); ?>
        </footer> <!-- ##### end - FOOTER ##### -->

    </body>
</html>