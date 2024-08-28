<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Registration Form</title>
    <link rel="stylesheet" href="styles.css" />
    <body style='text-align: center;'>
    
    <style> 
	table, th, td {
                border: 1px solid white;
                border-collapse: collapse;
        }

	th, td {
                padding: 15px; 
        }

	table.center {
                margin-left: auto; 
                margin-right: auto;
        }
        
       	caption {
                text-align:left;
                font-size: 20px;
                margin-bottom: 20px;
        }
    </style>

    <div style="display: flex; justify-content: center; align-items: center;"></div>
  </head>
<body>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_FILES['clientCert']) && $_FILES['clientCert']['error'] == UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['clientCert']['tmp_name'];
                $fileName = $_FILES['clientCert']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $allowedExts = array('key', 'pem');

                $department = filter_input(INPUT_POST, 'Departments', FILTER_SANITIZE_STRING);
                if (in_array($fileExtension, $allowedExts)) {
                        $fileContent = file_get_contents($fileTmpPath);
                        $rootContent = file_get_contents("/var/www/html/". $department . "CA.pem");     
                        $parsed = openssl_x509_parse($fileContent);
                        $valid = openssl_x509_verify($fileContent,$rootContent);
                        
                       	if (isset($parsed['subject']['CN'])) {
                                $displayName = strtoupper( $parsed["subject"]["CN"]);   
                        }
                        else{
                             	$displayName = '';
                        }

                        if ($valid == 1){
                                $username="employee";
                                $password="employee123";
                                $database=$department;
                                $mysqli=new mysqli("localhost", $username, $password, $database);

                                if ($mysqli->connect_error) {
                                        die("Connection failed: " . $mysqli->connect_error);
                                }
                                $displayDepartment = strtoupper($database);
                                echo "<h1>Welcome $displayName to the $displayDepartment Department Database.</h1> <br><br>";

                                $getTables = "SHOW TABLES";
                                $tblResult = $mysqli->query($getTables);
                                $tables = array();


                                while ($tblRow = mysqli_fetch_row($tblResult)) {
                                        array_push($tables, $tblRow[0]);
                                }

                                foreach ($tables as $tblName){

                                        $query = "SELECT * FROM `$tblName`";

                                        $result = $mysqli->query($query);
                                        $info = array();
                                        while ($row = $result->fetch_assoc()) {
                                                $info[] = $row; 
                                        }

                                        $col = array_keys(reset($info));
                                        echo "<table class = 'center'>";
                                        echo "<caption>Table Name: $tblName </captiion>";
                                        echo "<tr>";
                                        foreach ($col as $header) {
                                                echo "<th>" . $header . "</th>";
                                        }
                                        echo "</tr>";

                                        foreach ($info as $row) {
                                                echo "<tr>";
                                                foreach ($col as $header) {
                                                        echo "<td>" . $row[$header] . "</td>";
                                                }
                                                echo "</tr>";
                                        }
                                }
                                echo "</table>";
                                

                        }
                        else { 
                                echo "Invalid Certificate.";
                                echo '<p>Please select correct department/certificate and try again.</p>';
                                echo '<form action="index.html" method="get">';
                                echo '<button type="submit">Try Again</button>';
                                echo '</form>';
                        }
                } 
                else {
                      	echo "Invalid file extension. Allowed extensions are: " . implode(", ", $allowedExts);
                        echo '<Please try again.</p>';
                        echo '<form action="index.html" method="get">';
                        echo '<button type="submit">Try Again</button>';
                        echo '</form>';
                }
        } 
        else {
                echo "No file uploaded or there was an upload error.";
                echo '<p>Please try again.</p>';
                echo '<form action="index.html" method="get">';
                echo '<button type="submit">Try Again</button>';
                echo '</form>';
        }
} 
else {
      	echo "Invalid request.";
        echo '<p>Something went wrong. Please try again.</p>';
        echo '<form action="index.html" method="get">';
        echo '<button type="submit">Try Again</button>';
        echo '</form>';
}
?>

</body>
</html>
