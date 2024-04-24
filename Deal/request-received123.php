<?php 
error_reporting(0);
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
}

// Get the donor user ID from the session
$donorUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch patient details if necessary
$result = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cost = $_POST['cost'];
    $pUserID = isset($_POST['pUserID']) ? $_POST['pUserID'] : ''; // Retrieve patient ID from form submission
    $transactionID = isset($_POST['transactionID']) ? $_POST['transactionID'] : ''; // Retrieve transaction ID from form submission

    try {
        // Prepare INSERT statement
        $sql = "INSERT INTO thirdlookingfor (User_ID, Cost) VALUES (:userID, :cost)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':userID', $donorUserId, PDO::PARAM_INT);
        $stmt->bindParam(':cost', $cost, PDO::PARAM_STR);
    
        // Execute the statement
        $stmt->execute();
        
        // Redirect to a success page or display a success message
        header("Location: DonorPanel.php?user_id=$donorUserId");
        exit();
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
    }
}

try {
    // Prepare SELECT statement to retrieve requests received by the donor for the provided user ID from the Payment table
    $sql = "SELECT * FROM Payment WHERE DuserID = :donorUserId";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':donorUserId', $donorUserId, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();
    
    // Check if there are any requests
    if ($stmt->rowCount() > 0) {
        // Display the requests
        echo "<h2>Requests Received:</h2>";
        echo "<div class='row'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Fetch additional patient details
            $sqlPatientDetails = "SELECT 
                u.User_ID, 
                u.Name, 
                p.Age, 
                p.Hospital_Id, 
                GROUP_CONCAT(Issues SEPARATOR ', ') AS HealthIssues, 
                ec.Contact_ID, 
                ec.Relationship, 
                pc.Contact AS EmergencyContact, 
                cn.Name AS EmergencyContactName,
                h.Name AS HospitalName,
                h.Street AS HospitalStreet,
                hl.City AS HospitalCity,
                hl.Zip_Code AS HospitalZipCode
            FROM 
                User u 
            INNER JOIN 
                Patient p ON u.User_ID = p.User_ID 
            LEFT JOIN 
                Issue i ON p.User_ID = i.User_ID
            LEFT JOIN 
                EmergencyContact ec ON p.User_ID = ec.User_ID
            LEFT JOIN 
                PatientContact pc ON p.User_ID = pc.User_ID
            LEFT JOIN 
                ContactName cn ON ec.Contact_ID = cn.Contact_ID
            LEFT JOIN 
                Hospital h ON p.Hospital_Id = h.Hospital_Id
            LEFT JOIN 
                HospitalLocation hl ON h.City = hl.City
            WHERE 
                u.User_ID = :puserId
            GROUP BY 
                u.User_ID";
            $stmtPatientDetails = $dbh->prepare($sqlPatientDetails);
            $stmtPatientDetails->bindParam(':puserId', $row['PuserID'], PDO::PARAM_INT);
            $stmtPatientDetails->execute();
            $result = $stmtPatientDetails->fetch(PDO::FETCH_ASSOC);
            ?>
            <!-- Display patient details -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        Patient Details
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlentities($result['Name']); ?> (User ID: <?php echo htmlentities($result['User_ID']); ?>)</h5>
                        <p class="card-text"><strong>Age:</strong> <?php echo htmlentities($result['Age']); ?></p>
                        <p class="card-text"><strong>Health Issues:</strong> <?php echo htmlentities($result['HealthIssues']); ?></p>
                        <p class="card-text"><strong>Emergency Contact:</strong> <?php echo htmlentities($result['EmergencyContact']); ?></p>
                        <p class="card-text"><strong>Relationship:</strong> <?php echo htmlentities($result['Relationship']); ?></p>
                        <p class="card-text"><strong>Emergency Contact Name:</strong> <?php echo htmlentities($result['EmergencyContactName']); ?></p>
                        <p class="card-text"><strong>Hospital Name:</strong> <?php echo htmlentities($result['HospitalName']); ?></p>
                        <p class="card-text"><strong>Hospital Address:</strong> <?php echo htmlentities($result['HospitalStreet'] . ', ' . $result['HospitalCity'] . ', ' . $result['HospitalZipCode']); ?></p>
                    </div>
                </div>
            </div>
        <?php
        }
        echo "</div>";
    } else {
        echo "No requests received by this donor for the provided user ID.";
    }
} catch (PDOException $e) {
    // Display error message
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests Received</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Requests Received
                </div>
                <div class="card-body">
                    <?php
                    // PHP code to display the requests
                    if ($stmt->rowCount() > 0) {
                        echo "<h4>Requests Received:</h4>";
                        echo "<ul class='list-group'>";
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<li class='list-group-item'>Request ID: " . $row['Transaction_ID'] . ", Patient ID: " . $row['PuserID'] . "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>No requests received by this donor for the provided user ID.</p>";
                    }
                    ?>

                    <!-- Form to input the cost -->
                    <form method="post">
                        <div class="form-group">
                            <label for="cost">Enter Cost (BDT):</label>
                            <input type="number" step="10" class="form-control" id="cost" name="cost" required>
                            <!-- Hidden input to pass the Patient ID -->
                            <input type="hidden" name="pUserID" value="<?php echo isset($result['User_ID']) ? htmlentities($result['User_ID']) : ''; ?>">
                            <!-- Hidden input to pass the Transaction ID -->
                            <input type="hidden" name="transactionID" value="<?php echo isset($row['Transaction_ID']) ? htmlentities($row['Transaction_ID']) : ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Cost</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
