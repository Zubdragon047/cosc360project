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
                console.log('Tab clicked: Loading reports');
                setTimeout(() => loadReports('all'), 100);
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
    
    // Content search
    const contentSearchForm = document.getElementById('content-search-form');
    const contentSearchInput = document.getElementById('content-search-input');
    
    if (contentSearchForm) {
        contentSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('content-search-input').value;
            searchContent(searchTerm);
        });
    }
    
    if (contentSearchInput) {
        contentSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchContent(this.value);
            }
        });
    }
    
    // Report filters
    const reportFilters = document.querySelectorAll('.report-filter');
    if (reportFilters && reportFilters.length > 0) {
        console.log('Found report filters:', reportFilters.length);
        reportFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                const status = this.getAttribute('data-status');
                console.log('Report filter clicked:', status);
                
                // Remove active class from all filters
                reportFilters.forEach(f => f.classList.remove('active'));
                
                // Add active class to current filter
                this.classList.add('active');
                
                // Load reports with the selected status
                loadReports(status);
            });
        });
    } else {
        console.log('No report filters found');
    }
    
    // Initial load of reports if reports tab is active or if URL has #reports hash
    if (document.querySelector('#reports.tab-content.active') || window.location.hash === '#reports') {
        console.log('Reports tab is active or URL hash is #reports');
        
        // If URL has #reports hash, activate that tab
        if (window.location.hash === '#reports') {
            console.log('URL hash is #reports, activating reports tab');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            tabLinks.forEach(link => {
                link.classList.remove('active');
            });
            document.getElementById('reports').classList.add('active');
            document.querySelector('[data-tab="reports"]').classList.add('active');
        }
        
        // Slight delay to ensure DOM is fully loaded
        setTimeout(() => {
            console.log('Initial load of reports based on URL hash');
            loadReports('all');
        }, 300);
    } else {
        console.log('Reports tab is not active initially');
    }
    
    // Close modal when clicking on the close button or outside the modal
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('report-details-modal');
        if (modal && (event.target.classList.contains('close') || event.target === modal)) {
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
    
    // Force load reports directly as a fallback
    window.addEventListener('load', function() {
        const reportsTab = document.getElementById('reports');
        if (reportsTab && reportsTab.classList.contains('active')) {
            console.log('Window loaded: Force-loading reports as fallback');
            setTimeout(() => loadReports('all'), 500);
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

// Function to search content across all content types
function searchContent(searchTerm) {
    const resultsContainer = document.getElementById('content-search-results');
    const summaryContainer = document.getElementById('content-search-summary');
    
    if (!resultsContainer) {
        console.error('Content search results container not found');
        return;
    }
    
    if (!searchTerm.trim()) {
        resultsContainer.innerHTML = '<p>Please enter a search term to find content.</p>';
        summaryContainer.style.display = 'none';
        return;
    }
    
    resultsContainer.innerHTML = '<div class="loading-indicator">Searching content...</div>';
    summaryContainer.style.display = 'none';
    
    fetch(`admin_handler.php?action=search_content&search=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`Expected JSON response but got ${contentType}`);
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update summary
                if (data.summary) {
                    document.getElementById('total-results').textContent = data.summary.total;
                    document.getElementById('book-results-count').textContent = data.summary.books;
                    document.getElementById('thread-results-count').textContent = data.summary.threads;
                    document.getElementById('comment-results-count').textContent = data.summary.comments;
                    summaryContainer.style.display = 'block';
                }
                
                // Check if we have any results
                if (data.summary && data.summary.total > 0) {
                    // Create results table
                    let html = '<div class="content-search-table-container">';
                    html += '<table class="content-search-table">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th>Type</th>';
                    html += '<th>Title</th>';
                    html += '<th>Content</th>';
                    html += '<th>Author</th>';
                    html += '<th>Date</th>';
                    html += '<th>Actions</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    // Add books
                    if (data.results.books && data.results.books.length > 0) {
                        data.results.books.forEach(book => {
                            html += book.html;
                        });
                    }
                    
                    // Add threads
                    if (data.results.threads && data.results.threads.length > 0) {
                        data.results.threads.forEach(thread => {
                            html += thread.html;
                        });
                    }
                    
                    // Add comments
                    if (data.results.comments && data.results.comments.length > 0) {
                        data.results.comments.forEach(comment => {
                            html += comment.html;
                        });
                    }
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    
                    resultsContainer.innerHTML = html;
                } else {
                    resultsContainer.innerHTML = '<p>No content found matching your search.</p>';
                    summaryContainer.style.display = 'none';
                }
            } else {
                resultsContainer.innerHTML = `<p>Error: ${data.message || 'Unknown error'}</p>`;
                summaryContainer.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error searching content:', error);
            resultsContainer.innerHTML = `<p>Error searching content: ${error.message}</p>`;
            summaryContainer.style.display = 'none';
        });
}

// Function to load reports
function loadReports(status) {
    console.log('loadReports called with status:', status);
    const resultsContainer = document.getElementById('report-results');
    
    if (!resultsContainer) {
        console.error('Report results container not found');
        return;
    }
    
    resultsContainer.innerHTML = '<div class="loading-indicator">Loading reports...</div>';
    
    // Create an AJAX request using XMLHttpRequest
    const xhr = new XMLHttpRequest();
    const timestamp = new Date().getTime();
    const url = `admin_handler.php?action=get_reports&status=${encodeURIComponent(status)}&_=${timestamp}`;
    
    console.log('Requesting reports from URL:', url);
    
    xhr.open('GET', url, true);
    
    xhr.onload = function() {
        console.log('Response received, status:', xhr.status);
        
        if (xhr.status === 200) {
            try {
                console.log('Response text:', xhr.responseText.substring(0, 200) + '...');
                
                const data = JSON.parse(xhr.responseText);
                console.log('Parsed JSON data:', data);
                
                if (data.success) {
                    if (data.reports && data.reports.length > 0) {
                        console.log('Found reports:', data.reports.length);
                        
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
                        const statusDisplay = status === 'all' ? 'any status' : `status "${status}"`;
                        resultsContainer.innerHTML = `<p>No reports found with ${statusDisplay}.</p>`;
                        console.log('No reports found with status:', status);
                    }
                } else {
                    resultsContainer.innerHTML = `<p>Error: ${data.message || 'Unknown error'}</p>`;
                    console.error('API returned error:', data.message);
                }
            } catch (error) {
                console.error('Error parsing JSON response:', error);
                console.error('Raw response:', xhr.responseText);
                resultsContainer.innerHTML = `<p>Error parsing server response. See console for details.</p>`;
                resultsContainer.innerHTML += `<p><a href="test_reports_api.php" target="_blank">Run API Test</a></p>`;
            }
        } else {
            console.error('HTTP Error:', xhr.status, xhr.statusText);
            resultsContainer.innerHTML = `<p>HTTP Error ${xhr.status}: ${xhr.statusText}</p>`;
            resultsContainer.innerHTML += `<p><a href="test_reports_api.php" target="_blank">Run API Test</a></p>`;
        }
    };
    
    xhr.onerror = function() {
        console.error('Network error occurred');
        resultsContainer.innerHTML = '<p>Network error occurred. Please check your connection and try again.</p>';
        resultsContainer.innerHTML += `<p><button onclick="loadReports('${status}')" style="margin-top: 10px;">Retry</button></p>`;
        resultsContainer.innerHTML += `<p><a href="test_reports_api.php" target="_blank">Run API Test</a></p>`;
    };
    
    xhr.send();
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