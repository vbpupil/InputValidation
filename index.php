<form method="POST">
    <input type="text" name="name" value="dean" placeholder="NAME"><br>
    <input type="text" name="telephone" value="+441604444555" placeholder="TELEPHONE"><br>
    <input type="text" name="postcode" value="nn57j y" placeholder="POSTCODE"><br>
    <input type="text" name="mobile" value="07908765432" placeholder="MOBILE"><br>
    <input type="text" name="amount" value="1.00" placeholder="Amount"><br>
    <input type="submit" name="submit">
</form>

<?php

use vbpupil\InputValidation;

include 'vendor/autoload.php';

$check = array('name','telephone','postcode','mobile', 'amount');

if(isset($_POST)) {
    InputValidation::validate($_POST, $check);
}