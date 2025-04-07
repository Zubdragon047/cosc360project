document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab links
            tabLinks.forEach(link => {
                link.classList.remove('active');
            });
            
            // Add active class to current tab and content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            // Load content for specific tabs if needed
            if (tabId === 'reports') {
                loadReports('all');
            }
        });
    });
    
    // User search
    const userSearchForm = document.getElementById('user-search-form');
    if (userSearchForm) {
        userSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('user-search').value;
            searchUsers(searchTerm);
        });
    }
    
    // Book search
    const bookSearchForm = document.getElementById('book-search-form');
    if (bookSearchForm) {
        bookSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('book-search').value;
            searchBooks(searchTerm);
        });
    }
    
    // Thread search
    const threadSearchForm = document.getElementById('thread-admin-search-form');
    if (threadSearchForm) {
        threadSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('thread-admin-search').value;
            searchThreads(searchTerm);
        });
    }
    
    // Report filters
    const reportFilters = document.querySelectorAll('.report-filter');
    if (reportFilters) {
        reportFilters.forEach(filter => {
            filter.addEventListener('click', function() {
                const status = this.getAttribute('data-status');
                
                // Remove active class from all filters
                reportFilters.forEach(f => f.classList.remove('active'));
                
                // Add active class to current filter
                this.classList.add('active');
                
                // Load reports with the selected status
                loadReports(status);
            });
        });
    }
    
    // Initial load of reports if reports tab is active
    if (document.querySelector('#reports.tab-content.active')) {
        loadReports('all');
    }
    
    // Close modal when clicking on the close button or outside the modal
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('report-details-modal');
        if (event.target.classList.contains('close') || event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Handle view report button clicks (using event delegation)
    document.addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('view-report-button')) {
            const reportId = event.target.getAttribute('data-report-id');
            showReportDetails(reportId);
        }
    });
});

// Function to search users
function searchUsers(searchTerm) {
    const resultsContainer = document.getElementById('user-results');
    resultsContainer.innerHTML = '<div class="loading-indicator">Searching users...</div>';
    
    fetch(`admin_handler.php?action=search_users&search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.users.length > 0) {
                    let html = '<div class="user-table-container">';
                    html += '<table class="user-table">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th>Profile</th>';
                    html += '<th>Username</th>';
                    html += '<th>Email</th>';
                    html += '<th>Name</th>';
                    html += '<th>Type</th>';
                    html += '<th>Books</th>';
                    html += '<th>Comments</th>';
                    html += '<th>Actions</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    data.users.forEach(user => {
                        html += user.html;
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    
                    resultsContainer.innerHTML = html;
                } else {
                    resultsContainer.innerHTML = '<p>No users found matching your search.</p>';
                }
            } else {
                resultsContainer.innerHTML = `<p>Error: ${data.message}</p>`;
            }
        })
        .catch(error => {
            resultsContainer.innerHTML = `<p>Error: ${error.message}</p>`;
        });
}

// Function to search books
function searchBooks(searchTerm) {
    const resultsContainer = document.getElementById('book-results');
    resultsContainer.innerHTML = '<div class="loading-indicator">Searching books...</div>';
    
    fetch(`admin_handler.php?action=search_books&search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.books.length > 0) {
                    let html = '<div class="book-table-container">';
                    html += '<table class="book-table">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th>Cover</th>';
                    html += '<th>Title</th>';
                    html += '<th>Category</th>';
                    html += '<th>Status</th>';
                    html += '<th>Owner</th>';
                    html += '<th>Actions</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    data.books.forEach(book => {
                        html += book.html;
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    
                    resultsContainer.innerHTML = html;
                } else {
                    resultsContainer.innerHTML = '<p>No books found matching your search.</p>';
                }
            } else {
                resultsContainer.innerHTML = `<p>Error: ${data.message}</p>`;
            }
        })
        .catch(error => {
            resultsContainer.innerHTML = `<p>Error: ${error.message}</p>`;
        });
}

// Function to search threads
function searchThreads(searchTerm) {
    const resultsContainer = document.getElementById('thread-admin-results');
    resultsContainer.innerHTML = '<div class="loading-indicator">Searching threads...</div>';
    
    fetch(`admin_handler.php?action=search_threads&search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.threads.length > 0) {
                    let html = '<div class="thread-table-container">';
                    html += '<table class="thread-table">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th>Title</th>';
                    html += '<th>Author</th>';
                    html += '<th>Created</th>';
                    html += '<th>Comments</th>';
                    html += '<th>Actions</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    data.threads.forEach(thread => {
                        html += thread.html;
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    
                    resultsContainer.innerHTML = html;
                } else {
                    resultsContainer.innerHTML = '<p>No threads found matching your search.</p>';
                }
            } else {
                resultsContainer.innerHTML = `<p>Error: ${data.message}</p>`;
            }
        })
        .catch(error => {
            resultsContainer.innerHTML = `<p>Error: ${error.message}</p>`;
        });
}

// Function to load reports
function loadReports(status) {
    const resultsContainer = document.getElementById('report-results');
    resultsContainer.innerHTML = '<div class="loading-indicator">Loading reports...</div>';
    
    fetch(`admin_handler.php?action=get_reports&status=${encodeURIComponent(status)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.reports.length > 0) {
                    let html = '<div class="report-table-container">';
                    html += '<table class="report-table">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th>Content</th>';
                    html += '<th>Reporter</th>';
                    html += '<th>Reason</th>';
                    html += '<th>Status</th>';
                    html += '<th>Date</th>';
                    html += '<th>Actions</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    data.reports.forEach(report => {
                        html += report.html;
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    
                    resultsContainer.innerHTML = html;
                    
                    // Set up event listeners for report action buttons
                    setupReportActionListeners();
                } else {
                    resultsContainer.innerHTML = '<p>No reports found.</p>';
                }
            } else {
                resultsContainer.innerHTML = `<p>Error: ${data.message}</p>`;
            }
        })
        .catch(error => {
            resultsContainer.innerHTML = `<p>Error: ${error.message}</p>`;
        });
}

// Function to set up event listeners for report action buttons
function setupReportActionListeners() {
    // View report details
    document.querySelectorAll('.view-report-button').forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.getAttribute('data-report-id');
            showReportDetails(reportId);
        });
    });
}

// Function to show report details in modal
function showReportDetails(reportId) {
    fetch(`admin_handler.php?action=get_report_details&report_id=${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('report-details-modal');
                const content = document.getElementById('report-detail-content');
                
                let html = '<div class="report-details">';
                html += `<p><strong>Content Type:</strong> ${data.report.content_type}</p>`;
                html += `<p><strong>Content ID:</strong> ${data.report.content_id}</p>`;
                html += `<p><strong>Reporter:</strong> ${data.report.reporter_username}</p>`;
                html += `<p><strong>Reason:</strong> ${data.report.reason}</p>`;
                html += `<p><strong>Details:</strong></p>`;
                html += `<div class="report-details-text">${data.report.details || 'No additional details provided.'}</div>`;
                html += `<p><strong>Status:</strong> ${data.report.status}</p>`;
                html += `<p><strong>Date:</strong> ${new Date(data.report.created_at).toLocaleString()}</p>`;
                
                // Add view content link based on type
                let viewLink = '#';
                if (data.report.content_type === 'book') {
                    viewLink = `book_detail.php?id=${data.report.content_id}`;
                } else if (data.report.content_type === 'thread') {
                    viewLink = `thread.php?id=${data.report.content_id}`;
                } else if (data.report.content_type === 'comment') {
                    viewLink = `thread.php?id=${data.report.thread_id}#comment-${data.report.content_id}`;
                }
                
                html += `<p><a href="${viewLink}" target="_blank" class="view-content-link">View Reported Content</a></p>`;
                html += '</div>';
                
                content.innerHTML = html;
                modal.style.display = 'block';
            } else {
                alert('Error loading report details: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error loading report details: ' + error.message);
        });
} 