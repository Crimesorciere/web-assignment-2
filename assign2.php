<?php
// Dynamic variables for configuration
$host = 'localhost';               // Database host
$db = 'college';                   // Database name
$user = 'root';                    // Database username
$pass = '';                        // Database password
$studentTable = 'students';        // Table name for storing students' info
$subjectTable = 'subjects';        // Table name for subjects

// Field configuration (label => database_column)
$formFields = [
    'First Name' => 'first_name',
    'Last Name' => 'last_name',
    'Email' => 'email',
    'Phone' => 'phone',
    'Gender' => 'gender'
];
$genderOptions = ['Male', 'Female'];   // Options for gender dropdown

// Connect to MySQL database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve available subjects dynamically from the database
$subjects = [];
$subjectQuery = "SELECT name FROM $subjectTable";
$result = $conn->query($subjectQuery);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row['name'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentData = [];
    foreach ($formFields as $label => $column) {
        $studentData[$column] = $_POST[$column];
    }
    $studentData['subject'] = $_POST['subject'];

    $stmt = $conn->prepare("INSERT INTO $studentTable (first_name, last_name, email, phone, gender, subject) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssss",
        $studentData['first_name'],
        $studentData['last_name'],
        $studentData['email'],
        $studentData['phone'],
        $studentData['gender'],
        $studentData['subject']
    );

    if ($stmt->execute()) {
        echo "<p>Registration successful!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>
    <style>
        body { font-family: Arial, sans-serif; }
        form { max-width: 400px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input[type="text"], input[type="email"], input[type="tel"], select { width: 100%; padding: 8px; }
        input[type="submit"] { margin-top: 20px; padding: 10px; width: 100%; }
    </style>
</head>
<body>
    <h2>College Student Registration</h2>
    <form method="POST" action="">

        <!-- Dynamic Form Fields -->
        <?php foreach ($formFields as $label => $name): ?>
            <label for="<?php echo $name; ?>"><?php echo $label; ?></label>
            <?php if ($name == 'gender'): ?>
                <select id="<?php echo $name; ?>" name="<?php echo $name; ?>" required>
                    <?php foreach ($genderOptions as $option): ?>
                        <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="<?php echo $name == 'email' ? 'email' : 'text'; ?>" id="<?php echo $name; ?>" name="<?php echo $name; ?>" required>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Dynamic Subjects Dropdown -->
        <label for="subject">Preferred Subject</label>
        <select id="subject" name="subject" required>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?php echo $subject; ?>"><?php echo $subject; ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Register">
    </form>
</body>
</html>
