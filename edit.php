<?php

session_start();

if ( ! isset($_SESSION['name']) ) {
    die("Not logged in");
}

// If the user requested logout go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

$status = false;

if ( isset($_SESSION['status']) ) {
    $status = htmlentities($_SESSION['status']);
    $status_color = htmlentities($_SESSION['color']);

    unset($_SESSION['status']);
    unset($_SESSION['color']);
}

try 
{
    $pdo = new PDO("mysql:host=localhost;dbname=coursera_javascript_jquery_and_json", "root", "root");
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
    die();
}

$name = htmlentities($_SESSION['name']);

$_SESSION['color'] = 'red';

if (isset($_REQUEST['profile_id']))
{

    $profile_id = htmlentities($_REQUEST['profile_id']);

    // Check to see if we have some POST data, if we do process it
    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) 
    {
        if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1)
        {
            $_SESSION['status'] = "All fields are required";
            header("Location: edit.php?profile_id=" . htmlentities($_REQUEST['profile_id']));
            return;
        }

        if (strpos($_POST['email'], '@') === false)
        {
            $_SESSION['status'] = "Email address must contain @";
            header("Location: edit.php?profile_id=" . htmlentities($_REQUEST['profile_id']));
            return;
        }

        $first_name = htmlentities($_POST['first_name']);
        $last_name = htmlentities($_POST['last_name']);
        $email = htmlentities($_POST['email']);
        $headline = htmlentities($_POST['headline']);
        $summary = htmlentities($_POST['summary']);

        $stmt = $pdo->prepare("
            UPDATE profile
            SET first_name = :first_name, last_name = :last_name, email = :email, headline = :headline, summary = :summary
            WHERE profile_id = :profile_id
        ");

        $stmt->execute([
            ':first_name' => $first_name, 
            ':last_name' => $last_name, 
            ':email' => $email,
            ':headline' => $headline,
            ':summary' => $summary,
            ':profile_id' => $profile_id,
        ]);

        $_SESSION['status'] = 'Record edited';
        $_SESSION['color'] = 'green';

        header('Location: index.php');
        return;
    }

    $stmt = $pdo->prepare("
        SELECT * FROM profile 
        WHERE profile_id = :profile_id
    ");

    $stmt->execute([
        ':profile_id' => $profile_id, 
    ]);

    $profile = $stmt->fetch(PDO::FETCH_OBJ);

}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Khaled Dawood Ismaeel Khalifa</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <h1>Editing Profile for <?php echo $name; ?></h1>
            <?php
                if ( $status !== false ) 
                {
                    // Look closely at the use of single and double quotes
                    echo(
                        '<p style="color: ' .$status_color. ';" class="col-sm-10 col-sm-offset-2">'.
                            htmlentities($status).
                        "</p>\n"
                    );
                }
            ?>
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-sm-2" for="first_name">First Name:</label>
                    <div class="col-sm-5">
                        <input class="form-control" type="text" name="first_name" id="first_name" value="<?php echo $profile->first_name; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="last_name">Last Name:</label>
                    <div class="col-sm-5">
                        <input class="form-control" type="text" name="last_name" id="last_name" value="<?php echo $profile->last_name; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="email">Email:</label>
                    <div class="col-sm-5">
                        <input class="form-control" type="text" name="email" id="email" value="<?php echo $profile->email; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="headline">Headline:</label>
                    <div class="col-sm-5">
                        <input class="form-control" type="text" name="headline" id="headline" value="<?php echo $profile->headline; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="summary">Summary:</label>
                    <div class="col-sm-5">
                        <textarea class="form-control" name="summary" id="summary" rows="8"><?php echo $profile->summary; ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-2 col-sm-offset-2">
                        <input class="btn btn-primary" type="submit" value="Save">
                        <input class="btn" type="submit" name="cancel" value="Cancel">
                    </div>
                </div>
            </form>

        </div>
    </body>
</html>