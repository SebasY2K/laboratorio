<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Laboratorio CRUD - Tecnológico de Antioquia</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
    .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #0056b3; color: white; }
    .btn { padding: 6px 12px; margin: 2px; cursor: pointer; }
    label { display: block; margin-top: 8px; font-weight: bold; }
    input[type=text], input[type=number] { padding: 5px; width: 250px; }
    .msg { color: #b30000; font-weight: bold; }
</style>
</head>
<body>

<h2>Gestión de Base de Datos SAMPLE (CRUD + Ajax)</h2>

<!-- SECCIÓN CUSTOMER -->
<div class="card">
    <h3>1. Gestión de Clientes (CUSTOMER)</h3>
    <p class="msg" id="customerMsg"></p>
    <form id="formCustomer">
        <input type="hidden" id="customer_id" name="customer_id" value="">
        <label>Nombre:</label>
        <input type="text" id="firstname" name="firstname" required>

        <label>Apellido:</label>
        <input type="text" id="lastname" name="lastname" required>

        <label>Email:</label>
        <input type="text" id="email" name="email" required>

        <label>Saldo ($):</label>
        <input type="number" step="0.01" id="balance" name="balance" value="0">

        <br><br>
        <button type="submit" class="btn">Guardar / Actualizar</button>
        <button type="button" class="btn" onclick="resetCustomerForm()">Cancelar edición</button>
    </form>

    <table id="tableCustomer">
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Saldo</th><th>Acciones</th></tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- SECCIÓN MANUFACTURER -->
<div class="card">
    <h3>2. Búsqueda y Gestión de Fabricantes (MANUFACTURER)</h3>

    <label>Buscar Fabricante por Nombre (Ajax en tiempo real):</label>
    <input type="text" id="filtro" autocomplete="off" placeholder="Escribe para buscar...">

    <hr>

    <p class="msg" id="manufacturerMsg"></p>
    <form id="formManufacturer">
        <input type="hidden" id="manufacturer_id" name="manufacturer_id" value="">
        <label>Empresa:</label>
        <input type="text" id="name" name="name" required>

        <label>Email:</label>
        <input type="text" id="m_email" name="email">

        <label>Teléfono:</label>
        <input type="text" id="phone" name="phone">

        <label>Ciudad:</label>
        <input type="text" id="city" name="city">

        <br><br>
        <button type="submit" class="btn">Guardar / Actualizar</button>
        <button type="button" class="btn" onclick="resetManufacturerForm()">Cancelar edición</button>
    </form>

    <table id="tableManufacturer">
        <thead>
            <tr><th>ID</th><th>Empresa</th><th>Email</th><th>Teléfono</th><th>Ciudad</th><th>Acciones</th></tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
// ---------- Utilidades ----------
async function postData(action, data) {
    const formData = new FormData();
    formData.append("action", action);
    for (const key in data) formData.append(key, data[key]);
    const res = await fetch("actions.php", { method: "POST", body: formData });
    return res.json();
}

async function getData(action, params = "") {
    const res = await fetch(`actions.php?action=${action}${params}`);
    return res.json();
}

// ---------- CUSTOMER ----------
function renderCustomers(list) {
    const tbody = document.querySelector("#tableCustomer tbody");
    tbody.innerHTML = "";
    list.forEach(c => {
        tbody.innerHTML += `
            <tr>
                <td>${c.customer_id}</td>
                <td>${c.firstname} ${c.lastname}</td>
                <td>${c.email}</td>
                <td>$${c.balance}</td>
                <td>
                    <button class="btn" onclick='editCustomer(${JSON.stringify(c)})'>Editar</button>
                    <button class="btn" onclick="deleteCustomer(${c.customer_id})">Eliminar</button>
                </td>
            </tr>`;
    });
}

function editCustomer(c) {
    document.getElementById("customer_id").value = c.customer_id;
    document.getElementById("firstname").value = c.firstname;
    document.getElementById("lastname").value = c.lastname;
    document.getElementById("email").value = c.email;
    document.getElementById("balance").value = c.balance;
}

function resetCustomerForm() {
    document.getElementById("formCustomer").reset();
    document.getElementById("customer_id").value = "";
}

async function deleteCustomer(id) {
    if (!confirm("¿Eliminar este cliente?")) return;
    const result = await postData("delete_customer", { customer_id: id });
    renderCustomers(result.customers);
}

document.getElementById("formCustomer").addEventListener("submit", async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    const result = await postData("save_customer", data);
    document.getElementById("customerMsg").innerText = result.success ? "" : result.message;
    if (result.success) {
        renderCustomers(result.customers);
        resetCustomerForm();
    }
});

// ---------- MANUFACTURER ----------
function renderManufacturers(list) {
    const tbody = document.querySelector("#tableManufacturer tbody");
    tbody.innerHTML = "";
    list.forEach(m => {
        tbody.innerHTML += `
            <tr>
                <td>${m.manufacturer_id}</td>
                <td>${m.name}</td>
                <td>${m.email ?? ""}</td>
                <td>${m.phone ?? ""}</td>
                <td>${m.city ?? ""}</td>
                <td>
                    <button class="btn" onclick='editManufacturer(${JSON.stringify(m)})'>Editar</button>
                    <button class="btn" onclick="deleteManufacturer(${m.manufacturer_id})">Eliminar</button>
                </td>
            </tr>`;
    });
}

function editManufacturer(m) {
    document.getElementById("manufacturer_id").value = m.manufacturer_id;
    document.getElementById("name").value = m.name;
    document.getElementById("m_email").value = m.email ?? "";
    document.getElementById("phone").value = m.phone ?? "";
    document.getElementById("city").value = m.city ?? "";
}

function resetManufacturerForm() {
    document.getElementById("formManufacturer").reset();
    document.getElementById("manufacturer_id").value = "";
}

async function deleteManufacturer(id) {
    if (!confirm("¿Eliminar este fabricante?")) return;
    const result = await postData("delete_manufacturer", { manufacturer_id: id });
    renderManufacturers(result.manufacturers);
}

document.getElementById("formManufacturer").addEventListener("submit", async (e) => {
    e.preventDefault();
    const raw = Object.fromEntries(new FormData(e.target));
    const data = { manufacturer_id: raw.manufacturer_id, name: raw.name, email: raw.email, phone: raw.phone, city: raw.city };
    const result = await postData("save_manufacturer", data);
    document.getElementById("manufacturerMsg").innerText = result.success ? "" : result.message;
    if (result.success) {
        renderManufacturers(result.manufacturers);
        resetManufacturerForm();
    }
});

document.getElementById("filtro").addEventListener("keyup", async (e) => {
    const result = await getData("search_manufacturer", "&q=" + encodeURIComponent(e.target.value));
    renderManufacturers(result.manufacturers);
});

// ---------- Carga inicial ----------
(async function loadInitial() {
    const customersResult = await getData("list_customers");
    renderCustomers(customersResult.customers);

    const manufacturersResult = await getData("list_manufacturers");
    renderManufacturers(manufacturersResult.manufacturers);
})();
</script>

</body>
</html>
