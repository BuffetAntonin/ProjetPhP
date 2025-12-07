<?php
require_once 'configuration.php';
require_once CHEMIN_ACCESSEUR . 'AccesseurConnexion.php';
use App\Accesseur\AccesseurConnexion;

session_start();
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$acc = new AccesseurConnexion();
list($ok, $data) = $acc->login($email, $password);
if (!$ok) {
    header('Location: login.php?error=' . urlencode($data));
    exit;
}
// success: set basic session
$_SESSION['user_id'] = $data['id'];
$_SESSION['user_name'] = $data['name'];

header('Location: dashboard.php');
exit;
