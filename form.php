<?php
include 'top.php';
//%^%^%^%^%^%^%^%^%^%^%
//
//SECTION: 1 initialize variables
//
//sc. 1a.
//if ($debug) {
print '<p>Post Attay:</p><pre>';
print_r($_POST);
print '</pre>';
//}
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// sc. 1b Security
//
// define security variable to be used in SECTION 2a.

$thisURL = $domain . $phpSelf;



//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// sc. 1c form variables
//
// Initialize variables one for each form element
// in the order they appear on the form

$firstName = "";
$comments = "";

$email = "xwan@uvm.edu";
$commentsERROR = false;
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// sc. 1d form error flags
//
// Initialize Error Flags one for each form element we validate
// in the order they appear in section 1c.
// 
// 
$firstNameERROR = false;
// 
// 
$emailERROR = false;

////%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// sc. 1e misc variables
//
// create array to hold error messages filled (if any) in 2d displayed in 3c. 
$errorMsg = array();
// 
// array used to hold form values that will be written to a CSV file
$dataRecord = array();
// 
// mailed the information to the user?
$mailed = false;
// 
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2 Process for when the form is submitted
//
if (isset($_POST["btnSubmit"])) {

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// sc. 2a Security
// 
    if (!securityCheck($thisURL)) {
        $msg = '<p>Sorry you cannot access this page. ';
        $msg .= 'Security breach detected and reported.</p>';
        die($msg);
    }


//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// sc. 2b Sanitize (clean) data  
// remove any potential JavaScript or html code from users input on the
// form. Note it is best to follow the same order as declared in section 1c.
    $firstName = htmlentities($_POST["txtFirstName"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $firstName;

    $comments = htmlentities($_POST["txtComments"], ENT_QUOTES, "UTF-8");

    $email = filter_var($_POST["txtEmail"], FILTER_SANITIZE_EMAIL);
    $dataRecord[] = $email;
    $dataRecord[] = $comments;



//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// sc. 2c Validation
// 
// Validation section. Check each value for possible errors, empty or
// not what we expect. You will need an IF block for each element you will
// check (see above section 1c and 1d). The if blocks should also be in the
// order that the elements appear on your form so that the error messages
// will be in the order they appear. errorMsg will be displayed on the form
// see section 3b. The error flag ($emailERROR) will be used in section 3c.
    if ($firstName == "") {
        $errorMsg[] = "Please enter your first name";
        $firstNameERROR = true;
    } elseif (!verifyAlphaNum($firstName)) {
        $errorMsg[] = "Your first name appears to have extra character.";
        $firstNameERROR = true;
    }

    if ($email == "") {
        $errorMsg[] = 'Please enter your email address';
        $emailERROR = true;
    } elseif (!verifyEmail($email)) {
        $errorMsg[] = 'Your email address appears to be incorrect.';
        $emailERROR = true;
    }

    if ($comments != ""){
        if (!verifyAlphaNum($comments)){
            $errorMsg[] = "Your comments appear to have extra characters that are not allowed.";
            $commentsERROR = true;
        }
    }
    
    ?>
<fieldset class="textarea">
    <p>
        <label class="required"for="txtComments">Comments</label>
        <textarea <?php if ($commentsERROR) print 'class="mistake"';?>
            id="txtComments"
            name="txtComments"
            onfocus="this.select()"
            tabindex="200"><?php print $comments; ?></textarea>
        
    </p>
    
</fieldset>
          







<?php
//
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// sc. 2d Process Form - Passed Validation
// 
// Process for when the form passes validation (the errorMsg array is empty)

    if (!$errorMsg) {
        if ($debug)
            print PHP_EOL . '<p>Form is valid</p>';
//    
//     
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// sc. 2e Save Data
// 
// This block saves the data to a CSV file.   
        $myFolder = 'data/';
// 
        $myFileName = 'registration';

        $fileExt = '.csv';

        $fileName = $myFolder . $myFileName . $fileExt;
        if ($debug)
            print PHP_EOL . '<p>file name is ' . $fileName;

//now just open the file for append
        $file = fopen($fileName, 'a');
// 
// write the forms informations
        fputcsv($file, $dataRecord);
// 
// close the file
        fclose($file);
// 
// 
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// sc. 2f Create message
// 
// build a message to display on the screen in section 3a and to mail
// to the person filling out the form (section 2g).
// 
        $message = '<h2>Your information.</h2>';

        foreach ($_POST as $htmlName => $value) {

            $message .= '<p>';
            //break up the form names into words

            $camelCase = preg_split('/(?=[A-Z])/', substr($htmlName, 3));

            foreach ($camelCase as $oneWoed) {
                $message .= $oneWord . ' ';
            }

            $message .= ' = ' . htmlentities($value, ENT_QUOTES, "UTF-8") . '</p>';
        }
// 
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// sc. 2g Mail to user
// 
// Process for mailing a message which contains the forms data
// the message was built in section 2f.
        $to = $email; // the person who filled out the form
        $cc = '';
        $bcc = '';

        $from = 'XintianWan <xwan@uvm.edu>';

        // subject of mail 
        $subject = 'Changing Earth: ';

        $mailed = sendMail($to, $cc, $bcc, $from, $subject, $message);
    } // end form is valid
}  //end if form was submitted
// 
// 
//#############################################################################
//
// SECTION 3 Display Form
//
?>

<article id="main">

    <?php
//####################################
//
// sc. 3a. 
// 
// If its the first time coming to the form or there are errors we are going
// to display the form.
    if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) {// closing of if marked with: end body submit
        print '<h2>Thank you for providing your information.</h2>';

        print '<p>For your records a copy of this data has ';

        if (!$mailed) {
            print "not ";
        }
        print 'been sent:</p>';
        print '<p>To: ' . $email . '</p>';
        print $message;
    } else {

        print '<h2>Register Today</h2>';
        print '<p class="form-heading">You information will greatly help us with our research.</p>';

//####################################
//
// sc. 3b Error Messages
// 
// display any error messages before we print out the form

        if ($errorMsg) {
            print '<div id="errors">' . PHP_EOL;
            print '<h2>Your form has the following mistakes that need to be fixed.</h2>' . PHP_EOL;
            print '<ol>' . PHP_EOL;

            foreach ($errorMsg as $err) {
                print '<li>' . $err . '</li>' . PHP_EOL;
            }

            print '</ol>' . PHP_EOL;
            print '</div>' . PHP_EOL;
        }
// 
// 
//####################################
//
// sc. 3c html Form
//
        /* Display the HTML form. note that the action is to this same page. $phpSelf
          is defined in top.php
          NOTE the line:
          value="<?php print $email; ?>
          this makes the form sticky by displaying either the initial default value (line ??)
          or the value they typed in (line ??)
          NOTE this line:
          <?php if($emailERROR) print 'class="mistake"'; ?>
          this prints out a css class so that we can highlight the background etc. to
          make it stand out that a mistake happened here.
         */
        ?>

        <form action="<?php print $phpSelf; ?>"
              id="frmRegister"
              method="post">

            <fieldset class="contact">
                <legend>Contact Information</legend>
                <p>
                    <label class="requires text-firld" for="txtFirstName">First Name</label>
                    <input autofocus
                    <?php if ($firstNameERROR) print 'class="mistake"'; ?>
                           id="txtFirstName"
                           maxlength="45"
                           name="txtFirstName"
                           onfocus="this.select()"
                           placeholder="Enter your first name"
                           tabindex="100"
                           type="text"
                           value="<?php print $firstName; ?>"
                           >
                </p>

                <p>   
                    <label class="required text-field" for="txtEmail">Email</label>
                    <input 
                    <?php if ($emailERROR) print 'class="mistake"'; ?>
                        id="txtEmail"
                        maxlength="45"
                        name="txtEmail"
                        onfocus="this.select()"
                        placeholder="Enter a valid email address"
                        tabindex="120"
                        type="text"
                        value="<?php print $email; ?>"
                        >
                </p>
            </fieldset> <!-- ends contact -->

            <fieldset class="buttons">
                <legend></legend>
                <input class="button" id="btnSubmit" name="btnSubmit" tabindex="900" type="submit" value="Register" >
            </fieldset> <!-- ends buttons -->
        </form>

        <?php
    } //end body submit
    ?>



</article>

<?php include 'footer.php'; ?>

</body>
</html>
