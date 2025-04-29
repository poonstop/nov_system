document.addEventListener('DOMContentLoaded', function() {
    // Get all the date cells in the table
    const dateCells = document.querySelectorAll('#recordsTable tbody tr td:nth-child(7)');
    
    // Get current date and time in Philippine timezone
    const now = new Date();
    // Format for Philippine time (UTC+8)
    const options = { 
        timeZone: 'Asia/Manila',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    };
    
    const philTime = new Intl.DateTimeFormat('en-US', options).format(now);
    
    // Update each cell
    dateCells.forEach(cell => {
        cell.innerHTML = `${philTime} <span class="badge bg-success">Today</span>`;
    });
});