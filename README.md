# InputValidation
Simple form input validation. To be used when you need a quick on the fly no nonsense validation.

This simple class is not meant to replace some of he more advanced validation scripts, its simply a substitute for
when you have pre existing forms that are not part of any form solution and just need to validate
simply and quickly. Validation now includes CSRF which is handled via $_SESSION.

## Install
A. In your terminal enter ***composer require vbpupil/input-validation***


B. Once installed you will need to take a copy of the *config* directory and place it to be outside of the *vendor dir*, ie

```
project
│
└───config
│   │
│   └───InputValidation
│               config.yml
│   
└───vendor
    │
    └───vbpupil
```

>This directory contains a yaml file that allow you to manage your definitions aswell as err and success return text. 


## Usage

Create your form as normal.

1. note that **textarea** has a \* at the end of the name, this means that it is not a required field so 
will only be validated if text is present.

2. also note that if you require CSRF support then simply add in the call below. The argument sent in **form-1** is the
unique id for the form. Also worth noting that session start is required before the call to ensure that the SESSION values persist.

```html
<?php session_start(); ?>

<form method="POST">
     <?= InputValidation::createToken('form-1') ?>
     <input type="text" name="name" value="john" placeholder="NAME"><br>
     <input type="text" name="telephone" value="+44204444555" placeholder="TELEPHONE"><br>
     <input type="text" name="postcode" value="CT16 1AA" placeholder="POSTCODE"><br>
     <input type="text" name="mobile" value="07908765432'" placeholder="MOBILE"><br>
     <textarea name="textarea*">this is some test text'</textarea>
     <input type="submit" name="submit">
 </form>
 ```
 
3. include the package.


 ```php
 use vbpupil\InputValidation;
 
 include 'vendor/autoload.php';
```

4. set which inputs you want to check;

  ```php
 //indicates what inputs it should be checking
 $check = array('telephone','postcode','mobile','textarea');

 ```

5. implement check upon post.

```php
  if(isset($_POST)) {
      $r = InputValidation::validate($_POST, $check);
  
      var_dump($r);
  }
  ```