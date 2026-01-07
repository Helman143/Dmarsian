// Global variables
let currentPostId = null;
let isEditMode = false;

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
            charCountSpan.textContent = `${currentLength}/200`;
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
        charCountSpan.textContent = '0/200';
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
        const response = await fetch('post_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
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
                if (!imgSrc.match(/^(https?:\/\/|\/)/)) {
                    imgSrc = '/' + imgSrc.replace(/^\//, '');
                }
                previewImg.src = imgSrc;
                imagePreview.style.display = 'block';
                
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
            charCountSpan.textContent = `${post.description.length}/200`;
        }
    } catch (error) {
        console.error('Error loading post data:', error);
        alert('Error loading post data');
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
        const response = await fetch('post_operations.php', {
            method: 'POST',
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
            alert(data.message);
            closeModal();
            // Use cache-busting reload to ensure fresh data
            location.reload(true);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error creating post:', error);
        alert('Error creating post: ' + error.message);
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
            alert(data.message);
            closeModal();
            location.reload(); // Refresh to show updated post
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error updating post:', error);
        alert('Error updating post');
    }
}

async function archivePost(postId) {
    if (!confirm('Are you sure you want to archive this post?')) {
        return;
    }
    
    try {
        const response = await fetch('post_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=archive&id=${postId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            // Remove the post card immediately from the DOM
            const postCard = document.querySelector(`[data-post-id="${postId}"]`);
            if (postCard) {
                postCard.style.transition = 'opacity 0.3s';
                postCard.style.opacity = '0';
                setTimeout(() => {
                    postCard.remove();
                    // Check if grid is empty and show message
                    const postGrid = document.getElementById('post-grid');
                    if (postGrid && postGrid.querySelectorAll('.post-card').length === 0) {
                        location.reload(true); // Force reload with cache bypass
                    }
                }, 300);
            } else {
                // Force reload with cache bypass to ensure fresh data
                location.reload(true);
            }
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error archiving post:', error);
        alert('Error archiving post');
    }
}

async function archiveCurrentPost() {
    if (!currentPostId) {
        alert('No post selected to archive');
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
            alert('Error saving changes. Please save manually before archiving.');
            return;
        }
    }
    
    // Now archive the post
    await archivePost(currentPostId);
}

async function filterPosts() {
    const yearFilter = document.getElementById('year-filter').value;
    const categoryFilter = document.getElementById('category-filter').value;
    
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'post_management.js:420',message:'filterPosts called',data:{yearFilter,categoryFilter},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H3'})}).catch(()=>{});
    // #endregion
    
    // Build URL with filters
    const params = [];
    if (yearFilter) params.push(`year=${yearFilter}`);
    if (categoryFilter) params.push(`category=${categoryFilter}`);
    
    const url = 'post_management.php?' + params.join('&');
    
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'post_management.js:430',message:'Redirecting with filters',data:{url,params},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H3'})}).catch(()=>{});
    // #endregion
    
    // Redirect to filtered page
    window.location.href = url;
}

function editPost(postId) {
    openModal(postId);
} 