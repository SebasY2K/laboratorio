<?php
require_once "db.php";
header("Content-Type: application/json; charset=utf-8");

$action = $_POST["action"] ?? $_GET["action"] ?? "";

function all_customers($pdo) {
    $stmt = $pdo->query("SELECT * FROM customer ORDER BY customer_id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function all_manufacturers($pdo, $filter = "") {
    if ($filter !== "") {
        $stmt = $pdo->prepare("SELECT * FROM manufacturer WHERE LOWER(name) LIKE :filter ORDER BY manufacturer_id DESC");
        $stmt->execute(["filter" => "%" . strtolower($filter) . "%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM manufacturer ORDER BY manufacturer_id DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

switch ($action) {

    case "list_customers":
        echo json_encode(["success" => true, "customers" => all_customers($pdo)]);
        break;

    case "list_manufacturers":
        echo json_encode(["success" => true, "manufacturers" => all_manufacturers($pdo)]);
        break;

    case "save_customer":
        $id = $_POST["customer_id"] ?? "";
        $firstname = trim($_POST["firstname"] ?? "");
        $lastname = trim($_POST["lastname"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $balance = $_POST["balance"] ?? 0;

        if ($firstname === "" || $lastname === "" || $email === "") {
            echo json_encode(["success" => false, "message" => "Nombre, apellido y email son obligatorios."]);
            break;
        }

        if ($id === "") {
            $stmt = $pdo->prepare("INSERT INTO customer (firstname, lastname, email, balance) VALUES (?, ?, ?, ?)");
            $stmt->execute([$firstname, $lastname, $email, $balance]);
        } else {
            $stmt = $pdo->prepare("UPDATE customer SET firstname=?, lastname=?, email=?, balance=? WHERE customer_id=?");
            $stmt->execute([$firstname, $lastname, $email, $balance, $id]);
        }

        echo json_encode(["success" => true, "customers" => all_customers($pdo)]);
        break;

    case "delete_customer":
        $id = $_POST["customer_id"] ?? "";
        $stmt = $pdo->prepare("DELETE FROM customer WHERE customer_id=?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true, "customers" => all_customers($pdo)]);
        break;

    case "save_manufacturer":
        $id = $_POST["manufacturer_id"] ?? "";
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $city = trim($_POST["city"] ?? "");

        if ($name === "") {
            echo json_encode(["success" => false, "message" => "El nombre de la empresa es obligatorio."]);
            break;
        }

        if ($id === "") {
            $stmt = $pdo->prepare("INSERT INTO manufacturer (name, email, phone, city) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $city]);
        } else {
            $stmt = $pdo->prepare("UPDATE manufacturer SET name=?, email=?, phone=?, city=? WHERE manufacturer_id=?");
            $stmt->execute([$name, $email, $phone, $city, $id]);
        }

        echo json_encode(["success" => true, "manufacturers" => all_manufacturers($pdo)]);
        break;

    case "delete_manufacturer":
        $id = $_POST["manufacturer_id"] ?? "";
        $stmt = $pdo->prepare("DELETE FROM manufacturer WHERE manufacturer_id=?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true, "manufacturers" => all_manufacturers($pdo)]);
        break;

    case "search_manufacturer":
        $q = $_GET["q"] ?? "";
        echo json_encode(["success" => true, "manufacturers" => all_manufacturers($pdo, $q)]);
        break;

    default:
        echo json_encode(["success" => false, "message" => "Acción no reconocida."]);
}
