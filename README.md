# InputValidation
Simple form input validation. To be used when you need a quick on the fly no nonsense validation.

This simple class is not meant to replace some of he more advanced validation scripts, its simply a substitute for
when you have pre existing forms that are not part of any form solution and just need to validate
simply and quickly.

## Install
A. In your terminal enter ***composer require vbpupil/input-validation***


B. Once installed you will need to rename the contents of **src** directory, ie
>**definitions.example** change to **definitions.text**

>**error.example** change to **error.text**

>**success.example** change to **success.text**


## Usage

Create your form as normal.

note that **textarea** has a \* at the end of the name, this means that it is not a required field so 
will only be validated if text is present.
```html
<form method="POST">
     <input type="text" name="name" value="john" placeholder="NAME"><br>
     <input type="text" name="telephone" value="+44204444555" placeholder="TELEPHONE"><br>
     <input type="text" name="postcode" value="CT16 1AA" placeholder="POSTCODE"><br>
     <input type="text" name="mobile" value="07908765432'" placeholder="MOBILE"><br>
     <textarea name="textarea*">this is some test text'</textarea>
     <input type="submit" name="submit">
 </form>
 ```
 
 include the package.
 ```php
 use vbpupil\InputValidation;
 
 include 'vendor/autoload.php';
 
```

Set which inputs you want to check;

  ```php
 //indicates what inputs it should be checking
 $check = array('telephone','postcode','mobile','textarea');

 ```
 
 Implement check upon post.
```php
  if(isset($_POST)) {
      $r = InputValidation::validate($_POST, $check);
  
      var_dump($r);
  }
  ```