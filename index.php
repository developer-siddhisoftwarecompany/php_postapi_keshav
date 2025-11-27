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
<?php
// ==== HOME PAGE MODERN UI ====
if ($method === "GET" && $action === "") {
?>
<!DOCTYPE html>
<html>
<head>
  <title>PHP API Dashboard</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
      body {
          background: #f5f7fb;
      }
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
      .btn-main:hover {
          background: #2d54cc;
      }
  </style>
</head>
<body>

<div class="container">
    <h1 class="header-title">PHP JSON API Dashboard</h1>

    <div class="row g-4">

        <!-- CREATE CARD -->
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

        <!-- UPDATE CARD -->
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


        <!-- DELETE CARD -->
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

        <!-- UPLOAD IMAGE CARD -->
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
?>


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
