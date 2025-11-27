<?php

// Path to JSON "database"
$dataFile = "data.json";

// Ensure JSON file exists
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

// Helpers
function readData($file) {
    return json_decode(file_get_contents($file), true);
}
function saveData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER["REQUEST_METHOD"];
$action = $_GET["action"] ?? "";

// ======================================================
// 0️⃣ DEFAULT PAGE – SHOW HTML FORMS
// ======================================================
if ($method === "GET" && $action === "") {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>PHP JSON API Demo</title>
    </head>
    <body>
        <h1>PHP JSON API (Create / Update / Delete / Upload)</h1>

        <h2>Create Data</h2>
        <form method="POST" action="index.php?action=create">
            ID: <input name="id"><br><br>
            Name: <input name="name"><br><br>
            Phone: <input name="phone"><br><br>
            <button type="submit">Create</button>
        </form>

        <hr>

        <h2>Update Data</h2>
        <form method="POST" action="index.php?action=update">
            ID (existing): <input name="id"><br><br>
            New Name: <input name="name"><br><br>
            New Phone: <input name="phone"><br><br>
            <button type="submit">Update</button>
        </form>

        <hr>

        <h2>Delete Data</h2>
        <form method="POST" action="index.php?action=delete">
            ID: <input name="id"><br><br>
            <button type="submit">Delete</button>
        </form>

        <hr>

        <h2>Upload Image</h2>
        <form method="POST" action="index.php?action=upload-image" enctype="multipart/form-data">
            <input type="file" name="image"><br><br>
            <button type="submit">Upload Image</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// From here on, everything is JSON API
header("Content-Type: application/json");

// ======================================================
// 1️⃣ CREATE (POST)
// ======================================================
if ($method === "POST" && $action === "create") {
    $id    = $_POST["id"]   ?? "";
    $name  = $_POST["name"] ?? "";
    $phone = $_POST["phone"] ?? "";

    if (!$id) {
        echo json_encode(["error" => "ID is required"]);
        exit;
    }
    if (!$name || trim($name) === "") {
        echo json_encode(["error" => "Name cannot be empty"]);
        exit;
    }
    if (strlen($phone) < 10) {
        echo json_encode(["error" => "Phone must be at least 10 digits"]);
        exit;
    }

    $data = readData($dataFile);
    foreach ($data as $item) {
        if ($item["id"] == $id) {
            echo json_encode(["error" => "ID already exists"]);
            exit;
        }
    }

    $data[] = ["id" => $id, "name" => $name, "phone" => $phone];
    saveData($dataFile, $data);

    echo json_encode(["success" => true, "message" => "Data saved"]);
    exit;
}

// ======================================================
// 2️⃣ UPDATE (POST)
// ======================================================
if ($method === "POST" && $action === "update") {
    $id    = $_POST["id"]   ?? "";
    $name  = $_POST["name"] ?? null;
    $phone = $_POST["phone"] ?? null;

    if (!$id) {
        echo json_encode(["error" => "ID required"]);
        exit;
    }

    $data = readData($dataFile);
    $found = false;

    foreach ($data as &$item) {
        if ($item["id"] == $id) {
            $found = true;

            if ($name !== null) {
                if (trim($name) === "") {
                    echo json_encode(["error" => "Name cannot be empty"]);
                    exit;
                }
                $item["name"] = $name;
            }

            if ($phone !== null) {
                if (strlen($phone) < 10) {
                    echo json_encode(["error" => "Phone must be at least 10 digits"]);
                    exit;
                }
                $item["phone"] = $phone;
            }
        }
    }

    if (!$found) {
        echo json_encode(["error" => "ID does not exist"]);
        exit;
    }

    saveData($dataFile, $data);
    echo json_encode(["success" => true, "message" => "Updated successfully"]);
    exit;
}

// ======================================================
// 3️⃣ DELETE (POST)
// ======================================================
if ($method === "POST" && $action === "delete") {
    $id = $_POST["id"] ?? "";

    if (!$id) {
        echo json_encode(["error" => "ID required"]);
        exit;
    }

    $data = readData($dataFile);
    $newData = [];
    $deleted = false;

    foreach ($data as $item) {
        if ($item["id"] == $id) {
            $deleted = true;
            continue;
        }
        $newData[] = $item;
    }

    if (!$deleted) {
        echo json_encode(["error" => "ID does not exist"]);
        exit;
    }

    saveData($dataFile, $newData);
    echo json_encode(["success" => true, "message" => "Deleted successfully"]);
    exit;
}

// ======================================================
// 4️⃣ IMAGE UPLOAD (POST)
// ======================================================
if ($method === "POST" && $action === "upload-image") {

    if (!isset($_FILES["image"])) {
        echo json_encode(["error" => "Image is required"]);
        exit;
    }

    if (!file_exists("uploads")) {
        mkdir("uploads");
    }

    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $target   = "uploads/" . $fileName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
        echo json_encode([
            "success"  => true,
            "message"  => "Image uploaded successfully",
            "file_url" => $target
        ]);
    } else {
        echo json_encode(["error" => "Failed to upload"]);
    }
    exit;
}

// If nothing matched:
echo json_encode(["error" => "Invalid route"]);
?>
