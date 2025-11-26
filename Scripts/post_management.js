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
    
    if (file) {
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
    
    // Reset file input
    imageInput.value = '';
    
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
            
            // Handle image
            if (post.image_path) {
                const imageUploader = document.getElementById('image-uploader');
                const imagePreview = document.getElementById('image-preview');
                const previewImg = document.getElementById('preview-img');
                
                // Hide uploader content
                imageUploader.querySelector('i').style.display = 'none';
                imageUploader.querySelector('p').style.display = 'none';
                imageUploader.querySelector('span').style.display = 'none';
                
                // Show preview
                previewImg.src = post.image_path;
                imagePreview.style.display = 'block';
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
    
    try {
        const response = await fetch('post_operations.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            closeModal();
            location.reload(); // Refresh to show new post
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error creating post:', error);
        alert('Error creating post');
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
            location.reload(); // Refresh to remove archived post
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error archiving post:', error);
        alert('Error archiving post');
    }
}

async function archiveCurrentPost() {
    if (currentPostId) {
        await archivePost(currentPostId);
    }
}

async function filterPosts() {
    const yearFilter = document.getElementById('year-filter').value;
    const categoryFilter = document.getElementById('category-filter').value;
    
    // Build URL with filters
    let url = 'admin_post_management.php?';
    if (yearFilter) url += `year=${yearFilter}`;
    if (categoryFilter) url += `${yearFilter ? '&' : ''}category=${categoryFilter}`;
    
    // Redirect to filtered page
    window.location.href = url;
}

function editPost(postId) {
    openModal(postId);
} 