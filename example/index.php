<?php

require_once '../vendor/autoload.php';

$secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

$ga = new Symm\GoogleAuthenticator\GoogleAuthenticator('user@example.com', $secret);
$endroid = new \Symm\GoogleAuthenticator\QrCodeGenerator\GoogleChartQrCodeGenerator();

$ga->setIssuer('example.com');
$ga->setQrCodeGenerator($endroid);
$ga->setTolerance(1);

$isAuthenticated = false;
if ($_POST) {
    $isAuthenticated = $ga->verifyCode($_POST['code']);
}

$imageUrl = $ga->getQRCodeUrl();

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
            <div class="col-md-12">
                <h1>Google Authenticator Example</h1>
            </div>
            <div class="col-md-6">
                <h3>1. Install Google Authenticator</h3>
                <p>
                    <a href="https://support.google.com/accounts/answer/1066447?hl=en">Download the app here</a>
                </p>
                <h3>2. Scan the QR Code with your device:</h3>
                <img src="<?= $imageUrl; ?>"  />
            </div>
            <div class="col-md-6">
            <h3>2. Enter the code displayed:</h3>
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
                    <input type="text" class="form-control" id="authenticationCode" autocomplete="off"
                           name="code" placeholder="Enter code" value="<?php echo ($_POST) ? htmlentities($_POST['code']) : ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
            </div>
        </div>
    </div>
</body>
</html>


