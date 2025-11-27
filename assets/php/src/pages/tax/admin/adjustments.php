<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the modal element
    const adjustmentModal = document.getElementById('manageAdjustments');
    
    // Add event listener to the modal
    adjustmentModal.addEventListener('show.bs.modal', function(event) {
        // Button that triggered the modal
        const button = event.relatedTarget;
        
        // Extract data from button's data attributes
        const categoryId = button.getAttribute('data-id');
        const categoryName = button.getAttribute('data-category-name');
        const categoryDescription = button.getAttribute('data-category-description');
        const categoryTypeId = button.getAttribute('data-type-id');
        
        // Get the form elements
        const idInput = adjustmentModal.querySelector('#adjustmentCategoryID');
        const nameInput = adjustmentModal.querySelector('#adjustmentCategoryName');
        const typeSelect = adjustmentModal.querySelector('#adjustmentTypeID');
        const categoryIdInput = adjustmentModal.querySelector('#adjustmentCategoryID');
        
        // Update modal title based on whether it's an edit or add
        const modalTitle = adjustmentModal.querySelector('.modal-title');
        modalTitle.textContent = categoryId ? 'Edit Tax Adjustment Category' : 'Add Tax Adjustment Category';
        
        // Populate the form if editing
        if (categoryId) {
            idInput.value = categoryId;
            nameInput.value = categoryName;
            typeSelect.value = categoryTypeId;
            categoryIdInput.value = categoryId;
            tinymce.init({
               selector: '#adjustmentCategoryDescription'
            });
            
            // Set TinyMCE content
            if (tinymce.get('adjustmentCategoryDescription')) {
                tinymce.get('adjustmentCategoryDescription').setContent(categoryDescription || '');
            }
        } else {
            // Reset form if adding new
            idInput.value = '';
            nameInput.value = '';
            typeSelect.value = '';
            
            // Clear TinyMCE content
            if (tinymce.get('adjustmentCategoryDescription')) {
                tinymce.get('adjustmentCategoryDescription').setContent('');
            }
        }
    });
    
    // Reset form when modal is hidden
    adjustmentModal.addEventListener('hidden.bs.modal', function() {
        const form = adjustmentModal.querySelector('form');
        form.reset();
        
        // Clear TinyMCE content
        if (tinymce.get('adjustmentCategoryDescription')) {
            tinymce.get('adjustmentCategoryDescription').setContent('');
        }
    });
});

// Add this after the existing modal event listeners
// delete modal handling
document.addEventListener('DOMContentLoaded', function() {
    // Existing code remains...

    // Delete modal handling
    const deleteModal = document.getElementById('deleteAdjustmentModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            // Button that triggered the modal
            const button = event.relatedTarget;
            
            // Extract data from button's data attributes
            const categoryId = button.getAttribute('data-id');
            const categoryName = button.getAttribute('data-category-name');
            
            // Update the modal's hidden input and text
            const categoryIdInput = deleteModal.querySelector('#deleteCategoryId');
            const categoryNameElement = deleteModal.querySelector('#deleteCategoryName');
            
            if (categoryIdInput) categoryIdInput.value = categoryId;
            if (categoryNameElement) categoryNameElement.textContent = categoryName;
        });

        // Reset form when delete modal is hidden
        deleteModal.addEventListener('hidden.bs.modal', function() {
            const form = deleteModal.querySelector('form');
            if (form) form.reset();
        });

        // Optional: Add form submission handling with fetch API
      //   const deleteForm = deleteModal.querySelector('form');
      //   if (deleteForm) {
      //       deleteForm.addEventListener('submit', function(e) {
      //           e.preventDefault();
                
      //           fetch(this.action, {
      //               method: 'POST',
      //               body: new FormData(this)
      //           })
      //           .then(response => response.json())
      //           .then(data => {
      //               if (data.success) {
      //                   // Hide the modal
      //                   bootstrap.Modal.getInstance(deleteModal).hide();
                        
      //                   // Refresh the page or remove the element from DOM
      //                   window.location.reload();
      //               } else {
      //                   // Show error message
      //                   Alert.error(data.message || 'Failed to delete category');
      //               }
      //           })
      //           .catch(error => {
      //               console.error('Error:', error);
      //               Alert.error('An error occurred while deleting the category');
      //           });
      //       });
      //   }
    }
});
</script>