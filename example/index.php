<?php

require_once '../PHPGangsta/GoogleAuthenticator.php';

session_start();

// Normally we would store the secret along side the user info but for demo purposes we'll save it in the session.
if (!array_key_exists('secret', $_SESSION)) {
    $secret = null;
} else {
    $secret = $_SESSION['secret'];
}
$ga = new PHPGangsta_GoogleAuthenticator($secret);
$_SESSION['secret'] = $ga->getSecret();

$isAuthenticated = false;
if ($_POST) {
    $isAuthenticated = $ga->verifyCode($_POST['code'], 2);    // 2 = 2*30sec clock tolerance
}


$imageUrl = $ga->getQRCodeGoogleUrl('MyApp');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <div class="row">
            <h1>Google Authenticator Example</h1>

            <h2>1. Install Google Authenticator</h2>
            <p>
                <a href="https://support.google.com/accounts/answer/1066447?hl=en">Download the app here</a>
            </p>
            <h2>2. Scan the Barcode with your Phone:</h2>
                <img src="<?= $imageUrl; ?>"  />
            <h3>2. Enter the code displayed to authenticate:</h3>
            <form role="form" method="POST">
                <?php
                if ($_POST) {
                    if ($isAuthenticated) {
                        echo '<div class="alert alert-success">Valid authentication code</div>';
                    } else {
                        echo  '<div class="alert alert-danger">Invalid authentication code</div>';
                    }
                }
                ?>
                <div class="form-group <?php if ($_POST) { print ($isAuthenticated) ? 'has-success' : 'has-error'; } ?>">
                    <label for="authenticationCode">Authentication Code</label>
                    <input type="text" class="form-control" id="authenticationCode" name="code" placeholder="Enter code" value="<?php echo ($_POST) ? htmlentities($_POST['code']) : ''; ?>">
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>


