document.addEventListener("DOMContentLoaded", () => {
    // Get the threads container element
    const threadsContainer = document.querySelector('.threads-container');
    
    // Get search parameters
    const searchInput = document.getElementById('thread-search');
    let searchQuery = searchInput ? searchInput.value : '';
    
    // Initialize the last update time
    let lastUpdateTime = new Date().toISOString();
    
    // Set up polling for new threads every 10 seconds
    const threadInterval = setInterval(refreshThreads, 10000);
    
    // Function to refresh threads
    function refreshThreads() {
        // Build the URL with the search query if it exists
        let url = 'thread_handler.php?action=list&last_update=' + encodeURIComponent(lastUpdateTime);
        if (searchQuery) {
            url += '&search=' + encodeURIComponent(searchQuery);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.threads.length > 0) {
                    // Update the last update time
                    lastUpdateTime = data.timestamp;
                    
                    // Process new threads
                    data.threads.forEach(thread => {
                        // Check if this thread already exists
                        const existingThread = document.querySelector(`[data-thread-id="${thread.thread_id}"]`);
                        
                        if (existingThread) {
                            // Update existing thread
                            existingThread.querySelector('h3 a').textContent = thread.title;
                            existingThread.querySelector('.thread-meta').innerHTML = 
                                `Started by: ${thread.username} | Date: ${formatDate(thread.created_at)} | Comments: ${thread.comment_count}`;
                            existingThread.querySelector('.thread-preview').textContent = 
                                `${thread.content.substring(0, 150)}...`;
                            
                            // Highlight the updated thread
                            existingThread.classList.add('updated-thread');
                            setTimeout(() => {
                                existingThread.classList.remove('updated-thread');
                            }, 3000);
                        } else {
                            // Create a new thread element
                            const threadElement = document.createElement('div');
                            threadElement.className = 'thread-item new-thread';
                            threadElement.setAttribute('data-thread-id', thread.thread_id);
                            
                            threadElement.innerHTML = `
                                <h3><a href="thread.php?id=${thread.thread_id}">${thread.title}</a></h3>
                                <p class="thread-meta">
                                    Started by: ${thread.username} | 
                                    Date: ${formatDate(thread.created_at)} | 
                                    Comments: ${thread.comment_count}
                                </p>
                                <p class="thread-preview">${thread.content.substring(0, 150)}...</p>
                            `;
                            
                            // Insert at the top of the threads container
                            threadsContainer.insertBefore(threadElement, threadsContainer.firstChild);
                            
                            // Remove highlight after animation
                            setTimeout(() => {
                                threadElement.classList.remove('new-thread');
                            }, 3000);
                        }
                    });
                    
                    // If there was a "no threads" message, remove it
                    const noThreadsMessage = threadsContainer.querySelector('p:only-child');
                    if (noThreadsMessage && noThreadsMessage.textContent.includes('No threads')) {
                        noThreadsMessage.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error refreshing threads:', error);
            });
    }
    
    // Function to format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true
        };
        return date.toLocaleDateString('en-US', options);
    }
    
    // Handle search form submission
    const searchForm = document.getElementById('thread-search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            searchQuery = searchInput.value;
            
            // Update the URL with search parameter without refreshing the page
            const url = new URL(window.location);
            if (searchQuery) {
                url.searchParams.set('search', searchQuery);
            } else {
                url.searchParams.delete('search');
            }
            window.history.pushState({}, '', url);
            
            // Show loading indicator
            threadsContainer.innerHTML = '<p class="loading-threads">Searching threads...</p>';
            
            // Perform search with AJAX
            fetch(`thread_handler.php?action=list&search=${encodeURIComponent(searchQuery)}`)
                .then(response => response.json())
                .then(data => {
                    // Clear the container
                    threadsContainer.innerHTML = '';
                    
                    if (data.success && data.threads.length > 0) {
                        // Update the last update time
                        lastUpdateTime = data.timestamp;
                        
                        // Display threads
                        data.threads.forEach(thread => {
                            const threadElement = document.createElement('div');
                            threadElement.className = 'thread-item';
                            threadElement.setAttribute('data-thread-id', thread.thread_id);
                            
                            threadElement.innerHTML = `
                                <h3><a href="thread.php?id=${thread.thread_id}">${thread.title}</a></h3>
                                <p class="thread-meta">
                                    Started by: ${thread.username} | 
                                    Date: ${formatDate(thread.created_at)} | 
                                    Comments: ${thread.comment_count}
                                </p>
                                <p class="thread-preview">${thread.content.substring(0, 150)}...</p>
                            `;
                            
                            threadsContainer.appendChild(threadElement);
                        });
                    } else {
                        // No threads found
                        threadsContainer.innerHTML = '<p>No threads found matching your search.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error searching threads:', error);
                    threadsContainer.innerHTML = '<p>Error searching threads. Please try again.</p>';
                });
        });
    }
}); 