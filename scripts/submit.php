<?php
/**
 * This script handles the form processing
 *
 * PHP version 7.2
 *
 * @category Registration
 * @package  Registration
 * @author   Benson Imoh,ST <benson@stbensonimoh.com>
 * @license  GPL https://opensource.org/licenses/gpl-license
 * @link     https://stbensonimoh.com
 */
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// echo json_encode($_POST);

// // Pull in the required files
require '../config.php';
require './DB.php';
require './Notify.php';
// require './Newsletter.php';

// Capture the post data coming from the form
$firstName = htmlspecialchars($_POST['firstName'], ENT_QUOTES);
$lastName = htmlspecialchars($_POST['lastName'], ENT_QUOTES);
$email = $_POST['email'];
$phone = $_POST['full_phone'];
$gender = htmlspecialchars($_POST['gender'], ENT_QUOTES);
$dob = htmlspecialchars($_POST['dob'], ENT_QUOTES);
$story = htmlspecialchars($_POST['story'], ENT_QUOTES);

$details = array(
    "firstName" => $firstName,
    "lastName" => $lastName,
    "email" => $email,
    "phone" => $phone,
    "gender" => $gender,
    "dob" => $dob,
    "story" => $story
);

$db = new DB($host, $db, $username, $password);

$notify = new Notify($smstoken, $emailHost, $emailUsername, $emailPassword, $SMTPDebug, $SMTPAuth, $SMTPSecure, $Port);

// $newsletter = new Newsletter($apiUserId, $apiSecret);

// First check to see if the user is in the Database
if ($db->userExists($email, "impactstory")) {
    echo json_encode("user_exists");
} else {
    // Insert the user into the database
    $db->getConnection()->beginTransaction();
    $db->insertUser("impactstory", $details);
    // Send SMS
    $notify->viaSMS(
            "YouthSummit",
            "Dear {$firstName} {$lastName}, thank you for sharing your inspiring story with us. We agree that you've got what it takes. However, We can only have 6 of you share your story on the #AWLOYouthSummit Impact Story. Look out for our email in the coming days to know if you were selected. All the Best!
        - The AWLO Team",
            $phone
    );

//     /**
//      * Add User to the SendPulse Mail List
//      */
//     $emails = array(
//             array(
//                 'email'                            => $email,
//                 'variables'                        => array(
//                     'name'                         => $name,
//                     'phone'                        => $phone,
//                     'businessName'                 => $businessName,
//                     'businessDescription'          => $businessDescription
//                 )
//             )
//         );

//     $newsletter->insertIntoList("239101", $emails);

    $name = $firstName . ' ' . $lastName;
    // Send Email
    require './emails.php';
    // Send Email
    $notify->viaEmail("youthsummit@awlo.org", "AWLO Youth Summit", $email, $name, $emailBody, "Hi {$firstName}! Thank you for sharing your Impact Story.");

    $db->getConnection()->commit();

    echo json_encode("success");
}
