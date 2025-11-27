<?php

// ======================================================
// CONFIG — USE RENDER WRITABLE DIRECTORY
// ======================================================
$dataDir = "/var/www/html/storage/";
$dataFile = $dataDir . "data.json";
$uploadDir = $dataDir . "uploads/";

// Create storage folder if missing
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
}

// Create uploads folder if missing
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Create data.json if missing
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}
// ======================================================
// HELPERS
// ======================================================
function readData($file) {
    return json_decode(file_get_contents($file), true);
}

function saveData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER["REQUEST_METHOD"];
$action = $_GET["action"] ?? "";


// ======================================================
// MODERN UI — HOMEPAGE
// ======================================================
if ($method === "GET" && $action === "") {
?>
<!DOCTYPE html>
<html>
<head>
  <title>PHP API Dashboard</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- FontAwesome Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
      body { background: #f5f7fb; }
      .card {
          border: none;
          border-radius: 14px;
          box-shadow: 0 4px 10px rgba(0,0,0,0.08);
      }
      .header-title {
          font-weight: 700;
          color: #3b4cca;
          text-align: center;
          margin-bottom: 30px;
          margin-top: 20px;
      }
      .btn-main {
          background: #3b6bff;
          color: #fff;
          font-weight: 600;
      }
  </style>
</head>
<body>

<div class="container">
    <h1 class="header-title">PHP JSON API Dashboard</h1>

    <div class="row g-4">

        <!-- CREATE -->
        <div class="col-lg-6">
            <div class="card p-4">
                <h4><i class="fa fa-plus-circle text-primary"></i> Create Data</h4>
                <form method="POST" action="index.php?action=create" class="mt-3">

                    <label class="form-label">ID</label>
                    <input type="text" class="form-control" name="id" required>

                    <label class="form-label mt-3">Name</label>
                    <input type="text" class="form-control" name="name" required>

                    <label class="form-label mt-3">Phone</label>
                    <input type="text" class="form-control" name="phone" required>

                    <button class="btn btn-main mt-4 w-100">Create</button>
                </form>
            </div>
        </div>

        <!-- UPDATE -->
        <div class="col-lg-6">
            <div class="card p-4">
                <h4><i class="fa fa-edit text-warning"></i> Update Data</h4>
                <form method="POST" action="index.php?action=update" class="mt-3">

                    <label class="form-label">ID</label>
                    <input type="text" class="form-control" name="id" required>

                    <label class="form-label mt-3">New Name</label>
                    <input type="text" class="form-control" name="name">

                    <label class="form-label mt-3">New Phone</label>
                    <input type="text" class="form-control" name="phone">

                    <button class="btn btn-warning mt-4 w-100 text-white fw-bold">Update</button>
                </form>
            </div>
        </div>

        <!-- DELETE -->
        <div class="col-lg-6">
            <div class="card p-4">
                <h4><i class="fa fa-trash text-danger"></i> Delete Data</h4>
                <form method="POST" action="index.php?action=delete" class="mt-3">

                    <label class="form-label">ID</label>
                    <input type="text" class="form-control" name="id" required>

                    <button class="btn btn-danger mt-4 w-100 fw-bold">Delete</button>
                </form>
            </div>
        </div>

        <!-- UPLOAD -->
        <div class="col-lg-6">
            <div class="card p-4">
                <h4><i class="fa fa-upload text-success"></i> Upload Image</h4>
                <form method="POST" action="index.php?action=upload-image" enctype="multipart/form-data" class="mt-3">

                    <label class="form-label">Choose Image</label>
                    <input type="file" class="form-control" name="image" required>

                    <button class="btn btn-success mt-4 w-100 fw-bold">Upload</button>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html>
<?php
exit;
}

// ======================================================
// API — JSON LOGIC
// ======================================================

header("Content-Type: application/json");

// CREATE
if ($method === "POST" && $action === "create") {
    $id    = $_POST["id"] ?? "";
    $name  = $_POST["name"] ?? "";
    $phone = $_POST["phone"] ?? "";

    if (!$id) { echo json_encode(["error"=>"ID required"]); exit; }
    if (!$name) { echo json_encode(["error"=>"Name required"]); exit; }
    if (strlen($phone) < 10) { echo json_encode(["error"=>"Phone too short"]); exit; }

    $data = readData($dataFile);

    foreach ($data as $d) {
        if ($d["id"] === $id) {
            echo json_encode(["error"=>"ID already exists"]);
            exit;
        }
    }

    $data[] = ["id"=>$id, "name"=>$name, "phone"=>$phone];
    saveData($dataFile, $data);

    echo json_encode(["success"=>true]);
    exit;
}

// UPDATE
if ($method === "POST" && $action === "update") {
    $id = $_POST["id"] ?? "";

    if (!$id) { echo json_encode(["error"=>"ID required"]); exit; }

    $data = readData($dataFile);
    $found = false;

    foreach ($data as &$d) {
        if ($d["id"] === $id) {
            $found = true;

            if ($_POST["name"] !== "") {
                $d["name"] = $_POST["name"];
            }
            if ($_POST["phone"] !== "" && strlen($_POST["phone"]) >= 10) {
                $d["phone"] = $_POST["phone"];
            }
        }
    }

    if (!$found) {
        echo json_encode(["error"=>"ID not found"]);
        exit;
    }

    saveData($dataFile, $data);
    echo json_encode(["success"=>true]);
    exit;
}

// DELETE
if ($method === "POST" && $action === "delete") {
    $id = $_POST["id"] ?? "";

    if (!$id) { echo json_encode(["error"=>"ID required"]); exit; }

    $data = readData($dataFile);
    $new = [];
    $deleted = false;

    foreach ($data as $d) {
        if ($d["id"] === $id) {
            $deleted = true;
            continue;
        }
        $new[] = $d;
    }

    if (!$deleted) {
        echo json_encode(["error"=>"ID not found"]);
        exit;
    }

    saveData($dataFile, $new);
    echo json_encode(["success"=>true]);
    exit;
}

// UPLOAD IMAGE
if ($method === "POST" && $action === "upload-image") {

    if (!isset($_FILES["image"])) {
        echo json_encode(["error"=>"Image required"]);
        exit;
    }

    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $filePath = $uploadDir . $fileName;

    move_uploaded_file($_FILES["image"]["tmp_name"], $filePath);

    echo json_encode(["success"=>true, "file"=>$filePath]);
    exit;
}

echo json_encode(["error"=>"Invalid route"]);
?>
