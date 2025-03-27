<?php
include __DIR__ . '/../connection.php';
include '../templates/header.php';

// Helper function to capitalize first letter of each word
function capitalizeWords($string) {
    return ucwords(strtolower($string));
}
// Previous query remains the same
$query = "
    SELECT 
        id,
        name, 
        address, 
        IFNULL(owner_representative, 'Not specified') AS owner_rep,
        GROUP_CONCAT(violations SEPARATOR ', ') AS all_violations,
        COUNT(violations) AS num_violations,
        MAX(created_at) AS latest_date,
        nov_files 
    FROM establishments 
    GROUP BY name, address, owner_representative
    ORDER BY latest_date DESC
";
$result = $conn->query($query);
?>

<style>
    body {
        background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
    }
    .container {
        margin-top: 20px;
    }
    .table-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .search-bar {
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .search-bar:focus {
        box-shadow: 0 0 10px rgba(16, 52, 108, 0.3);
        border-color: #10346C;
    }
    
    /* Responsive Table Styles */
    @media (max-width: 768px) {
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
        }
        
        .table td, .table th {
            white-space: nowrap;
        }
    }
    
    /* Search Result Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    #recordsTable tbody tr {
        transition: all 0.3s ease;
    }
    
    #recordsTable tbody tr.search-match {
        animation: fadeIn 0.5s ease;
        background-color: rgba(16, 52, 108, 0.05);
    }
    
    #recordsTable tbody tr:hover {
        background-color: rgba(16, 52, 108, 0.1);
        transform: scale(1.01);
    }
    
    /* Empty Search Result Styling */
    .no-results {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
</style>

<div class="container">
    <h4 class="mb-3 text-center">Existing Records</h4>

    <!-- Search Bar with Icon -->
    <div class="input-group mb-3">
        <span class="input-group-text" style="background-color: #10346C; color: white;">
            <i class="fas fa-search"></i>
        </span>
        <input 
            type="text" 
            id="searchInput" 
            class="form-control search-bar" 
            placeholder="Search establishment, address, or violations"
            onkeyup="filterTable()"
        >
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="recordsTable">
                <!-- Previous table structure remains the same -->
                <thead>
                    <tr>
                        <th onclick="sortTable(0)" style="cursor: pointer;">Establishment</th>
                        <th onclick="sortTable(1)" style="cursor: pointer;">Address</th>
                        <th onclick="sortTable(2)" style="cursor: pointer;">Owner/Representative</th>  
                        <th onclick="sortTable(3)" style="cursor: pointer;">Violations</th>
                        <th>NOV File</th>
                        <th onclick="sortTable(4)" style="cursor: pointer;">No. of Violations</th>
                        <th onclick="sortTable(5)" style="cursor: pointer;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= capitalizeWords(htmlspecialchars($row['name'])) ?></td>
                        <td><?= capitalizeWords(htmlspecialchars($row['address'])) ?></td>
                        <td><?= capitalizeWords(htmlspecialchars($row['owner_rep'])) ?></td>
                        <td><?= capitalizeWords(htmlspecialchars($row['all_violations'])) ?></td>
                        <td>
                        <?php 
                              // Split violations by comma and capitalize each one
                    $violations = array_map('trim', explode(',', $row['all_violations']));
                    $capitalizedViolations = array_map('capitalizeWords', $violations);
                    echo htmlspecialchars(implode(', ', $capitalizedViolations));
                             ?>
        </td>
        <td>
                            <?php if (!empty($row['nov_files'])): ?>
                                <a href="nov_files/<?= $row['nov_files'] ?>" class="file-link" target="_blank">View NOV</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= $row['num_violations'] ?></td>
                        <td><?= date('M d, Y', strtotime($row['latest_date'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="noResults" class="no-results" style="display: none;">
                <p>No results found. Try a different search term.</p>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

<script>
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const table = document.getElementById('recordsTable');
        const rows = table.getElementsByTagName('tr');
        const noResultsDiv = document.getElementById('noResults');
        let visibleRowCount = 0;

        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;

            for (let j = 0; j < cells.length; j++) {
                if (j !== 4 && cells[j] && cells[j].innerText.toLowerCase().includes(searchInput)) {
                    match = true;
                    break;
                }
            }

            if (match) {
                rows[i].style.display = '';
                rows[i].classList.add('search-match');
                visibleRowCount++;
            } else {
                rows[i].style.display = 'none';
                rows[i].classList.remove('search-match');
            }
        }

        // Show/hide no results message
        noResultsDiv.style.display = visibleRowCount === 0 ? 'block' : 'none';
    }

    // Previous sortTable function remains the same
    function sortTable(columnIndex) {
        const table = document.getElementById('recordsTable');
        const rows = Array.from(table.rows).slice(1);
        const isAscending = table.getAttribute('data-sort') !== 'asc';
        const multiplier = isAscending ? 1 : -1;

        // Previous sorting logic remains the same
        if (columnIndex === 5) {
            rows.sort((a, b) => {
                const aNum = parseInt(a.cells[columnIndex].innerText);
                const bNum = parseInt(b.cells[columnIndex].innerText);
                return (aNum - bNum) * multiplier;
            });
        } 
        else if (columnIndex === 6) {
            rows.sort((a, b) => {
                const aDate = new Date(a.cells[columnIndex].innerText);
                const bDate = new Date(b.cells[columnIndex].innerText);
                return (aDate - bDate) * multiplier;
            });
        } 
        else {
            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].innerText.toLowerCase();
                const bText = b.cells[columnIndex].innerText.toLowerCase();
                return aText.localeCompare(bText) * multiplier;
            });
        }

        rows.forEach(row => table.appendChild(row));
        table.setAttribute('data-sort', isAscending ? 'asc' : 'desc');
    }
</script>