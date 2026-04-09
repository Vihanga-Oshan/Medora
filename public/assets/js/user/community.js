document.addEventListener('DOMContentLoaded', function() {
    console.log('Community script loaded');
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Community functionality
    let currentFilter = 'all';
    let currentPage = 1;
    let isLoading = false;
    let pendingDeletePostId = null;

    // Initialize posting functionality first
    initializePostModal();
    
    // Initialize category tags
    initializeCategoryTags();
    
    // Initialize post menu dropdowns
    initializePostMenus();
    
    // Initialize delete confirmation modal
    initializeDeleteConfirmationModal();

    // Post creation functionality
    const postButton = document.querySelector('.community-content-header .btn-primary');
    console.log('Post button found:', postButton);
    
    if (postButton) {
        postButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Post button clicked - showing modal');
            showCreatePostModal();
        });
    } else {
        console.error('Post button not found!');
    }

    // Like functionality for existing posts
    setupLikeFunctionality();

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.post-menu-container')) {
            closeAllPostMenus();
        }
    });

    function showCreatePostModal() {
        console.log('showCreatePostModal called');
        
        // Reset form to create mode first
        resetPostForm();
        
        const modal = document.getElementById('postModalOverlay');
        console.log('Modal element:', modal);
        
        if (modal) {
            modal.style.display = 'flex';
            modal.offsetHeight; // Force reflow
            modal.classList.add('show');
            
            const titleInput = document.getElementById('postTitle');
            if (titleInput) {
                setTimeout(() => titleInput.focus(), 100);
            }
        } else {
            console.error('Modal element not found!');
        }
    }

    function initializePostModal() {
        console.log('Initializing post modal');
        
        const modal = document.getElementById('postModalOverlay');
        const closeBtn = document.getElementById('postModalClose');
        const cancelBtn = document.getElementById('cancelPost');
        const submitBtn = document.getElementById('submitPost');
        const contentInput = document.getElementById('postContentInput');
        const titleInput = document.getElementById('postTitle');
        const postForm = document.getElementById('postForm');

        // Initialize file upload functionality
        initializeFileUpload();

        // Close modal functions
        const closeModal = () => {
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
                resetPostForm();
            }
        };

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        }

        // Enable/disable submit button based on content
        function updateSubmitButton() {
            const hasContent = contentInput && contentInput.value.trim().length > 0;
            if (submitBtn) {
                submitBtn.disabled = !hasContent;
            }
        }

        if (contentInput) {
            contentInput.addEventListener('input', updateSubmitButton);
        }

        // REMOVE FORM SUBMISSION HANDLER - Let form submit naturally
        // if (postForm) {
        //     postForm.addEventListener('submit', handlePostSubmission);
        // }
    }

    function initializeFileUpload() {
        const fileInput = document.getElementById('postImage');
        const fileUploadBtn = document.querySelector('.file-upload-btn');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const imagePreview = document.getElementById('imagePreview');
        const removeFileBtn = document.getElementById('removeFile');

        if (!fileInput || !fileUploadBtn) return;

        // File input change handler
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                displayFileInfo(file);
            }
        });

        // Remove file handler
        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearFileSelection();
            });
        }

        // Drag and drop functionality
        fileUploadBtn.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        fileUploadBtn.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });

        fileUploadBtn.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                fileInput.files = files;
                displayFileInfo(files[0]);
            }
        });

        function displayFileInfo(file) {
            // Update file name and size
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);

            // Show image preview if it's an image
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }

            // Show file info and update button state
            fileInfo.style.display = 'block';
            fileUploadBtn.classList.add('file-selected');
            fileUploadBtn.querySelector('.upload-text').textContent = 'Change Image';
        }

        function clearFileSelection() {
            fileInput.value = '';
            fileInfo.style.display = 'none';
            fileUploadBtn.classList.remove('file-selected');
            fileUploadBtn.querySelector('.upload-text').textContent = 'Choose Image';
            imagePreview.src = '';
            imagePreview.style.display = 'none';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }

    function initializeCategoryTags() {
        const categoryTags = document.querySelectorAll('.category-tag');
        const selectedCategoryInput = document.getElementById('selectedCategory');

        categoryTags.forEach(tag => {
            tag.addEventListener('click', function() {
                // Remove active class from all tags
                categoryTags.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tag
                this.classList.add('active');
                
                // Update hidden input
                const category = this.getAttribute('data-category');
                if (selectedCategoryInput) {
                    selectedCategoryInput.value = category;
                }
            });
        });
    }

    function initializePostMenus() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.post-menu-btn')) {
                e.preventDefault();
                e.stopPropagation();
                
                const menuBtn = e.target.closest('.post-menu-btn');
                const postId = menuBtn.getAttribute('data-post-id');
                const dropdown = document.getElementById(`postMenu-${postId}`);
                
                if (dropdown) {
                    // Close all other dropdowns
                    closeAllPostMenus();
                    
                    // Toggle current dropdown
                    dropdown.classList.toggle('show');
                }
            }
            
            // Handle menu option clicks
            if (e.target.closest('.menu-option')) {
                e.preventDefault();
                const menuOption = e.target.closest('.menu-option');
                const postId = menuOption.getAttribute('data-post-id');
                
                if (menuOption.classList.contains('edit-post')) {
                    handleEditPost(postId);
                } else if (menuOption.classList.contains('delete-post-btn')) {
                    handleDeletePost(postId);
                } else if (menuOption.classList.contains('report-post')) {
                    handleReportPost(postId);
                } else if (menuOption.classList.contains('save-post')) {
                    handleSavePost(postId);
                } else if (menuOption.classList.contains('share-post')) {
                    handleSharePost(postId);
                }
                
                closeAllPostMenus();
            }
        });
    }

    function closeAllPostMenus() {
        const allDropdowns = document.querySelectorAll('.post-menu-dropdown');
        allDropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }

    function showCreatePostModal() {
        console.log('showCreatePostModal called');
        const modal = document.getElementById('postModalOverlay');
        console.log('Modal element:', modal);
        
        if (modal) {
            modal.style.display = 'flex';
            // Force reflow
            modal.offsetHeight;
            modal.classList.add('show');
            
            const titleInput = document.getElementById('postTitle');
            if (titleInput) {
                setTimeout(() => titleInput.focus(), 100);
            }
        } else {
            console.error('Modal element not found!');
        }
    }

    function initializeDeleteConfirmationModal() {
        const modal = document.getElementById('deleteConfirmationModal');
        const cancelBtn = document.getElementById('cancelDelete');
        const confirmBtn = document.getElementById('confirmDelete');

        if (!modal || !cancelBtn || !confirmBtn) {
            console.warn('Delete confirmation modal elements not found');
            return;
        }

        // Cancel button
        cancelBtn.addEventListener('click', function() {
            hideDeleteConfirmationModal();
        });

        // Confirm button
        confirmBtn.addEventListener('click', function() {
            if (pendingDeletePostId) {
                executeDeletePost(pendingDeletePostId);
            }
        });

        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideDeleteConfirmationModal();
            }
        });

        // ESC key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display !== 'none') {
                hideDeleteConfirmationModal();
            }
        });
    }

    function showDeleteConfirmationModal(postId) {
        pendingDeletePostId = postId;
        const modal = document.getElementById('deleteConfirmationModal');
        
        if (modal) {
            modal.style.display = 'flex';
            // Force reflow
            modal.offsetHeight;
            modal.classList.add('show');
            
            // Initialize Lucide icons for the modal
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            // Focus on cancel button for accessibility
            const cancelBtn = document.getElementById('cancelDelete');
            if (cancelBtn) {
                setTimeout(() => cancelBtn.focus(), 100);
            }
        }
    }

    function hideDeleteConfirmationModal() {
        const modal = document.getElementById('deleteConfirmationModal');
        const confirmBtn = document.getElementById('confirmDelete');
        
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        
        // Reset button state
        if (confirmBtn) {
            confirmBtn.classList.remove('loading');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i data-lucide="trash-2" class="btn-icon"></i>Delete Post';
            // Re-initialize icons after innerHTML change
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        pendingDeletePostId = null;
    }

    function executeDeletePost(postId) {
        const confirmBtn = document.getElementById('confirmDelete');
        
        // Show loading state
        if (confirmBtn) {
            confirmBtn.classList.add('loading');
            confirmBtn.disabled = true;
        }

        // Find and submit the hidden form for this post
        const deleteForm = document.getElementById(`deleteForm-${postId}`);
        if (deleteForm) {
            deleteForm.submit();
        } else {
            console.error('Delete form not found for post:', postId);
            hideDeleteConfirmationModal();
            alert('Delete form not found');
        }
    }

    function handleDeletePost(postId) {
        showDeleteConfirmationModal(postId);
    }

    function handleEditPost(postId) {
        console.log('Edit post:', postId);
        
        // Get post data from hidden div
        const editDataDiv = document.getElementById(`editData-${postId}`);
        if (!editDataDiv) {
            alert('Post data not found');
            return;
        }
        
        // Extract post data
        const postData = {
            postId: editDataDiv.getAttribute('data-post-id'),
            content: editDataDiv.getAttribute('data-content'),
            title: editDataDiv.getAttribute('data-title'),
            postType: editDataDiv.getAttribute('data-post-type'),
            anonymous: editDataDiv.getAttribute('data-anonymous') === 'true',
            imageUrl: editDataDiv.getAttribute('data-image-url')
        };
        
        showEditPostModal(postData);
    }

    function showEditPostModal(postData) {
        // Update modal title and subtitle
        const modalTitle = document.getElementById('postModalTitle');
        const modalSubtitle = document.getElementById('postModalSubtitle');
        const submitBtn = document.getElementById('submitPost');
        
        if (modalTitle) modalTitle.textContent = 'Edit Post';
        if (modalSubtitle) modalSubtitle.textContent = 'Update your post content.';
        if (submitBtn) submitBtn.textContent = 'Update Post';
        
        // Set form action for editing
        const form = document.getElementById('postForm');
        const editPostIdInput = document.getElementById('editPostId');
        const isEditModeInput = document.getElementById('isEditMode');
        
        if (form) form.action = form.action.replace('/user/community/posts', '/user/community/posts/edit');
        if (editPostIdInput) editPostIdInput.value = postData.postId;
        if (isEditModeInput) isEditModeInput.value = 'true';
        
        // Populate form fields
        const titleInput = document.getElementById('postTitle');
        const contentInput = document.getElementById('postContentInput');
        const anonymousCheckbox = document.getElementById('postAnonymous');
        
        if (titleInput) titleInput.value = postData.title || '';
        if (contentInput) contentInput.value = postData.content || '';
        if (anonymousCheckbox) anonymousCheckbox.checked = postData.anonymous;
        
        // Set category/post type
        const categoryTags = document.querySelectorAll('.category-tag');
        categoryTags.forEach(tag => {
            tag.classList.remove('active');
            const radio = document.getElementById(tag.getAttribute('data-category'));
            if (radio) radio.checked = false;
        });
        
        if (postData.postType) {
            const targetTag = document.querySelector(`[data-category="${postData.postType}"]`);
            const targetRadio = document.getElementById(postData.postType);
            if (targetTag) targetTag.classList.add('active');
            if (targetRadio) targetRadio.checked = true;
        }
        
        // Show current image if exists
        const currentImagePreview = document.getElementById('currentImagePreview');
        const currentImage = document.getElementById('currentImage');
        
        if (postData.imageUrl && postData.imageUrl.trim() !== '' && postData.imageUrl !== 'null') {
            if (currentImagePreview) currentImagePreview.style.display = 'block';
            if (currentImage) currentImage.src = postData.imageUrl;
        } else {
            if (currentImagePreview) currentImagePreview.style.display = 'none';
        }
        
        // Show the modal
        const modal = document.getElementById('postModalOverlay');
        if (modal) {
            modal.style.display = 'flex';
            modal.offsetHeight;
            modal.classList.add('show');
            
            // Focus on content input
            setTimeout(() => {
                if (contentInput) {
                    contentInput.focus();
                    contentInput.setSelectionRange(contentInput.value.length, contentInput.value.length);
                }
            }, 100);
        }
    }

    function resetPostForm() {
        const form = document.getElementById('postForm');
        if (form) {
            form.reset();
            // Reset form action to create
            const baseAction = form.action.replace('/user/community/posts/edit', '/user/community/posts');
            form.action = baseAction;
        }
        
        // Reset modal to create mode
        const modalTitle = document.getElementById('postModalTitle');
        const modalSubtitle = document.getElementById('postModalSubtitle');
        const submitBtn = document.getElementById('submitPost');
        const editPostIdInput = document.getElementById('editPostId');
        const isEditModeInput = document.getElementById('isEditMode');
        
        if (modalTitle) modalTitle.textContent = 'Create New Post';
        if (modalSubtitle) modalSubtitle.textContent = 'Share your thoughts with the community.';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.value = 'Post'; // Use .value for input elements
        }
        if (editPostIdInput) editPostIdInput.value = '';
        if (isEditModeInput) isEditModeInput.value = 'false';
        
        // Hide current image preview
        const currentImagePreview = document.getElementById('currentImagePreview');
        if (currentImagePreview) currentImagePreview.style.display = 'none';
        
        // Reset file upload
        const fileInfo = document.getElementById('fileInfo');
        const fileUploadBtn = document.querySelector('.file-upload-btn');
        const imagePreview = document.getElementById('imagePreview');
        
        if (fileInfo) fileInfo.style.display = 'none';
        if (fileUploadBtn) {
            fileUploadBtn.classList.remove('file-selected');
            const uploadText = fileUploadBtn.querySelector('.upload-text');
            if (uploadText) uploadText.textContent = 'Choose Image';
        }
        if (imagePreview) {
            imagePreview.src = '';
            imagePreview.style.display = 'none';
        }
        
        // Reset category selection to first one (General)
        const categoryTags = document.querySelectorAll('.category-tag');
        const selectedCategoryInput = document.getElementById('selectedCategory');
        
        categoryTags.forEach(tag => tag.classList.remove('active'));
        if (categoryTags.length > 0) {
            categoryTags[0].classList.add('active');
            const firstRadio = categoryTags[0].getAttribute('data-category');
            const radio = document.getElementById(firstRadio);
            if (radio) radio.checked = true;
            if (selectedCategoryInput) {
                selectedCategoryInput.value = 'general';
            }
        }
    }

    function handlePostSubmission(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const isEditMode = document.getElementById('isEditMode').value === 'true';
        
        // Show loading state
        const submitBtn = document.getElementById('submitPost');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = isEditMode ? 'Updating...' : 'Posting...';
        }

        console.log(isEditMode ? 'Updating post...' : 'Creating post...');

        // Submit to backend (form action is already set correctly)
        fetch(e.target.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                // Server sent a redirect (success case)
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                console.log(isEditMode ? 'Post updated successfully!' : 'Post created successfully!');
                const modal = document.getElementById('postModalOverlay');
                if (modal) {
                    modal.classList.remove('show');
                    setTimeout(() => modal.style.display = 'none', 300);
                }
                resetPostForm();
                window.location.reload();
            } else if (data) {
                console.error('Failed to ' + (isEditMode ? 'update' : 'create') + ' post:', data.message);
            }
        })
        .catch(error => {
            console.error('Error ' + (isEditMode ? 'updating' : 'creating') + ' post:', error);
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = isEditMode ? 'Update Post' : 'Post';
            }
        });
    }

    // Update the createPostElement function to include edit data
    function createPostElement(post) {
        const postDiv = document.createElement('div');
        postDiv.className = 'community-post';
        postDiv.setAttribute('data-post-id', post.postId);
        
        const currentUserId = window.currentUser ? window.currentUser.userId : null;
        const isOwner = currentUserId && currentUserId === post.userId;
        
        // Create owner-specific menu options
        const ownerOptions = isOwner ? `
            <button class="menu-option edit-post" data-post-id="${post.postId}">
                <i data-lucide="edit-2"></i>
                <span>Edit Post</span>
            </button>
            <button class="menu-option delete-post-btn" data-post-id="${post.postId}">
                <i data-lucide="trash-2"></i>
                <span>Delete Post</span>
            </button>
            <div class="menu-divider"></div>
        ` : '';
        
        const ownerForms = isOwner ? `
            <form id="deleteForm-${post.postId}" 
                  action="/user/community/posts/delete" 
                  method="post" 
                  style="display: none;">
                <input type="hidden" name="postId" value="${post.postId}" />
            </form>
            
            <div id="editData-${post.postId}" style="display: none;"
                 data-post-id="${post.postId}"
                 data-content="${post.content || ''}"
                 data-title="${post.title || ''}"
                 data-post-type="${post.postType || 'general'}"
                 data-anonymous="${post.anonymous || false}"
                 data-image-url="${post.imageUrl || ''}">
            </div>
        ` : '';
        
        postDiv.innerHTML = `
            <div class="post-header">
                <div class="post-author">
                    <img src="${post.profilePictureUrl || '/assets/img/avatar.png'}" 
                         alt="${post.displayName || 'User'}" 
                         class="author-avatar">
                    <div class="author-info">
                        <h4 class="author-name">${post.anonymous ? 'Anonymous User' : (post.displayName || post.username)}</h4>
                        <span class="post-time">${formatDate(post.createdAt)}</span>
                    </div>
                </div>
                <div class="post-menu-container">
                    <button class="post-menu-btn" data-post-id="${post.postId}">
                        <i data-lucide="more-horizontal" class="menu-icon"></i>
                    </button>
                    <div class="post-menu-dropdown" id="postMenu-${post.postId}">
                        ${ownerOptions}
                        <button class="menu-option report-post" data-post-id="${post.postId}">
                            <i data-lucide="flag"></i>
                            <span>Report Post</span>
                        </button>
                        <button class="menu-option save-post" data-post-id="${post.postId}">
                            <i data-lucide="bookmark"></i>
                            <span>Save Post</span>
                        </button>
                        <button class="menu-option share-post" data-post-id="${post.postId}">
                            <i data-lucide="share"></i>
                            <span>Share Post</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="post-content">
                <p class="post-text">${post.content}</p>
                ${post.imageUrl ? `<div class="post-image"><img src="${post.imageUrl}" class="content-image" alt="Post image"></div>` : ''}
            </div>
            <div class="post-actions">
                <button class="action-btn like-btn ${post.active ? 'liked' : ''}" data-post-id="${post.postId}">
                    <i data-lucide="heart" class="action-icon ${post.active ? 'filled' : ''}"></i>
                    <span class="action-count">${post.likesCount || 0}</span>
                    <span class="action-text">Like</span>
                </button>
                <button class="action-btn comment-btn" data-post-id="${post.postId}">
                    <i data-lucide="message-circle" class="action-icon"></i>
                    <span class="action-count">${post.commentsCount || 0}</span>
                    <span class="action-text">Comment</span>
                </button>
                <button class="action-btn share-btn" data-post-id="${post.postId}">
                    <i data-lucide="share-2" class="action-icon"></i>
                    <span class="action-count">${post.sharesCount || 0}</span>
                    <span class="action-text">Share</span>
                </button>
            </div>
            ${ownerForms}
        `;
        
        // Initialize Lucide icons for this post
        setTimeout(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 0);
        
        return postDiv;
    }

    function setupLikeFunctionality() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.like-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.like-btn');
                const postId = btn.getAttribute('data-post-id');
                toggleLike(postId, btn);
            }
        });
    }

    function toggleLike(postId, btn) {
        fetch('/user/community/posts/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ postId: Number(postId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('liked', data.liked);
                const count = btn.querySelector('.action-count');
                
                if (count) {
                    const currentCount = parseInt(count.textContent) || 0;
                    count.textContent = data.liked ? currentCount + 1 : Math.max(0, currentCount - 1);
                }
            }
        })
        .catch(error => {
            console.error('Error toggling like:', error);
        });
    }

    function loadMorePosts() {
        if (!isLoading) {
            currentPage++;
            loadPosts(false);
        }
    }

    function showEmptyState() {
        const container = document.getElementById('postsContainer');
        if (container) {
            container.innerHTML = `
                <div class="empty-posts">
                    <h3>No posts yet</h3>
                    <p>Be the first to share something with the community!</p>
                </div>
            `;
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
        
        if (diffInHours < 1) return 'Just now';
        if (diffInHours === 1) return '1 hour ago';
        if (diffInHours < 24) return `${diffInHours} hours ago`;
        
        const diffInDays = Math.floor(diffInHours / 24);
        return diffInDays === 1 ? '1 day ago' : `${diffInDays} days ago`;
    }

    function handlePostSubmission(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const isEditMode = document.getElementById('isEditMode').value === 'true';
        
        // Show loading state
        const submitBtn = document.getElementById('submitPost');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = isEditMode ? 'Updating...' : 'Posting...';
        }

        console.log(isEditMode ? 'Updating post...' : 'Creating post...');

        // Submit to backend (form action is already set correctly)
        fetch(e.target.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                // Server sent a redirect (success case)
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                console.log(isEditMode ? 'Post updated successfully!' : 'Post created successfully!');
                const modal = document.getElementById('postModalOverlay');
                if (modal) {
                    modal.classList.remove('show');
                    setTimeout(() => modal.style.display = 'none', 300);
                }
                resetPostForm();
                window.location.reload();
            } else if (data) {
                console.error('Failed to ' + (isEditMode ? 'update' : 'create') + ' post:', data.message);
            }
        })
        .catch(error => {
            console.error('Error ' + (isEditMode ? 'updating' : 'creating') + ' post:', error);
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = isEditMode ? 'Update Post' : 'Post';
            }
        });
    }

    // Add missing stub functions to prevent errors
    function handleReportPost(postId) {
        console.log('Report post:', postId);
        alert('Report functionality not implemented yet');
    }

    function handleSavePost(postId) {
        console.log('Save post:', postId);
        alert('Save functionality not implemented yet');
    }

    function handleSharePost(postId) {
        console.log('Share post:', postId);
        alert('Share functionality not implemented yet');
    }

    function loadPosts(reset) {
        console.log('loadPosts called, reset:', reset);
        // This function can be empty for now since posts are loaded server-side
    }
});
