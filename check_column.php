<?php
$host = '127.0.0.1';
$db = 'sp';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query('DESCRIBE speed_bumps');
while($row = $result->fetch_assoc()) {
    if($row['Field'] == 'confidence_level') {
        echo 'Field: ' . $row['Field'] . PHP_EOL;
        echo 'Type: ' . $row['Type'] . PHP_EOL;
        echo 'Null: ' . $row['Null'] . PHP_EOL;
        echo 'Default: ' . $row['Default'] . PHP_EOL;
        break;
    }
}
$conn->close();
?>