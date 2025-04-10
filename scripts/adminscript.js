document.addEventListener('DOMContentLoaded', function() {
    console.log('Document loaded, initializing admin page');
    
    // Make key functions globally accessible
    window.generateReport = null; // Will be assigned later in initUsageReports
    
    // Tab navigation setup
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            console.log('Tab clicked:', tabId);
            
            // Remove active class from all tabs
            tabLinks.forEach(tab => tab.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to current tab
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            // Execute specific actions for certain tabs
            if (tabId === 'reports') {
                setTimeout(() => {
                    console.log('Loading reports for Reports tab');
                    loadReports('all');
                }, 100);
            } else if (tabId === 'content-search') {
                setTimeout(() => {
                    console.log('Loading content overview for Content tab');
                    searchContent('');
                }, 100);
            } else if (tabId === 'users') {
                // If we're on the Users tab and there's no content yet, load all users
                const userResults = document.getElementById('user-results');
                if (userResults && userResults.querySelector('.user-table') === null) {
                    setTimeout(() => {
                        console.log('Loading all users for User Management tab');
                        searchUsers('');
                    }, 100);
                }
            } else if (tabId === 'books') {
                // If we're on the Books tab and there's no content yet, load empty search
                const bookResults = document.getElementById('book-results');
                if (bookResults && bookResults.querySelector('p')?.textContent === 'Enter a search term to find books or leave empty to view all books.') {
                    setTimeout(() => {
                        console.log('Loading all books for Book Management tab');
                        searchBooks('');
                    }, 100);
                }
            } else if (tabId === 'threads') {
                // If we're on the Threads tab and there's no content yet, load empty search
                const threadResults = document.getElementById('thread-admin-results');
                if (threadResults && threadResults.querySelector('p')?.textContent === 'Enter a search term to find discussion threads or leave empty to view all threads.') {
                    setTimeout(() => {
                        console.log('Loading all threads for Discussion Management tab');
                        searchThreads('');
                    }, 100);
                }
            } else if (tabId === 'usage-reports') {
                // Initialize the usage reports with default values when tab is clicked
                setTimeout(() => {
                    console.log('Initializing Usage Reports tab');
                    const dateRange = document.getElementById('date-range')?.value || '30';
                    const reportType = document.getElementById('report-type')?.value || 'content';
                    generateReport(dateRange, reportType);
                }, 100);
            }
            
            // Update URL hash
            window.location.hash = tabId;
        });
    });
    
    // Initialize search forms
    // User search
    const userSearchForm = document.getElementById('user-search-form');
    if (userSearchForm) {
        console.log('User search form found, adding event listener');
        userSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('user-search').value;
            console.log('User search submitted with term:', searchTerm);
            searchUsers(searchTerm);
        });
    } else {
        console.log('User search form not found');
    }
    
    // Book search
    const bookSearchForm = document.getElementById('book-search-form');
    if (bookSearchForm) {
        console.log('Book search form found, adding event listener');
        bookSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('book-search').value;
            console.log('Book search submitted with term:', searchTerm);
            searchBooks(searchTerm);
        });
    } else {
        console.log('Book search form not found');
    }
    
    // Thread search
    const threadSearchForm = document.getElementById('thread-admin-search-form');
    if (threadSearchForm) {
        console.log('Thread search form found, adding event listener');
        threadSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('thread-admin-search').value;
            console.log('Thread search submitted with term:', searchTerm);
            searchThreads(searchTerm);
        });
    } else {
        console.log('Thread search form not found');
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
                // Only prevent default if this is a JavaScript-powered action
                // If we're using regular links for the server-side fallback, let the link work normally
                if (window.location.href.includes('status=')) {
                    // We're in server-rendered mode, let the link work normally
                    return;
                }
                
                // Otherwise handle it with JavaScript
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
    
    // Process URL hash on page load
    if (window.location.hash) {
        console.log('URL hash detected:', window.location.hash);
        const tabId = window.location.hash.substring(1);
        const tabLink = document.querySelector(`.tab-link[data-tab="${tabId}"]`);
        
        if (tabLink) {
            console.log('Tab link found for hash, clicking it to activate tab');
            // Use setTimeout to ensure DOM is fully ready
            setTimeout(() => {
                tabLink.click();
            }, 100);
        } else {
            console.log('No tab link found for hash, defaulting to first tab');
            // Default to first tab
            tabLinks[0]?.click();
        }
    } else {
        console.log('No URL hash, defaulting to first tab');
        // Default to first tab if no hash
        tabLinks[0]?.click();
    }
    
    // Handle URL param for status filter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        console.log('Status param found in URL:', status);
        
        // If we're on the reports tab and there's a status parameter, load with that filter
        if (window.location.hash === '#reports' || document.querySelector('#reports.tab-content.active')) {
            setTimeout(() => {
                console.log('Loading reports with status filter from URL param');
                loadReports(status);
                
                // Update active class on filter buttons
                const filterElements = document.querySelectorAll('.report-filter');
                filterElements.forEach(filter => {
                    if (filter.getAttribute('data-status') === status) {
                        filter.classList.add('active');
                    } else {
                        filter.classList.remove('active');
                    }
                });
            }, 200);
        }
    }
});

// Function to search users
function searchUsers(searchTerm) {
    console.log('Searching users with term:', searchTerm);
    const resultsContainer = document.getElementById('user-results');
    
    if (!resultsContainer) {
        console.error('User results container not found');
        return;
    }
    
    // Show loading indicator
    resultsContainer.innerHTML = '<div class="loading-indicator">Searching users...</div>';
    
    // Create an AJAX request using XMLHttpRequest
    const xhr = new XMLHttpRequest();
    const timestamp = new Date().getTime();
    const url = `admin_handler.php?action=search_users&search=${encodeURIComponent(searchTerm)}&_=${timestamp}`;
    console.log('Fetching users from:', url);
    
    xhr.open('GET', url, true);
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log('Parsed user search data:', data);
                
                if (data.success) {
                    if (data.users && data.users.length > 0) {
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
                    resultsContainer.innerHTML = `
                        <div class="error-message">
                            Error: ${data.message || 'Unknown error'}
                            <button class="retry-button" onclick="searchUsers('${encodeURIComponent(searchTerm)}')">Retry</button>
                        </div>`;
                    console.error('API returned error:', data.message);
                }
            } catch (error) {
                console.error('Error parsing JSON response:', error);
                console.error('Raw response:', xhr.responseText.substring(0, 500));
                resultsContainer.innerHTML = `
                    <div class="error-message">
                        Error parsing server response: ${error.message}
                        <button class="retry-button" onclick="searchUsers('${encodeURIComponent(searchTerm)}')">Retry</button>
                    </div>`;
            }
        } else {
            console.error('HTTP Error:', xhr.status, xhr.statusText);
            resultsContainer.innerHTML = `
                <div class="error-message">
                    HTTP Error ${xhr.status}: ${xhr.statusText}
                    <button class="retry-button" onclick="searchUsers('${encodeURIComponent(searchTerm)}')">Retry</button>
                </div>`;
        }
    };
    
    xhr.onerror = function() {
        console.error('Network error occurred during user search');
        resultsContainer.innerHTML = `
            <div class="error-message">
                Network error occurred. Please check your connection and try again.
                <button class="retry-button" onclick="searchUsers('${encodeURIComponent(searchTerm)}')">Retry</button>
            </div>`;
    };
    
    xhr.timeout = 10000; // 10 second timeout
    xhr.ontimeout = function() {
        console.error('Request timed out during user search');
        resultsContainer.innerHTML = `
            <div class="error-message">
                Request timed out. Server might be busy.
                <button class="retry-button" onclick="searchUsers('${encodeURIComponent(searchTerm)}')">Retry</button>
            </div>`;
    };
    
    xhr.send();
}

// Function to search books
function searchBooks(searchTerm) {
    console.log('Searching books with term:', searchTerm);
    const resultsContainer = document.getElementById('book-results');
    
    if (!resultsContainer) {
        console.error('Book results container not found');
        return;
    }
    
    // Show loading indicator
    resultsContainer.innerHTML = '<div class="loading-indicator">Searching books...</div>';
    
    // Create an AJAX request using XMLHttpRequest
    const xhr = new XMLHttpRequest();
    const timestamp = new Date().getTime();
    const url = `admin_handler.php?action=search_books&search=${encodeURIComponent(searchTerm)}&_=${timestamp}`;
    console.log('Fetching books from:', url);
    
    xhr.open('GET', url, true);
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log('Parsed book search data:', data);
                
                if (data.success) {
                    if (data.books && data.books.length > 0) {
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
                    resultsContainer.innerHTML = `
                        <div class="error-message">
                            Error: ${data.message || 'Unknown error'}
                            <button class="retry-button" onclick="searchBooks('${encodeURIComponent(searchTerm)}')">Retry</button>
                        </div>`;
                    console.error('API returned error:', data.message);
                }
            } catch (error) {
                console.error('Error parsing JSON response:', error);
                console.error('Raw response:', xhr.responseText.substring(0, 500));
                resultsContainer.innerHTML = `
                    <div class="error-message">
                        Error parsing server response: ${error.message}
                        <button class="retry-button" onclick="searchBooks('${encodeURIComponent(searchTerm)}')">Retry</button>
                    </div>`;
            }
        } else {
            console.error('HTTP Error:', xhr.status, xhr.statusText);
            resultsContainer.innerHTML = `
                <div class="error-message">
                    HTTP Error ${xhr.status}: ${xhr.statusText}
                    <button class="retry-button" onclick="searchBooks('${encodeURIComponent(searchTerm)}')">Retry</button>
                </div>`;
        }
    };
    
    xhr.onerror = function() {
        console.error('Network error occurred during book search');
        resultsContainer.innerHTML = `
            <div class="error-message">
                Network error occurred. Please check your connection and try again.
                <button class="retry-button" onclick="searchBooks('${encodeURIComponent(searchTerm)}')">Retry</button>
            </div>`;
    };
    
    xhr.timeout = 10000; // 10 second timeout
    xhr.ontimeout = function() {
        console.error('Request timed out during book search');
        resultsContainer.innerHTML = `
            <div class="error-message">
                Request timed out. Server might be busy.
                <button class="retry-button" onclick="searchBooks('${encodeURIComponent(searchTerm)}')">Retry</button>
            </div>`;
    };
    
    xhr.send();
}

// Function to search threads
function searchThreads(searchTerm) {
    console.log('Searching threads with term:', searchTerm);
    const resultsContainer = document.getElementById('thread-admin-results');
    
    if (!resultsContainer) {
        console.error('Thread results container not found');
        return;
    }
    
    // Show loading indicator
    resultsContainer.innerHTML = '<div class="loading-indicator">Searching threads...</div>';
    
    // Create an AJAX request using XMLHttpRequest
    const xhr = new XMLHttpRequest();
    const timestamp = new Date().getTime();
    const url = `admin_handler.php?action=search_threads&search=${encodeURIComponent(searchTerm)}&_=${timestamp}`;
    console.log('Fetching threads from:', url);
    
    xhr.open('GET', url, true);
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log('Parsed thread search data:', data);
                
                if (data.success) {
                    if (data.threads && data.threads.length > 0) {
                        let html = '<div class="thread-table-container">';
                        html += '<table class="thread-table">';
                        html += '<thead>';
                        html += '<tr>';
                        html += '<th>Title</th>';
                        html += '<th>Author</th>';
                        html += '<th>Date</th>';
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
                    resultsContainer.innerHTML = `
                        <div class="error-message">
                            Error: ${data.message || 'Unknown error'}
                            <button class="retry-button" onclick="searchThreads('${encodeURIComponent(searchTerm)}')">Retry</button>
                        </div>`;
                    console.error('API returned error:', data.message);
                }
            } catch (error) {
                console.error('Error parsing JSON response:', error);
                console.error('Raw response:', xhr.responseText.substring(0, 500));
                resultsContainer.innerHTML = `
                    <div class="error-message">
                        Error parsing server response: ${error.message}
                        <button class="retry-button" onclick="searchThreads('${encodeURIComponent(searchTerm)}')">Retry</button>
                    </div>`;
            }
        } else {
            console.error('HTTP Error:', xhr.status, xhr.statusText);
            resultsContainer.innerHTML = `
                <div class="error-message">
                    HTTP Error ${xhr.status}: ${xhr.statusText}
                    <button class="retry-button" onclick="searchThreads('${encodeURIComponent(searchTerm)}')">Retry</button>
                </div>`;
        }
    };
    
    xhr.onerror = function() {
        console.error('Network error occurred during thread search');
        resultsContainer.innerHTML = `
            <div class="error-message">
                Network error occurred. Please check your connection and try again.
                <button class="retry-button" onclick="searchThreads('${encodeURIComponent(searchTerm)}')">Retry</button>
            </div>`;
    };
    
    xhr.timeout = 10000; // 10 second timeout
    xhr.ontimeout = function() {
        console.error('Request timed out during thread search');
        resultsContainer.innerHTML = `
            <div class="error-message">
                Request timed out. Server might be busy.
                <button class="retry-button" onclick="searchThreads('${encodeURIComponent(searchTerm)}')">Retry</button>
            </div>`;
    };
    
    xhr.send();
}

// Function to search content across all content types
function searchContent(searchTerm) {
    console.log('Loading content overview...');
    const resultsContainer = document.getElementById('content-search-results');
    const summaryContainer = document.getElementById('content-search-summary');
    
    if (!resultsContainer) {
        console.error('Content search results container not found');
        return;
    }
    
    resultsContainer.innerHTML = '<div class="loading-indicator">Loading content overview...</div>';
    summaryContainer.style.display = 'none';
    
    // Debug: log the URL we're fetching
    const timestamp = new Date().getTime();
    const url = `admin_handler.php?action=get_content_overview&_=${timestamp}`;
    console.log('Fetching content overview from:', url);
    
    // Use XMLHttpRequest instead of fetch for better compatibility and error handling
    const xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    
    xhr.onload = function() {
        console.log('XHR status:', xhr.status);
        console.log('Response text preview:', xhr.responseText.substring(0, 200));
        
        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log('Parsed data:', data);
                
                if (data.success) {
                    // Update summary
                    if (data.summary) {
                        document.getElementById('total-results').textContent = data.summary.total;
                        document.getElementById('book-results-count').textContent = data.summary.books;
                        document.getElementById('thread-results-count').textContent = data.summary.threads;
                        document.getElementById('comment-results-count').textContent = data.summary.comments;
                        summaryContainer.style.display = 'block';
                    }
                    
                    if (!data.content || (!data.content.books && !data.content.threads && !data.content.comments)) {
                        resultsContainer.innerHTML = '<p>No content found.</p>';
                        return;
                    }
                    
                    // Generate content overview
                    let html = '<div class="content-overview">';
                    
                    // Recent activity section
                    html += '<div class="overview-section">';
                    html += '<h4>Recent Activity</h4>';
                    
                    // Books section
                    html += '<div class="content-section">';
                    html += '<h5>Latest Books</h5>';
                    if (data.content.books && data.content.books.length > 0) {
                        html += '<table class="overview-table">';
                        html += '<thead><tr><th>Title</th><th>User</th><th>Date</th><th>Actions</th></tr></thead>';
                        html += '<tbody>';
                        data.content.books.forEach(book => {
                            html += '<tr>';
                            html += '<td><a href="book_detail.php?id=' + book.book_id + '">' + book.title + '</a></td>';
                            html += '<td>' + book.username + '</td>';
                            html += '<td>' + formatDate(book.created_at) + '</td>';
                            html += '<td>';
                            html += '<a href="book_detail.php?id=' + book.book_id + '" class="view-button">View</a>';
                            html += '<form action="admin_actions.php" method="post" class="inline-form">';
                            html += '<input type="hidden" name="action" value="delete_book">';
                            html += '<input type="hidden" name="book_id" value="' + book.book_id + '">';
                            html += '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this book? This action cannot be undone.\')">Delete</button>';
                            html += '</form>';
                            html += '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody>';
                        html += '</table>';
                    } else {
                        html += '<p>No recent books found.</p>';
                    }
                    html += '</div>';
                    
                    // Threads section
                    html += '<div class="content-section">';
                    html += '<h5>Latest Discussions</h5>';
                    if (data.content.threads && data.content.threads.length > 0) {
                        html += '<table class="overview-table">';
                        html += '<thead><tr><th>Title</th><th>User</th><th>Date</th><th>Actions</th></tr></thead>';
                        html += '<tbody>';
                        data.content.threads.forEach(thread => {
                            html += '<tr>';
                            html += '<td><a href="thread.php?id=' + thread.thread_id + '">' + thread.title + '</a></td>';
                            html += '<td>' + thread.username + '</td>';
                            html += '<td>' + formatDate(thread.created_at) + '</td>';
                            html += '<td>';
                            html += '<a href="thread.php?id=' + thread.thread_id + '" class="view-button">View</a>';
                            html += '<form action="admin_actions.php" method="post" class="inline-form">';
                            html += '<input type="hidden" name="action" value="delete_thread">';
                            html += '<input type="hidden" name="thread_id" value="' + thread.thread_id + '">';
                            html += '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this thread? All comments will be deleted as well. This action cannot be undone.\')">Delete</button>';
                            html += '</form>';
                            html += '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody>';
                        html += '</table>';
                    } else {
                        html += '<p>No recent discussions found.</p>';
                    }
                    html += '</div>';
                    
                    // Comments section
                    html += '<div class="content-section">';
                    html += '<h5>Latest Comments</h5>';
                    if (data.content.comments && data.content.comments.length > 0) {
                        html += '<table class="overview-table">';
                        html += '<thead><tr><th>Comment</th><th>User</th><th>Thread</th><th>Date</th><th>Actions</th></tr></thead>';
                        html += '<tbody>';
                        data.content.comments.forEach(comment => {
                            html += '<tr>';
                            html += '<td>' + comment.content.substring(0, 50) + (comment.content.length > 50 ? '...' : '') + '</td>';
                            html += '<td>' + comment.username + '</td>';
                            html += '<td><a href="thread.php?id=' + comment.thread_id + '">' + comment.thread_title + '</a></td>';
                            html += '<td>' + formatDate(comment.created_at) + '</td>';
                            html += '<td>';
                            html += '<a href="thread.php?id=' + comment.thread_id + '#comment-' + comment.comment_id + '" class="view-button">View</a>';
                            html += '<form action="admin_actions.php" method="post" class="inline-form">';
                            html += '<input type="hidden" name="action" value="delete_comment">';
                            html += '<input type="hidden" name="comment_id" value="' + comment.comment_id + '">';
                            html += '<button type="submit" class="delete-button" onclick="return confirm(\'Are you sure you want to delete this comment? This action cannot be undone.\')">Delete</button>';
                            html += '</form>';
                            html += '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody>';
                        html += '</table>';
                    } else {
                        html += '<p>No recent comments found.</p>';
                    }
                    html += '</div>';
                    
                    html += '</div>'; // End overview-section
                    html += '</div>'; // End content-overview
                    
                    resultsContainer.innerHTML = html;
                } else {
                    console.error('API returned error:', data.message);
                    resultsContainer.innerHTML = `
                        <div class="error-message">
                            <p>Error: ${data.message || 'Unknown error loading content overview'}</p>
                            <button onclick="searchContent('')" class="retry-button">Retry</button>
                        </div>`;
                    summaryContainer.style.display = 'none';
                }
            } catch (error) {
                console.error('Error parsing JSON response:', error);
                console.error('Raw response:', xhr.responseText);
                resultsContainer.innerHTML = `
                    <div class="error-message">
                        <p>Error parsing server response. See console for details.</p>
                        <button onclick="searchContent('')" class="retry-button">Retry</button>
                    </div>`;
                summaryContainer.style.display = 'none';
            }
        } else {
            console.error('HTTP Error:', xhr.status, xhr.statusText);
            resultsContainer.innerHTML = `
                <div class="error-message">
                    <p>HTTP Error ${xhr.status}: ${xhr.statusText}</p>
                    <button onclick="searchContent('')" class="retry-button">Retry</button>
                </div>`;
            summaryContainer.style.display = 'none';
        }
    };
    
    xhr.onerror = function() {
        console.error('Network error occurred');
        resultsContainer.innerHTML = `
            <div class="error-message">
                <p>Network error occurred. Please check your connection and try again.</p>
                <button onclick="searchContent('')" class="retry-button">Retry</button>
            </div>`;
        summaryContainer.style.display = 'none';
    };
    
    xhr.timeout = 10000; // 10 seconds timeout
    xhr.ontimeout = function() {
        console.error('Request timed out');
        resultsContainer.innerHTML = `
            <div class="error-message">
                <p>Request timed out. Server might be busy.</p>
                <button onclick="searchContent('')" class="retry-button">Retry</button>
            </div>`;
        summaryContainer.style.display = 'none';
    };
    
    // Send the request
    try {
        xhr.send();
    } catch (error) {
        console.error('Error sending request:', error);
        resultsContainer.innerHTML = `
            <div class="error-message">
                <p>Error sending request: ${error.message}</p>
                <button onclick="searchContent('')" class="retry-button">Retry</button>
            </div>`;
        summaryContainer.style.display = 'none';
    }
}

// Helper function to format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Function to load reports
function loadReports(status = 'all') {
    console.log('Loading reports with status filter:', status);
    const resultsContainer = document.getElementById('report-results');
    
    if (!resultsContainer) {
        console.error('Report results container not found');
        return;
    }
    
    // Show loading indicator
    resultsContainer.innerHTML = '<div class="loading-indicator">Loading reports...</div>';
    
    // Get all report filter elements and update active class
    const filterElements = document.querySelectorAll('.report-filter');
    filterElements.forEach(filter => {
        if (filter.getAttribute('data-status') === status) {
            filter.classList.add('active');
        } else {
            filter.classList.remove('active');
        }
    });
    
    // Debug: log the URL we're fetching
    const timestamp = new Date().getTime();
    const url = `admin_handler.php?action=get_reports&status=${status}&_=${timestamp}`;
    console.log('Fetching reports from:', url);
    
    // Use XMLHttpRequest for better compatibility and error handling
    const xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log('Parsed report data:', data);
                
                // Remove loading indicator
                const loadingIndicator = resultsContainer.querySelector('.loading-indicator');
                if (loadingIndicator) loadingIndicator.remove();
                
                if (data.success) {
                    if (!data.reports || data.reports.length === 0) {
                        // Hide any fallback that might be showing
                        const fallbackReports = document.getElementById('fallback-reports');
                        if (fallbackReports) {
                            fallbackReports.style.display = 'none';
                        }
                        
                        resultsContainer.innerHTML = '<p>No reports found with selected status.</p>';
                        return;
                    }
                    
                    // Hide any fallback that might be showing
                    const fallbackReports = document.getElementById('fallback-reports');
                    if (fallbackReports) {
                        fallbackReports.style.display = 'none';
                    }
                    
                    // Create table for reports
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
                    
                    // Add each report to the table
                    data.reports.forEach(report => {
                        html += report.html;
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    
                    // Update the container with the new HTML
                    resultsContainer.innerHTML = html;
                    
                    // Add event listeners to view report buttons
                    setupReportActionListeners();
                    
                } else {
                    console.error('Error loading reports:', data.message);
                    // Show error message and show fallback if available
                    resultsContainer.innerHTML = `
                        <div class="error-message">
                            Failed to load reports: ${data.message || 'Unknown error'}
                            <button class="retry-button" onclick="loadReports('${status}')">Retry</button>
                        </div>
                    `;
                    
                    // Show the fallback if it exists
                    const fallbackReports = document.getElementById('fallback-reports');
                    if (fallbackReports) {
                        fallbackReports.style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error parsing report data:', error);
                // Show error message and show fallback if available
                resultsContainer.innerHTML = `
                    <div class="error-message">
                        Failed to parse report data: ${error.message}
                        <button class="retry-button" onclick="loadReports('${status}')">Retry</button>
                    </div>
                `;
                
                // Show the fallback if it exists
                const fallbackReports = document.getElementById('fallback-reports');
                if (fallbackReports) {
                    fallbackReports.style.display = 'block';
                }
            }
        } else {
            console.error('XHR error:', xhr.status, xhr.statusText);
            // Show error message and show fallback if available
            resultsContainer.innerHTML = `
                <div class="error-message">
                    Failed to load reports. Server returned status ${xhr.status}
                    <button class="retry-button" onclick="loadReports('${status}')">Retry</button>
                </div>
            `;
            
            // Show the fallback if it exists
            const fallbackReports = document.getElementById('fallback-reports');
            if (fallbackReports) {
                fallbackReports.style.display = 'block';
            }
        }
    };
    
    xhr.onerror = function() {
        console.error('Network error while loading reports');
        // Show error message and show fallback if available
        resultsContainer.innerHTML = `
            <div class="error-message">
                Network error while loading reports. Please check your connection.
                <button class="retry-button" onclick="loadReports('${status}')">Retry</button>
            </div>
        `;
        
        // Show the fallback if it exists
        const fallbackReports = document.getElementById('fallback-reports');
        if (fallbackReports) {
            fallbackReports.style.display = 'block';
        }
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
    console.log("Showing report details for report ID:", reportId);
    
    // Show loading indicator in the modal
    const modal = document.getElementById('report-details-modal');
    const content = document.getElementById('report-detail-content');
    
    if (!content) {
        console.error("Error: report-detail-content element not found!");
        alert("Error: Could not find the content container for report details.");
        return;
    }
    
    // Show the modal with a loading message
    content.innerHTML = '<div class="loading">Loading report details...</div>';
    modal.style.display = 'block';
    
    fetch(`admin_handler.php?action=get_report_details&report_id=${reportId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                let html = '<div class="report-details">';
                html += `<p><strong>Content Type:</strong> <span class="content-type-indicator ${data.report.content_type}">${data.report.content_type}</span></p>`;
                html += `<p><strong>Content ID:</strong> ${data.report.content_id}</p>`;
                html += `<p><strong>Reporter:</strong> ${data.report.reporter_username}</p>`;
                html += `<p><strong>Reason:</strong> ${data.report.reason}</p>`;
                html += `<p><strong>Details:</strong></p>`;
                html += `<div class="report-details-text">${data.report.details || 'No additional details provided.'}</div>`;
                html += `<p><strong>Status:</strong> <span class="status-indicator ${data.report.status}">${data.report.status}</span></p>`;
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
                
                html += `<div class="report-actions">`;
                html += `<a href="${viewLink}" target="_blank" class="view-content-btn">View Reported Content</a>`;
                
                // Add action buttons based on status
                if (data.report.status === 'pending') {
                    html += `<form action="admin_actions.php" method="post" class="inline-form">
                            <input type="hidden" name="action" value="resolve_report">
                            <input type="hidden" name="report_id" value="${data.report.report_id}">
                            <button type="submit" class="resolve-button">Resolve Report</button>
                        </form>
                        <form action="admin_actions.php" method="post" class="inline-form">
                            <input type="hidden" name="action" value="dismiss_report">
                            <input type="hidden" name="report_id" value="${data.report.report_id}">
                            <button type="submit" class="dismiss-button">Dismiss Report</button>
                        </form>`;
                }
                
                html += `</div>`;
                html += '</div>';
                
                content.innerHTML = html;
            } else {
                content.innerHTML = `<div class="error-message">Error loading report details: ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error("Error fetching report details:", error);
            content.innerHTML = `<div class="error-message">
                <p>Error loading report details: ${error.message}</p>
                <p>Please try again or reload the page.</p>
            </div>`;
        });
}

// Function to initialize usage reports
function initUsageReports() {
    console.log('Initializing usage reports');
    
    // Get references to DOM elements
    const dateRangeSelect = document.getElementById('date-range');
    const reportTypeSelect = document.getElementById('report-type');
    const generateButton = document.getElementById('generate-report');
    const reportTitle = document.getElementById('report-title');
    const reportLoading = document.getElementById('report-loading');
    const reportError = document.getElementById('report-error');
    const reportTableContainer = document.getElementById('report-table-container');
    
    // Debug element references
    console.log('dateRangeSelect:', dateRangeSelect);
    console.log('reportTypeSelect:', reportTypeSelect);
    console.log('generateButton:', generateButton);
    console.log('reportTitle:', reportTitle);
    console.log('reportLoading:', reportLoading);
    console.log('reportError:', reportError);
    console.log('reportTableContainer:', reportTableContainer);
    
    if (!dateRangeSelect || !reportTypeSelect || !generateButton) {
        console.error('Missing required elements for usage reports');
        return;
    }
    
    // Initialize Chart.js
    let reportChart = null;
    
    // Handle report generation
    generateButton.addEventListener('click', function() {
        console.log('Generate report button clicked');
        const dateRange = dateRangeSelect.value;
        const reportType = reportTypeSelect.value;
        
        console.log(`Button clicked with dateRange=${dateRange}, reportType=${reportType}`);
        generateReport(dateRange, reportType);
    });
    
    // Also update when report type is changed
    reportTypeSelect.addEventListener('change', function() {
        const dateRange = dateRangeSelect.value;
        const reportType = this.value;
        
        // Update report title based on selection
        updateReportTitle(reportType, dateRange);
    });
    
    // Update title when date range changes
    dateRangeSelect.addEventListener('change', function() {
        const dateRange = this.value;
        const reportType = reportTypeSelect.value;
        
        // Update report title based on selection
        updateReportTitle(reportType, dateRange);
    });
    
    // Function to update report title
    function updateReportTitle(reportType, dateRange) {
        let titleText = '';
        
        switch (reportType) {
            case 'content':
                titleText = 'Content Creation';
                break;
            case 'activity':
                titleText = 'User Activity';
                break;
            case 'popular':
                titleText = 'Content Popularity';
                break;
        }
        
        // Add date range
        if (dateRange === 'all') {
            titleText += ' (All Time)';
        } else {
            titleText += ` (Last ${dateRange} days)`;
        }
        
        if (reportTitle) {
            reportTitle.textContent = titleText;
        }
    }
    
    // Function to generate report
    function generateReport(dateRange, reportType) {
        console.log(`Generating ${reportType} report for ${dateRange} days`);
        
        // Show loading indicator
        if (reportLoading) {
            reportLoading.style.display = 'block';
        }
        
        // Hide error message if shown
        if (reportError) {
            reportError.style.display = 'none';
        }
        
        // Clear previous chart
        if (reportChart) {
            reportChart.destroy();
            reportChart = null;
        }
        
        // Make API request to get report data
        const timestamp = new Date().getTime();
        const url = `admin_handler.php?action=get_usage_report&report_type=${reportType}&date_range=${dateRange}&_=${timestamp}`;
        console.log('Fetching report data from:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received report data:', data);
                
                // Hide loading indicator
                if (reportLoading) {
                    reportLoading.style.display = 'none';
                }
                
                if (data.success) {
                    // Update report title if provided
                    if (data.title && reportTitle) {
                        reportTitle.textContent = data.title;
                    }
                    
                    // Process data based on report type
                    switch (reportType) {
                        case 'content':
                            renderContentReport(data);
                            break;
                        case 'activity':
                            renderActivityReport(data);
                            break;
                        case 'popular':
                            renderPopularityReport(data);
                            break;
                    }
                } else {
                    showError(data.message || 'Failed to generate report');
                }
            })
            .catch(error => {
                console.error('Error generating report:', error);
                if (reportLoading) {
                    reportLoading.style.display = 'none';
                }
                showError(error.message || 'An error occurred while generating the report');
            });
    }
    
    // Function to show error message
    function showError(message) {
        if (reportError) {
            reportError.textContent = message;
            reportError.style.display = 'block';
        }
    }
    
    // Function to render content creation report
    function renderContentReport(data) {
        const contentData = data.data || { books: [], threads: [], comments: [] };
        
        // Create a consolidated array with all dates
        const allDates = new Set();
        ['books', 'threads', 'comments'].forEach(type => {
            contentData[type].forEach(item => {
                allDates.add(item.date);
            });
        });
        
        // Convert to array and sort
        const sortedDates = Array.from(allDates).sort();
        
        // Create data maps for each content type
        const booksMap = new Map(contentData.books.map(item => [item.date, parseInt(item.count)]));
        const threadsMap = new Map(contentData.threads.map(item => [item.date, parseInt(item.count)]));
        const commentsMap = new Map(contentData.comments.map(item => [item.date, parseInt(item.count)]));
        
        // Create chart data
        const chartLabels = sortedDates.map(date => formatDate(date));
        const booksData = sortedDates.map(date => booksMap.get(date) || 0);
        const threadsData = sortedDates.map(date => threadsMap.get(date) || 0);
        const commentsData = sortedDates.map(date => commentsMap.get(date) || 0);
        
        createChart('Content Created Over Time', chartLabels, [
            {
                label: 'Books',
                data: booksData,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',  // Blue
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            },
            {
                label: 'Threads',
                data: threadsData,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',  // Teal
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            },
            {
                label: 'Comments',
                data: commentsData,
                backgroundColor: 'rgba(153, 102, 255, 0.5)',  // Purple
                borderColor: 'rgb(153, 102, 255)',
                borderWidth: 1
            }
        ]);
        
        // Create summary table
        const totals = data.totals || {};
        
        const getChangePercentage = (current, previous) => {
            if (previous <= 0) return 'N/A';
            return ((current - previous) / previous * 100).toFixed(2);
        };
        
        const booksChange = getChangePercentage(totals.books_current, totals.books_previous);
        const threadsChange = getChangePercentage(totals.threads_current, totals.threads_previous);
        const commentsChange = getChangePercentage(totals.comments_current, totals.comments_previous);
        
        let tableHTML = `
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Content Type</th>
                        <th>Current Period</th>
                        <th>Previous Period</th>
                        <th>Change</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Books</td>
                        <td class="count-cell">${totals.books_current || 0}</td>
                        <td class="count-cell">${totals.books_previous || 0}</td>
                        <td class="count-cell ${getChangeClass(booksChange)}">
                            ${booksChange === 'N/A' ? 'N/A' : booksChange + '%'}
                        </td>
                    </tr>
                    <tr>
                        <td>Threads</td>
                        <td class="count-cell">${totals.threads_current || 0}</td>
                        <td class="count-cell">${totals.threads_previous || 0}</td>
                        <td class="count-cell ${getChangeClass(threadsChange)}">
                            ${threadsChange === 'N/A' ? 'N/A' : threadsChange + '%'}
                        </td>
                    </tr>
                    <tr>
                        <td>Comments</td>
                        <td class="count-cell">${totals.comments_current || 0}</td>
                        <td class="count-cell">${totals.comments_previous || 0}</td>
                        <td class="count-cell ${getChangeClass(commentsChange)}">
                            ${commentsChange === 'N/A' ? 'N/A' : commentsChange + '%'}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="count-cell"><strong>${(totals.books_current || 0) + (totals.threads_current || 0) + (totals.comments_current || 0)}</strong></td>
                        <td class="count-cell"><strong>${(totals.books_previous || 0) + (totals.threads_previous || 0) + (totals.comments_previous || 0)}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            
            <h4 style="margin-top: 20px;">Daily Content Creation</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Books</th>
                        <th>Threads</th>
                        <th>Comments</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (sortedDates.length === 0) {
            tableHTML += '<tr><td colspan="5">No content creation data available for this period</td></tr>';
        } else {
            sortedDates.forEach(date => {
                const books = booksMap.get(date) || 0;
                const threads = threadsMap.get(date) || 0;
                const comments = commentsMap.get(date) || 0;
                const total = books + threads + comments;
                
                tableHTML += `
                    <tr>
                        <td>${formatDate(date)}</td>
                        <td class="count-cell">${books}</td>
                        <td class="count-cell">${threads}</td>
                        <td class="count-cell">${comments}</td>
                        <td class="count-cell"><strong>${total}</strong></td>
                    </tr>
                `;
            });
        }
        
        tableHTML += '</tbody></table>';
        
        if (reportTableContainer) {
            reportTableContainer.innerHTML = tableHTML;
        }
    }
    
    // Function to render user activity report
    function renderActivityReport(data) {
        const activity = data.data || [];
        const totals = data.totals || {};
        
        // Create chart for activity metrics
        createChart('Activity Distribution', ['Books', 'Threads', 'Comments'], [
            {
                label: 'Content Items',
                data: [
                    totals.book_count || 0,
                    totals.thread_count || 0, 
                    totals.comment_count || 0
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',  // Blue for consistency
                    'rgba(75, 192, 192, 0.5)',  // Teal
                    'rgba(153, 102, 255, 0.5)'  // Purple instead of red
                ],
                borderColor: [
                    'rgb(54, 162, 235)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ],
                borderWidth: 1
            }
        ], 'bar');
        
        // Create summary table
        let tableHTML = `
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Active Users</td>
                        <td class="count-cell">${totals.active_users || 0}</td>
                    </tr>
                    <tr>
                        <td>Books Created</td>
                        <td class="count-cell">${totals.book_count || 0}</td>
                    </tr>
                    <tr>
                        <td>Threads Created</td>
                        <td class="count-cell">${totals.thread_count || 0}</td>
                    </tr>
                    <tr>
                        <td>Comments Posted</td>
                        <td class="count-cell">${totals.comment_count || 0}</td>
                    </tr>
                </tbody>
            </table>
            
            <h4 style="margin-top: 20px;">Most Active Users</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Books</th>
                        <th>Threads</th>
                        <th>Comments</th>
                        <th>Total Activity</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (activity.length === 0) {
            tableHTML += '<tr><td colspan="6">No user activity data available for this period</td></tr>';
        } else {
            activity.forEach(user => {
                const totalActivity = parseInt(user.book_count) + parseInt(user.thread_count) + parseInt(user.comment_count);
                tableHTML += `
                    <tr>
                        <td>${user.username}</td>
                        <td class="count-cell">${user.book_count}</td>
                        <td class="count-cell">${user.thread_count}</td>
                        <td class="count-cell">${user.comment_count}</td>
                        <td class="count-cell"><strong>${totalActivity}</strong></td>
                        <td>${user.last_activity ? formatDate(user.last_activity) : 'N/A'}</td>
                    </tr>
                `;
            });
        }
        
        tableHTML += '</tbody></table>';
        
        if (reportTableContainer) {
            reportTableContainer.innerHTML = tableHTML;
        }
    }
    
    // Function to render content popularity report
    function renderPopularityReport(data) {
        const popular = data.data || { books: [], threads: [] };
        
        // Create charts for popular content
        if (popular.books.length > 0) {
            const bookLabels = popular.books.slice(0, 5).map(book => book.title.substring(0, 15) + (book.title.length > 15 ? '...' : ''));
            const bookViews = popular.books.slice(0, 5).map(book => parseInt(book.views) || 0);
            const bookComments = popular.books.slice(0, 5).map(book => parseInt(book.comment_count) || 0);
            
            createChart('Most Viewed Books', bookLabels, [
                {
                    label: 'Views',
                    data: bookViews,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',  // Blue for consistency
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                },
                {
                    label: 'Comments',
                    data: bookComments,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',  // Teal for consistency
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }
            ], 'bar');
        }
        
        // Create tables for popular books and threads
        let tableHTML = `
            <h4>Most Popular Books</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Creator</th>
                        <th>Views</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (popular.books.length === 0) {
            tableHTML += '<tr><td colspan="4">No book data available</td></tr>';
        } else {
            popular.books.forEach(book => {
                tableHTML += `
                    <tr>
                        <td><a href="book_detail.php?id=${book.book_id}">${book.title}</a></td>
                        <td>${book.username}</td>
                        <td class="count-cell">${book.views || 0}</td>
                        <td class="count-cell">${book.comment_count || 0}</td>
                    </tr>
                `;
            });
        }
        
        tableHTML += `
                </tbody>
            </table>
            
            <h4 style="margin-top: 20px;">Most Active Threads</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Creator</th>
                        <th>Views</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        if (popular.threads.length === 0) {
            tableHTML += '<tr><td colspan="4">No thread data available</td></tr>';
        } else {
            popular.threads.forEach(thread => {
                tableHTML += `
                    <tr>
                        <td><a href="thread.php?id=${thread.thread_id}">${thread.title}</a></td>
                        <td>${thread.username}</td>
                        <td class="count-cell">${thread.views || 0}</td>
                        <td class="count-cell">${thread.comment_count || 0}</td>
                    </tr>
                `;
            });
        }
        
        tableHTML += '</tbody></table>';
        
        if (reportTableContainer) {
            reportTableContainer.innerHTML = tableHTML;
        }
    }
    
    // Helper function to create a chart
    function createChart(title, labels, datasets, type = 'line') {
        console.log('Creating chart:', title, 'with type:', type);
        console.log('Chart labels:', labels);
        console.log('Chart datasets:', datasets);
        
        const chartCanvas = document.getElementById('report-chart');
        if (!chartCanvas) {
            console.error('Chart canvas not found');
            showError('Error: Chart canvas element not found');
            return;
        }
        
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded, attempting to load it dynamically');
            showError('Loading Chart.js library...');
            
            // Add Chart.js dynamically
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = function() {
                console.log('Chart.js loaded successfully');
                showError(''); // Clear error
                createChartInstance(title, labels, datasets, type);
            };
            script.onerror = function() {
                console.error('Failed to load Chart.js');
                showError('Failed to load Chart.js library. Please refresh the page or try again later.');
            };
            document.head.appendChild(script);
        } else {
            console.log('Chart.js is already loaded');
            createChartInstance(title, labels, datasets, type);
        }
    }
    
    function createChartInstance(title, labels, datasets, type) {
        console.log('Creating chart instance');
        const chartCanvas = document.getElementById('report-chart');
        if (!chartCanvas) {
            console.error('Chart canvas not found on instance creation');
            return;
        }
        
        try {
            // Destroy previous chart if exists
            if (reportChart) {
                console.log('Destroying previous chart');
                reportChart.destroy();
            }
            
            // Create new chart
            console.log('Initializing new chart');
            reportChart = new Chart(chartCanvas, {
                type: type,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: title,
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,  // No decimal places
                                callback: function(value) {
                                    return Math.round(value); // Return rounded values
                                }
                            }
                        }
                    }
                }
            });
            console.log('Chart created successfully');
        } catch (error) {
            console.error('Error creating chart:', error);
            showError('Error creating chart: ' + error.message);
        }
    }
    
    // Helper function to get CSS class for trend direction
    function getChangeClass(percentChange) {
        if (percentChange === 'N/A') return 'trend-neutral';
        const value = parseFloat(percentChange);
        if (value > 0) return 'trend-positive';
        if (value < 0) return 'trend-negative';
        return 'trend-neutral';
    }
    
    // Make generateReport globally accessible
    window.generateReport = generateReport;
    
    // Generate default report on page load
    const defaultDateRange = dateRangeSelect.value;
    const defaultReportType = reportTypeSelect.value;
    generateReport(defaultDateRange, defaultReportType);
}

// Add initialization for usage reports to the existing initAdminPage function
const originalInitAdminPage = initAdminPage;
function initAdminPage() {
    console.log('Initializing admin page');
    
    // Initialize usage reports tab
    try {
        console.log('Attempting to initialize usage reports');
        initUsageReports();
        console.log('Usage reports initialized successfully');
    } catch (error) {
        console.error('Error initializing usage reports:', error);
    }
}

// Add window load event to initialize page 
window.addEventListener('load', function() {
    console.log('Window loaded, running initAdminPage');
    initAdminPage();
}); 