<?php
session_start();
if (isset($_SESSION["logged"]) && $_SESSION["logged"] === true) {
    header("Location: fileManager.php");
    exit();
}
if(isset($_POST["username"]) && isset($_POST["password"])) {
    $u = $_POST["username"];
    $p = $_POST["password"];
    $passes = file("../passwords.txt");
    foreach ($passes as $pass) {
        $pass = explode(':', trim($pass));
        if($pass[0] === $u && password_verify($p, $pass[1])) {
            $_SESSION['logged'] = true;
            $_SESSION['username'] = $u;
            header("Location: fileManager.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sessionLogin</title>
    <style>
	body {
		background-color: black;
		color: white;
	}

	#form {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}
	label {
		margin-top: 0.75rem;
	}
	button {
		background-color: #0000;
		border: 3px solid #0056b3;
		color: #0056b3;
		font-size: 1.5rem;
		padding: .5rem;
		border-radius: .75rem;
	}
    </style>
</head>

<body style="display: grid; place-items: center; height: 100vh;">
    <div style="width: min-content; font-size: 1.7rem; transform: translateY(-20%);">
        <form method="post" action="index.php">
	    <label>
                Name:
                <input type="text" name="username" required><br>
            </label><br>
	    <label>
                Password:
                <input type="password" name="password" required>
            </label><br><br>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>

</html>
