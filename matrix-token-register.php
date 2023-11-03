<?php
$server_name = 'YOURSERVERNAMEHERE';
$base_url = 'https://matrix.example.com/';
$api_url = $base_url."_matrix/client/v3/register";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"];
    $password2 = $_POST["password2"];
    $username = $_POST["username"];
    if (!empty($_POST["session"])) {
        $session = $_POST["session"];
        $auth_token = $_POST["auth_token"];
        
        $data = array(
            "auth" => array(
                "session" => $session,
                "type" => "m.login.registration_token",
                "token" => $auth_token
            )
        );
        $data_json = json_encode($data);
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
      
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);

      $response_data = json_decode($response, true);

        if (stripos(json_encode($response_data), 'error') !== false) {
            $error_message = isset($response_data['error']) ? $response_data['error'] : 'API Error';
        } else if (isset($response_data['access_token'])) {
            exit;
        } else {
            $data = array(
                "auth" => array(
                    "session" => $session,
                    "type" => "m.login.dummy"
                )
            );

            $data_json = json_encode($data);

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'cURL Error: ' . curl_error($ch);
            }

            curl_close($ch);
            $response_data = json_decode($response, true);

            if (stripos(json_encode($response_data), 'error') !== false) {
                $error_message = isset($response_data['error']) ? $response_data['error'] : 'API Error';
            } else {
                $registration_message = 'Registration Successful';
            }
        }
    } else {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$!%^&*])[A-Za-z\d@#$!%^&*]{8,}$/';
        if ( $password != $password2)
        {
            $error_message = "Passwords do not match.";
        } else {
        if (!preg_match($pattern, $password)) {
            $error_message = "Password is invalid.<br>It must be at least 8 characters long,<br>contain at least 1 lowercase letter,<br>1 uppercase letter,<br>1 number,<br>1 special character.";
        } else {

        $data = array(
            "kind" => "user",
            "device_id" => "",
            "password" => $password,
            "username" => $username
        );

        $data_json = json_encode($data);
        $ch = curl_init($api_url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);
        $response_data = json_decode($response, true);

        if (isset($response_data['session'])) {
            $session = $response_data['session'];
        } else {
            $error_message = isset($response_data['error']) ? $response_data['error'] : 'API Error';
        }
    }
        }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $server_name; ?> Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        .error-message {
            color: red;
            font-size: 16px;
            margin: 10px 0;
        }
        .registration-message {
            color: green;
            font-size: 16px;
            margin: 10px 0;
        }
        .form-input {
            width: 95%;
            padding: 10px;
            margin: 0;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 16px;
        }
        .form-button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 16px;
            cursor: pointer;
        }
        .back-button {
            margin-top: 10px;
            background-color: #ccc;
            color: black;
            border: none;
            padding: 10px;
            border-radius: 3px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
<h2><?php echo $server_name; ?><h2><small>Create a new account.</small><br><br>
      <?php if (!empty($error_message)) { ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php } ?>

        <?php if (!empty($registration_message)) { ?>
            <div class="registration-message">
                        <?php echo $registration_message;?>
<br><br>Please continue in the app.<br>
Select custom server.<br>
enter url: <u><?php echo $base_url; ?></u><br>
<br>The App can be installed from<br>
<a href="https://play.google.com/store/apps/details?id=de.spiritcroc.riotx" alt="Get it on Google Play" target="_blank"><img src="gplay.png" width="200"></a></div>
         <?php } ?>

        <?php if (!empty($session) && empty($auth_token)) { ?>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input class="form-input" type="hidden" name="session" value="<?php echo $session; ?>">
                <label for="auth_token">Authorization Token:</label>
                <input class="form-input" type="text" name="auth_token" id="auth_token" required>
                <br>
                <input class="form-button" type="submit" value="Authorize">
            </form>
        <?php } elseif (empty($session) && empty($auth_token) && empty($error_message)) { ?>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <label for="username">Username:</label>
                <input class="form-input" type="text" name="username" id="username" required>
                <br>
                <label for="password">Password:</label>
                <input class="form-input" type="password" name="password" id="password" required>
                <br>
                <label for="password2">Confirm Password:</label>
                <input class="form-input" type="password" name="password2" id="password2" required>
                <br>
                <input class="form-button" type="submit" value="Register">
            </form>
        <?php } ?>

        <?php if (!empty($error_message)) { ?>
            <button class="back-button" onclick="goBack()">Go Back</button>
        <?php } ?>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
