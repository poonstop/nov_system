<?php
include '../config/db.php';
include '../templates/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $establishment_name = $_POST['establishment_name'];
    $business_address = $_POST['business_address'];
    $nature_of_business = $_POST['nature_of_business'];
    $non_conforming_products = $_POST['non_conforming_products'];
    $violations = isset($_POST['violations']) ? implode(", ", $_POST['violations']) : '';
    $remarks = $_POST['remarks'];

    $sql = "INSERT INTO nov_records (establishment_name, business_address, nature_of_business, non_conforming_products, violations, remarks) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $establishment_name, $business_address, $nature_of_business, $non_conforming_products, $violations, $remarks);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Record saved successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<div class="container mt-5">
    <h1 class="text-center">Notice of Violation Form</h1>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="establishment_name" class="form-label">Name of Establishment</label>
            <input type="text" class="form-control" id="establishment_name" name="establishment_name" required>
        </div>
        <div class="mb-3">
            <label for="business_address" class="form-label">Business Address</label>
            <input type="text" class="form-control" id="business_address" name="business_address" required>
        </div>
        <div class="mb-3">
            <label for="nature_of_business" class="form-label">Nature of Business</label>
            <select class="form-select" id="nature_of_business" name="nature_of_business" required>
                <option value="Retailer/Wholesaler">Retailer/Wholesaler</option>
                <option value="Hardware">Hardware</option>
                <option value="Supermarket/Grocery/Convenience Store">Supermarket/Grocery/Convenience Store</option>
                <option value="Service and Repair">Service and Repair</option>
                <option value="Others">Others</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="non_conforming_products" class="form-label">Non-Conforming Products/Goods/Services</label>
            <textarea class="form-control" id="non_conforming_products" name="non_conforming_products" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Violations</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="violations[]" value="No PS/ICC Mark" id="violation1">
                <label class="form-check-label" for="violation1">No PS/ICC Mark</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="violations[]" value="Invalid/Expired Accreditation" id="violation2">
                <label class="form-check-label" for="violation2">Invalid/Expired Accreditation</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="violations[]" value="Improper Labeling" id="violation3">
                <label class="form-check-label" for="violation3">Improper Labeling</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="violations[]" value="Price Tag Violations" id="violation4">
                <label class="form-check-label" for="violation4">Price Tag Violations</label>
            </div>
        </div>
        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php include '../templates/footer.php'; ?>