<?php

use Pico\Data\Entity\User;

require_once __DIR__."/app.php";
require_once __DIR__."/session.php";

$appUser = new User(null, $database);

if(isset($_SESSION) && isset($_SESSION['suser']) && isset($_SESSION['spass']))
{
    try
    {
        $username = $_SESSION['suser'];
        $password = $_SESSION['spass'];

        $appUser->findOneByUsernameAndPasswordAndBlockedAndActive($username, $password, false, true);
    }
    catch(Exception $e)
    {
        exit();
    }
}
else
{
    exit();
}