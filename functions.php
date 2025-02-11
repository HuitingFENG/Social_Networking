<?php

// ##### Sign up validation #####
if (isset($_POST['login_form'])){
    if ($_POST['login_form'] == "Signup"){
        if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['birth_year']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password_2'])) {
            if ($_POST['first_name'] != "" && $_POST['last_name'] != "" && $_POST['birth_year'] != "" && $_POST['email'] != "" && $_POST['password'] != "" && $_POST['password_2']) {
                if ($_POST['password'] === $_POST['password_2']) {

                    $sqlQuery = 'INSERT INTO `user`(`first_name`, `last_name`, `email`, `password`, `birth_year`) VALUES (:first_name, :last_name, :email, :password, :birth_year)';
                    $insertUser = $mysqlClient->prepare($sqlQuery);
                    $insertUser->execute([
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'email' => $_POST['email'],
                        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                        'birth_year' => $_POST['birth_year'],
                    ]);

                    $error = 'The account has been created. Now Sign in !!';
                }
                else {
                    $error = 'Wrong password';
                }
            }
            else {
                $error = 'Please enter all informations';
            }
        }
        else {
            $error = 'Please enter correct informations';
        }
    }

    // ##### Login validation #####
    if ($_POST['login_form'] == "Signin"){
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $errorvalidation = TRUE;
            foreach ($users as $user) {
                if ($user['email'] === $_POST['email'] && password_verify($_POST['password'], $user['password'])) {
                    $_SESSION['LOGGED_USER_id'] = $user['user_id'];
                    $_SESSION['LOGGED_USER_fname'] = $user['first_name'];
                    $_SESSION['LOGGED_USER_lname'] = $user['last_name'];
                    $_SESSION['LOGGED_USER_email'] = $user['email'];
                    $_SESSION['LOGGED_USER_byear'] = $user['birth_year'];
                    $errorvalidation = FALSE;
                }
            }
            if ($errorvalidation){
                $error = 'Incorrect email or password';
            }
        }
        else{
            $error = 'Please enter correct informations';
        }
    }
}


// ##### Put all relationships in the $_SESSION["relationShips"] array #####
if (!isset($_SESSION["relationShips"]) && isset($_SESSION["LOGGED_USER_id"])){
    $_SESSION["relationShips"] = array();
    $counterRelationShips = 0;
    $test = $_SESSION["LOGGED_USER_id"];
    $sqlQuery = 'SELECT * FROM `relationships` WHERE `user_1` = ' . $test . ' AND `active`= 1 OR `user_2` = ' . $test . ' AND `active`= 1';
    $searchRelationship = $mysqlClient->prepare($sqlQuery);
    $searchRelationship->execute();
    $relationships = $searchRelationship->fetchAll();

    foreach ($relationships as $relationship) {
        if ($relationship['user_2'] == $test){
            $_SESSION["relationShips"][$counterRelationShips] = $relationship['user_1'];
            $counterRelationShips++;
        }
        else if ($relationship['user_1'] == $test){
            $_SESSION["relationShips"][$counterRelationShips] = $relationship['user_2'];
            $counterRelationShips++;
        }
    }

    sort($_SESSION["relationShips"]);
}


// ##### Request friend #####
if(isset($_SESSION['LOGGED_USER_id']) && isset($_POST['id_user'])){
    if ($_POST['id_user'] != ""){
        $sqlQuery = 'INSERT INTO `relationships` (`user_1`, `user_2`, `active`) VALUES (:user_1, :user_2, :active)';
        $insertRelationship = $mysqlClient->prepare($sqlQuery);
        $insertRelationship->execute([
            'user_1' => $_SESSION['LOGGED_USER_id'],
            'user_2' => $_POST['id_user'],
            'active' => '1', // for the moment, by default: 1 (active). After 0 by default and 1 when the user_2 accept
        ]);

        $_SESSION["relationShips"][count($_SESSION["relationShips"])] = $_POST['id_user'];
        sort($_SESSION["relationShips"]);
        
        echo "You are now friend with " . $_POST['id_user'];
    }
    else{
        $error = 'Please enter a correct number';
    }
}


// ##### Send message (public) ##### publicSend
if(isset($_SESSION['LOGGED_USER_id']) && isset($_POST['publicSend'])){
    if (isset($_POST['object']) && isset($_POST['content'])){
        if ($_POST['object'] != ""  && $_POST['content'] != ""){
            if(strlen($_POST['content']) < 512 && strlen($_POST['object']) < 256){
                $dateSend = date("YmdHis");
                if($_FILES['screenshot']['error'] == 0){ // Let's test if the file has been sent and if there are no errors
                    // Let's test if the file is not too big
                    if ($_FILES['screenshot']['size'] <= 10000000)
                    {
                        // Let's test if the extension is allowed
                        $fileInfo = pathinfo($_FILES['screenshot']['name']);
                        $extension = $fileInfo['extension'];
                        $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];
                        if (in_array($extension, $allowedExtensions))
                        {
                            // The file can be validated and stored permanently
                            $newName = $_SESSION['LOGGED_USER_id'] . '_' . $_SESSION['LOGGED_USER_fname'] . '_' . $dateSend . '.' . $fileInfo['extension'] /*. basename($_FILES['screenshot']['name'])*/;
                            $path_in_holder = 'uploads/' . $newName;
                            move_uploaded_file($_FILES['screenshot']['tmp_name'], $path_in_holder);
                            echo "<script language = javascript>alert('File uploaded successfully!');</script>";
                            
                            $sqlQuery = 'INSERT INTO `public_post` (`user_id`, `date`, `title`, `content`, `file_name`) VALUES (:user_id, :date, :title, :content, :file_name)';
                            $insertRelationship = $mysqlClient->prepare($sqlQuery);
                            $insertRelationship->execute([
                                'user_id' => $_SESSION['LOGGED_USER_id'],
                                'date' => $dateSend,
                                'title' => $_POST['object'],
                                'content' => $_POST['content'],
                                'file_name' => $newName,
                            ]);
                        }
                        else{
                            $error = 'File not allowed. Please send jpg, jpeg, gif or png';
                        }
                    }
                    else{
                        $error = 'File too big (Please send a file smaller than 10MB)';
                    }
                }
                else{
                    $sqlQuery = 'INSERT INTO `public_post` (`user_id`, `date`, `title`, `content`) VALUES (:user_id, :date, :title, :content)';
                    $insertRelationship = $mysqlClient->prepare($sqlQuery);
                    $insertRelationship->execute([
                        'user_id' => $_SESSION['LOGGED_USER_id'],
                        'date' => $dateSend,
                        'title' => $_POST['object'],
                        'content' => $_POST['content'],
                    ]);
                }
            }
            else{
                $error = 'Object or content too long (object<256 and content<512)';
            }
        }
        else{
            $error = 'You must send an object and a content';
        }
    }
    else{
        $error = 'You must send an object and a content';
    }
}


// ##### Print Error #####
if(isset($error)){
    echo $error;
}

// ##### Send private message #####

if(isset($_POST['send'])){
    $message= htmlspecialchars($_POST['message']);
    $send_date = date('y-m-d h:i:s');
    $insertMessage = $mysqlClient->prepare('INSERT INTO messages(message, id_receiver, id_author, message_date) VALUES(?, ?, ?, ?)');
    $insertMessage->execute(array($message, $_POST['friend_id'], $_SESSION['LOGGED_USER_id'], $send_date));
}

?>