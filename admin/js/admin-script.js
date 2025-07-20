/**
 * Admin Dashboard JavaScript
 * 
 * This file contains the JavaScript functionality for the admin dashboard.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const adminContainer = document.querySelector('.admin-container');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            adminContainer.classList.toggle('sidebar-collapsed');
            
            // Store preference in localStorage
            if (adminContainer.classList.contains('sidebar-collapsed')) {
                localStorage.setItem('sidebar-collapsed', 'true');
            } else {
                localStorage.setItem('sidebar-collapsed', 'false');
            }
        });
        
        // Check localStorage for sidebar preference
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            adminContainer.classList.add('sidebar-collapsed');
        }
    }
    
    // Close notification functionality
    const closeNotifications = document.querySelectorAll('.close-notification');
    closeNotifications.forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });
    
    // Auto-hide notifications after 5 seconds
    setTimeout(function() {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            notification.style.display = 'none';
        });
    }, 5000);
    
    // Modal functionality
    const modal = document.getElementById('statusModal');
    if (modal) {
        const closeBtn = document.querySelector('.close-modal');
        
        // Close modal when clicking the X button
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        
        // Close modal when clicking outside the modal content
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Password confirmation validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (newPasswordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== newPasswordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        newPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value !== '' && confirmPasswordInput.value !== this.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });
    }
    
    // Print functionality
    const printBtn = document.getElementById('print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Export to CSV functionality
    const exportBtn = document.getElementById('export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const table = document.querySelector('.data-table');
            if (!table) return;
            
            // Get table headers
            const headers = [];
            const headerCells = table.querySelectorAll('thead th');
            headerCells.forEach(cell => {
                headers.push(cell.textContent.trim());
            });
            
            // Get table data
            const rows = [];
            const dataCells = table.querySelectorAll('tbody tr');
            dataCells.forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => {
                    // Get text content without status badge or action buttons
                    let content = cell.textContent.trim();
                    if (cell.querySelector('.status-badge')) {
                        content = cell.querySelector('.status-badge').textContent.trim();
                    }
                    if (cell.classList.contains('actions-cell')) {
                        content = ''; // Skip action buttons
                    }
                    rowData.push(content);
                });
                rows.push(rowData);
            });
            
            // Create CSV content
            let csvContent = headers.join(',') + '\n';
            rows.forEach(row => {
                csvContent += row.join(',') + '\n';
            });
            
            // Create download link
            const encodedUri = encodeURI('data:text/csv;charset=utf-8,' + csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'export.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
    
    // Responsive table handling
    const tables = document.querySelectorAll('.data-table');
    tables.forEach(table => {
        const headerCells = table.querySelectorAll('thead th');
        const dataCells = table.querySelectorAll('tbody td');
        
        // Add data-label attribute to cells for responsive display
        dataCells.forEach((cell, index) => {
            const headerIndex = index % headerCells.length;
            cell.setAttribute('data-label', headerCells[headerIndex].textContent.trim());
        });
    });
});
