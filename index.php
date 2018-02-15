<form method="POST">
    <input type="text" name="name" value="dean" placeholder="NAME"><br>
    <input type="text" name="telephone" value="+44204444555" placeholder="TELEPHONE"><br>
    <input type="text" name="postcode" value="CT16 1AA" placeholder="POSTCODE"><br>
    <input type="text" name="mobile" value="07908765432" placeholder="MOBILE"><br>
    <input type="submit" name="submit">
</form>

<?php

use vbpupil\InputValidation;

include 'vendor/autoload.php';

$check = array('telephone','postcode','mobile');

if(isset($_POST)) {
    $r = InputValidation::validate($_POST, $check);

    dump($r);
}