document.addEventListener('DOMContentLoaded', function() {
    console.log('Document loaded, initializing admin page');
    
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

// Initialize admin page
function initAdminPage() {
    console.log('Initializing admin page...');
    
    // Set up tab navigation
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Tab switching logic
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            console.log('Tab clicked:', tabId);
            
            // Update active tab
            tabLinks.forEach(tl => tl.classList.remove('active'));
            this.classList.add('active');
            
            // Show selected tab content
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === tabId) {
                    content.classList.add('active');
                }
            });
            
            // Load content based on tab
            if (tabId === 'reports') {
                setTimeout(() => {
                    loadReports('all');
                }, 100);
            } else if (tabId === 'content-search') {
                setTimeout(() => {
                    searchContent('');
                }, 100);
            }
            
            // Update URL hash for bookmarking/sharing
            window.location.hash = tabId;
        });
    });
    
    // Handle URL hash on page load
    if (window.location.hash) {
        const tabId = window.location.hash.substring(1);
        const tabLink = document.querySelector(`.tab-link[data-tab="${tabId}"]`);
        if (tabLink) {
            tabLink.click();
        }
    } else {
        // Default to first tab if no hash
        tabLinks[0].click();
    }
    
    // Set up report filters
    const reportFilters = document.querySelectorAll('.report-filter');
    if (reportFilters.length > 0) {
        reportFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                const status = this.getAttribute('data-status');
                loadReports(status);
            });
        });
    }
}

// Add window load event to initialize page 
window.addEventListener('load', function() {
    initAdminPage();
}); 