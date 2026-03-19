// Global variables
let currentPostId = null;
let isEditMode = false;

// Initialize BroadcastChannel for real-time updates
let postUpdateChannel = null;
if (typeof BroadcastChannel !== 'undefined') {
    postUpdateChannel = new BroadcastChannel('post-updates');
}

// Global base path for local image resolution
const basePath = (function() {
    const isProduction = window.location.hostname.includes('ondigitalocean.app');
    if (isProduction) return '';
    const path = window.location.pathname;
    const parts = path.split('/');
    if (parts.length > 2) return '/' + parts[1];
    return '';
})();

// Helper function to broadcast post updates
function broadcastPostUpdate(type, category, postId = null) {
    if (postUpdateChannel) {
        postUpdateChannel.postMessage({
            type: type,
            category: category,
            postId: postId,
            timestamp: Date.now()
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    setCurrentDate();
});

function initializeEventListeners() {
    // Modal controls
    const modal = document.getElementById('post-modal');
    const closeBtn = document.querySelector('.close-btn');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    
    // Hide modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Form submission
    const postForm = document.getElementById('post-form');
    if (postForm) {
        postForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Image upload
    const imageUploader = document.getElementById('image-uploader');
    const imageInput = document.getElementById('image-upload');
    
    if (imageUploader && imageInput) {
        imageUploader.addEventListener('click', () => imageInput.click());
        imageInput.addEventListener('change', handleImageUpload);
        
        // Drag and drop functionality
        imageUploader.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUploader.classList.add('dragover');
        });
        
        imageUploader.addEventListener('dragleave', () => {
            imageUploader.classList.remove('dragover');
        });
        
        imageUploader.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUploader.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                handleImageUpload();
            }
        });
    }
    
    // Character counter
    const descriptionTextarea = document.getElementById('post-description');
    const charCountSpan = document.getElementById('char-count');
    
    if (descriptionTextarea && charCountSpan) {
        descriptionTextarea.addEventListener('input', () => {
            const currentLength = descriptionTextarea.value.length;
            charCountSpan.textContent = `${currentLength}/999`;
        });
    }
}

function setCurrentDate() {
    const dateInput = document.getElementById('post-date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
    }
}

function openModal(postId = null) {
    const modal = document.getElementById('post-modal');
    const modalTitle = document.getElementById('modal-title');
    const updateBtn = document.querySelector('.update-btn');
    const postBtn = document.querySelector('.post-btn');
    const archiveBtn = document.querySelector('.archive-btn');
    
    if (postId) {
        // Edit mode
        isEditMode = true;
        currentPostId = postId;
        modalTitle.textContent = 'Edit Post';
        updateBtn.style.display = 'inline-block';
        postBtn.style.display = 'none';
        archiveBtn.style.display = 'inline-block';
        
        // Load post data
        loadPostData(postId);
    } else {
        // Create mode
        isEditMode = false;
        currentPostId = null;
        modalTitle.textContent = 'Create New Post';
        updateBtn.style.display = 'none';
        postBtn.style.display = 'inline-block';
        archiveBtn.style.display = 'none';
        
        // Reset form
        resetForm();
    }
    
    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('post-modal');
    modal.style.display = 'none';
    resetForm();
}

function resetForm() {
    const form = document.getElementById('post-form');
    form.reset();
    setCurrentDate();
    
    // Reset image preview
    const imageUploader = document.getElementById('image-uploader');
    const imagePreview = document.getElementById('image-preview');
    const imageInput = document.getElementById('image-upload');
    
    // Reset file input
    if (imageInput) {
        imageInput.value = '';
    }
    
    // Reset remove image flag
    const removeImageFlag = document.getElementById('remove-image-flag');
    if (removeImageFlag) {
        removeImageFlag.value = '0';
    }
    
    // Hide preview
    if (imagePreview) {
        imagePreview.style.display = 'none';
    }
    
    // Show uploader content
    const uploaderIcon = imageUploader.querySelector('i');
    const uploaderText = imageUploader.querySelector('p');
    const uploaderSpan = imageUploader.querySelector('span');
    
    if (uploaderIcon) uploaderIcon.style.display = 'block';
    if (uploaderText) uploaderText.style.display = 'block';
    if (uploaderSpan) uploaderSpan.style.display = 'block';
    
    // Reset character count
    const charCountSpan = document.getElementById('char-count');
    if (charCountSpan) {
        charCountSpan.textContent = '0/999';
    }
}

function handleImageUpload() {
    const file = document.getElementById('image-upload').files[0];
    const imageUploader = document.getElementById('image-uploader');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeImageFlag = document.getElementById('remove-image-flag');
    
    if (file) {
        // Reset remove image flag when new image is uploaded
        if (removeImageFlag) {
            removeImageFlag.value = '0';
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            // Hide uploader content
            imageUploader.querySelector('i').style.display = 'none';
            imageUploader.querySelector('p').style.display = 'none';
            imageUploader.querySelector('span').style.display = 'none';
            
            // Show preview
            previewImg.src = e.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    const imageUploader = document.getElementById('image-uploader');
    const imagePreview = document.getElementById('image-preview');
    const imageInput = document.getElementById('image-upload');
    const removeImageFlag = document.getElementById('remove-image-flag');
    
    // Reset file input
    imageInput.value = '';
    
    // Set flag to indicate image should be removed
    if (removeImageFlag) {
        removeImageFlag.value = '1';
    }
    
    // Hide preview
    imagePreview.style.display = 'none';
    
    // Show uploader content
    imageUploader.querySelector('i').style.display = 'block';
    imageUploader.querySelector('p').style.display = 'block';
    imageUploader.querySelector('span').style.display = 'block';
}

async function loadPostData(postId) {
    try {
        const response = await fetch(`post_operations.php?t=${Date.now()}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache'
            },
            cache: 'no-store',
            body: `action=fetch_single&id=${postId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            const post = data.post;
            
            // Fill form fields
            document.getElementById('post-title').value = post.title;
            document.getElementById('post-date').value = post.post_date;
            document.getElementById('post-category').value = post.category;
            document.getElementById('post-description').value = post.description;
            
            // Handle image - check for both null and empty string
            if (post.image_path && post.image_path.trim() !== '') {
                const imageUploader = document.getElementById('image-uploader');
                const imagePreview = document.getElementById('image-preview');
                const previewImg = document.getElementById('preview-img');
                
                // Hide uploader content
                imageUploader.querySelector('i').style.display = 'none';
                imageUploader.querySelector('p').style.display = 'none';
                imageUploader.querySelector('span').style.display = 'none';
                
                // Show preview - normalize path
                let imgSrc = post.image_path.trim();
                const isFullUrl = imgSrc.match(/^(https?:\/\/|data:)/);
                
                if (!isFullUrl) {
                    // Prepend base path if it's a local path
                    imgSrc = (window.siteBasePath || basePath || '') + '/' + imgSrc.replace(/^\//, '');
                }
                
                // Add error handler for cloud fallback
                previewImg.dataset.tries = '0';
                previewImg.onerror = function() {
                    const tries = parseInt(this.dataset.tries || '0');
                    if (tries === 0 && window.spacesBaseUrl && !isFullUrl) {
                        const fileName = post.image_path.trim().split('/').pop();
                        this.src = window.spacesBaseUrl.replace(/\/$/, '') + '/' + fileName;
                        this.dataset.tries = '1';
                    } else {
                        this.style.display = 'none';
                        // Show placeholder in background
                        imagePreview.style.backgroundImage = "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%232d2d2d' width='400' height='300'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23ffffff' font-family='Arial' font-size='18'%3ENo Image%3C/text%3E%3C/svg%3E\")";
                        imagePreview.style.backgroundSize = 'cover';
                    }
                };
                
                previewImg.src = imgSrc;
                previewImg.style.display = 'block';
                imagePreview.style.display = 'block';
                imagePreview.style.backgroundImage = 'none';
                
                // Reset remove image flag since image exists
                const removeImageFlag = document.getElementById('remove-image-flag');
                if (removeImageFlag) {
                    removeImageFlag.value = '0';
                }
            } else {
                // No image - ensure preview is hidden and flag is reset
                const imagePreview = document.getElementById('image-preview');
                if (imagePreview) {
                    imagePreview.style.display = 'none';
                }
                const removeImageFlag = document.getElementById('remove-image-flag');
                if (removeImageFlag) {
                    removeImageFlag.value = '0';
                }
            }
            
            // Update character count
            const charCountSpan = document.getElementById('char-count');
            charCountSpan.textContent = `${post.description.length}/999`;
        }
    } catch (error) {
        console.error('Error loading post data:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error loading post data',
            icon: 'error',
            background: '#1a1a1a',
            color: '#fff'
        });
    }
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'create');
    
    // Debug: Check if image file is included
    const imageFile = document.getElementById('image-upload').files[0];
    if (imageFile) {
        console.log('Image file selected:', imageFile.name, 'Size:', imageFile.size, 'Type:', imageFile.type);
    } else {
        console.log('No image file selected');
    }
    
    try {
        const response = await fetch(`post_operations.php?t=${Date.now()}`, {
            method: 'POST',
            headers: {
                'Cache-Control': 'no-cache'
            },
            cache: 'no-store',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Log image path for debugging
            if (data.image_path) {
                console.log('Image uploaded successfully:', data.image_path);
            } else {
                console.log('Post created but no image path returned');
            }
            
            // Get category from form data
            const category = formData.get('category');
            
            // Broadcast post creation to other tabs
            if (category) {
                broadcastPostUpdate('post-created', category);
            }
            
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#00ff6a'
            }).then(() => {
                closeModal();
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                background: '#1a1a1a',
                color: '#fff'
            });
        }
    } catch (error) {
        console.error('Error creating post:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error creating post: ' + error.message,
            icon: 'error',
            background: '#1a1a1a',
            color: '#fff'
        });
    }
}

async function updatePost() {
    const formData = new FormData(document.getElementById('post-form'));
    formData.append('action', 'update');
    formData.append('id', currentPostId);
    
    try {
        const response = await fetch('post_operations.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Get category from form data
            const category = formData.get('category');
            
            // Broadcast post update to other tabs
            if (category && currentPostId) {
                broadcastPostUpdate('post-updated', category, currentPostId);
            }
            
            Swal.fire({
                title: 'Updated!',
                text: data.message,
                icon: 'success',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#00ff6a'
            }).then(() => {
                closeModal();
                location.reload(); 
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                background: '#1a1a1a',
                color: '#fff'
            });
        }
    } catch (error) {
        console.error('Error updating post:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error updating post',
            icon: 'error',
            background: '#1a1a1a',
            color: '#fff'
        });
    }
}

async function archivePost(postId) {
    const result = await Swal.fire({
        title: 'Archive Post?',
        text: "Are you sure you want to archive this post?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00ff6a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!',
        background: '#1a1a1a',
        color: '#fff'
    });

    if (!result.isConfirmed) {
        return;
    }
    
    try {
        const response = await fetch(`post_operations.php?t=${Date.now()}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache'
            },
            cache: 'no-store',
            body: `action=archive&id=${postId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Get category from response or from the post card element before removing it
            let category = data.category || null;
            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            
            if (!category && postCard) {
                // Try to get category from the post tag element
                const postTag = postCard.querySelector('.post-tag');
                if (postTag) {
                    const tagText = postTag.textContent.trim().toLowerCase();
                    if (tagText === 'achievement') {
                        category = 'achievement';
                    } else if (tagText === 'event') {
                        category = 'event';
                    } else if (tagText === 'achievement/event') {
                        category = 'achievement_event';
                    }
                }
            }
            
            // If we still couldn't get category, try to fetch it from server
            if (!category) {
                try {
                    const fetchResponse = await fetch(`post_operations.php?t=${Date.now()}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Cache-Control': 'no-cache'
                        },
                        cache: 'no-store',
                        body: `action=fetch_single&id=${postId}`
                    });
                    const fetchData = await fetchResponse.json();
                    if (fetchData.success && fetchData.post) {
                        category = fetchData.post.category;
                    }
                } catch (err) {
                    console.error('Error fetching post category for broadcast:', err);
                }
            }
            
            // Broadcast post archive to other tabs (webpage.php)
            if (category) {
                broadcastPostUpdate('post-archived', category, postId);
            }
            
            // Immediately remove the post card from DOM
            if (postCard) {
                postCard.style.transition = 'opacity 0.3s, transform 0.3s';
                postCard.style.opacity = '0';
                postCard.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    postCard.remove();
                    
                    // Check if grid is empty and show message
                    const postGrid = document.getElementById('post-grid');
                    if (postGrid && postGrid.querySelectorAll('.post-card').length === 0) {
                        // Reload to show empty state message
                        location.reload();
                    }
                }, 300);
            } else {
                // If card not found, reload to ensure UI matches database
                location.reload();
            }
            
            Swal.fire({
                title: 'Archived!',
                text: data.message,
                icon: 'success',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#00ff6a'
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                background: '#1a1a1a',
                color: '#fff'
            });
            // Reload even on error to ensure UI matches database state
            location.reload();
        }
    } catch (error) {
        console.error('Error archiving post:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error archiving post: ' + error.message,
            icon: 'error',
            background: '#1a1a1a',
            color: '#fff'
        });
    }
}

async function archiveCurrentPost() {
    if (!currentPostId) {
        Swal.fire({
            title: 'Note',
            text: 'No post selected to archive',
            icon: 'info',
            background: '#1a1a1a',
            color: '#fff'
        });
        return;
    }
    
    // First, save any pending changes (like image removal) before archiving
    const removeImageFlag = document.getElementById('remove-image-flag');
    const hasPendingChanges = removeImageFlag && removeImageFlag.value === '1';
    
    if (hasPendingChanges) {
        // Save changes first, then archive
        try {
            await updatePost();
            // Wait a moment for the update to complete
            await new Promise(resolve => setTimeout(resolve, 500));
        } catch (error) {
            console.error('Error saving changes before archiving:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error saving changes. Please save manually before archiving.',
                icon: 'error',
                background: '#1a1a1a',
                color: '#fff'
            });
            return;
        }
    }
    
    // Now archive the post
    await archivePost(currentPostId);
}

// Delete post permanently (DELETE FROM database)
async function deletePost(postId) {
    const result = await Swal.fire({
        title: 'Delete Permanently?',
        text: "Are you sure you want to PERMANENTLY DELETE this post? This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00ff6a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        background: '#1a1a1a',
        color: '#fff'
    });

    if (!result.isConfirmed) {
        return;
    }
    
    try {
        const response = await fetch(`post_operations.php?t=${Date.now()}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache'
            },
            cache: 'no-store',
            body: `action=delete&id=${postId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Get category from response or from the post card element before removing it
            let category = data.category || null;
            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            
            if (!category && postCard) {
                // Try to get category from the post tag element
                const postTag = postCard.querySelector('.post-tag');
                if (postTag) {
                    const tagText = postTag.textContent.trim().toLowerCase();
                    if (tagText === 'achievement') {
                        category = 'achievement';
                    } else if (tagText === 'event') {
                        category = 'event';
                    } else if (tagText === 'achievement/event') {
                        category = 'achievement_event';
                    }
                }
            }
            
            // Broadcast post deletion to other tabs (webpage.php)
            if (category) {
                broadcastPostUpdate('post-deleted', category, postId);
            }
            
            // Immediately remove the post card from DOM
            if (postCard) {
                postCard.style.transition = 'opacity 0.3s, transform 0.3s';
                postCard.style.opacity = '0';
                postCard.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    postCard.remove();
                    
                    // Check if grid is empty and show message
                    const postGrid = document.getElementById('post-grid');
                    if (postGrid && postGrid.querySelectorAll('.post-card').length === 0) {
                        // Reload to show empty state message
                        location.reload();
                    }
                }, 300);
            } else {
                // If card not found, reload to ensure UI matches database
                location.reload();
            }
            
            Swal.fire({
                title: 'Deleted!',
                text: data.message,
                icon: 'success',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#00ff6a'
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                background: '#1a1a1a',
                color: '#fff'
            });
            // Reload even on error to ensure UI matches database state
            location.reload();
        }
    } catch (error) {
        console.error('Error deleting post:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error deleting post: ' + error.message,
            icon: 'error',
            background: '#1a1a1a',
            color: '#fff'
        });
    }
}

async function filterPosts() {
    const yearFilter = document.getElementById('year-filter').value;
    const categoryFilter = document.getElementById('category-filter').value;
    
    // Build URL with filters
    const params = [];
    // Only add year parameter if a specific year is selected (not empty "ALL YEARS")
    if (yearFilter && yearFilter.trim() !== '') {
        params.push(`year=${yearFilter}`);
    }
    if (categoryFilter) params.push(`category=${categoryFilter}`);
    
    const url = 'post_management.php' + (params.length > 0 ? '?' + params.join('&') : '');
    
    // Redirect to filtered page
    window.location.href = url;
}

function editPost(postId) {
    openModal(postId);
} 