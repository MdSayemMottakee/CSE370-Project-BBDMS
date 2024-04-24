<?php 
session_start(); 

// Include the database configuration file
include('../includes/config.php');

// Establish database connection (replace these credentials with your actual database credentials)
$db_host = 'localhost';
$db_name = 'bbdms';
$db_user = 'root';
$db_pass = '';

try {
    $dbh = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit(); // Exit the script if the connection fails
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the current data of the user from the database
$sql = "SELECT 
            d.Occupation, 
            dc.Contact, 
            r.Remarks, 
            dl1.Last_Date_Of_Donation, 
            dl1.Amount_Of_Blood_Donated, 
            dl2.Donation_Frequency
        FROM 
            Donor d
            INNER JOIN DonorContact dc ON d.User_ID = dc.User_ID
            INNER JOIN Remark r ON d.User_ID = r.User_ID
            INNER JOIN Donation_Log_1 dl1 ON d.User_ID = dl1.User_ID
            INNER JOIN Donation_Log_2 dl2 ON d.User_ID = dl2.User_ID
        WHERE 
            d.User_ID = :id";

try {
    $statement = $dbh->prepare($sql);
    $statement->execute([':id' => $id ]);
    
    // Fetch the user data
    $person = $statement->fetch(PDO::FETCH_ASSOC);

    // Check if the form is submitted
    if (isset($_POST['submit'])) {   
        // Extract data from the POST array
        $occupation = $_POST['Occupation'];
        $contact = $_POST['Contact'];
        $remarks = $_POST['Remarks'];
        $lastDateOfDonation = $_POST['Last_Date_Of_Donation'];
        $amountOfBloodDonated = $_POST['Amount_Of_Blood_Donated'];
        $donationFrequency = $_POST['Donation_Frequency'];

        // Prepare the SQL query
        $sql = 'UPDATE Donor d
                INNER JOIN DonorContact dc ON d.User_ID = dc.User_ID
                INNER JOIN Remark r ON d.User_ID = r.User_ID
                INNER JOIN Donation_Log_1 dl1 ON d.User_ID = dl1.User_ID
                INNER JOIN Donation_Log_2 dl2 ON d.User_ID = dl2.User_ID
                SET 
                    d.Occupation = :occupation, 
                    dc.Contact = :contact, 
                    r.Remarks = :remarks, 
                    dl1.Last_Date_Of_Donation = :lastDateOfDonation, 
                    dl1.Amount_Of_Blood_Donated = :amountOfBloodDonated, 
                    dl2.Donation_Frequency = :donationFrequency 
                WHERE 
                    d.User_ID = :id';

        // Prepare the statement
        $statement = $dbh->prepare($sql);

        // Bind parameters
        $statement->bindParam(':occupation', $occupation);
        $statement->bindParam(':contact', $contact);
        $statement->bindParam(':remarks', $remarks);
        $statement->bindParam(':lastDateOfDonation', $lastDateOfDonation);
        $statement->bindParam(':amountOfBloodDonated', $amountOfBloodDonated);
        $statement->bindParam(':donationFrequency', $donationFrequency);
        $statement->bindParam(':id', $id);

        // Execute the statement
        $statement->execute();

        // Redirect to DonorPanel.php after successful update
        header("Location: DonorPanel.php?user_id=$id");
        exit(); // Ensure no further code is executed after redirection
    }
} catch (PDOException $e) {
    // Handle any exceptions
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <title>Blood Bank Donor Management System | Update Donor Profile</title>
    <!-- Meta tag Keywords -->

    <script>
        addEventListener("load", function () {
            setTimeout(hideURLbar, 0);
        }, false);

        function hideURLbar() {
            window.scrollTo(0, 1);
        }
    </script>
    <!--// Meta tag Keywords -->

    <!-- Custom-Files -->
    <link rel="stylesheet" href="css/bootstrap.css">
    <!-- Bootstrap-Core-CSS -->
    <link rel="stylesheet" href="css/style.css" type="text/css" media="all" />
    <!-- Style-CSS -->
    <link rel="stylesheet" href="css/fontawesome-all.css">
    <!-- Font-Awesome-Icons-CSS -->
    <!-- //Custom-Files -->

    <!-- Web-Fonts -->
    <link href="//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i&amp;subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese"
        rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i&amp;subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese"
        rel="stylesheet">
    <!-- //Web-Fonts -->

</head>

<body>
<div class="container">
  <div class="card mt-5">
    <div class="card-header">
      <h2>Update Donor Profile</h2>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="form-group">
          <label for="Occupation">Occupation</label>
          <input value="<?= isset($person['Occupation']) ? $person['Occupation'] : ''; ?>" type="text" name="Occupation" id="Occupation" class="form-control">
        </div>
        <div class="form-group">
          <label for="Contact">Contact</label>
          <input value="<?= isset($person['Contact']) ? $person['Contact'] : ''; ?>" type="text" name="Contact" id="Contact" class="form-control">
        </div>
        <div class="form-group">
          <label for="Remarks">Remarks</label>
          <input value="<?= isset($person['Remarks']) ? $person['Remarks'] : ''; ?>" type="text" name="Remarks" id="Remarks" class="form-control">
        </div>
        <div class="form-group">
            <label for="Last_Date_Of_Donation">Last Date of Donation</label>
            <input value="<?= isset($person['Last_Date_Of_Donation']) ? $person['Last_Date_Of_Donation'] : ''; ?>" type="date" name="Last_Date_Of_Donation" id="Last_Date_Of_Donation" class="form-control">
        </div>
        <div class="form-group">
          <label for="Amount_Of_Blood_Donated">Amount of Blood Donated</label>
          <input value="<?= isset($person['Amount_Of_Blood_Donated']) ? $person['Amount_Of_Blood_Donated'] : ''; ?>" type="text" name="Amount_Of_Blood_Donated" id="Amount_Of_Blood_Donated" class="form-control">
        </div>
        <div class="form-group">
          <label for="Donation_Frequency">Donation Frequency</label>
          <input value="<?= isset($person['Donation_Frequency']) ? $person['Donation_Frequency'] : ''; ?>" type="text" name="Donation_Frequency" id="Donation_Frequency" class="form-control">
        </div>
        <div class="form-group">
          <button type="submit" name="submit" class="btn btn-info">Update Donor Profile</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Js files -->
<!-- JavaScript -->
<script src="../js/jquery-2.2.3.min.js"></script>
<!-- Default-JavaScript-File -->

<!-- fixed navigation -->
<script src="../js/fixed-nav.js"></script>
<!-- //fixed navigation -->

<script src="../js/bootstrap.js"></script>
<!-- Necessary-JavaScript-File-For-Bootstrap -->

</body>

</html>
