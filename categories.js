document.addEventListener('DOMContentLoaded', function() {
    const showFormButton = document.getElementById('showAddCategoryForm');
    const addCategoryForm = document.getElementById('addCategoryForm');
    const cancelButton = document.getElementById('cancelAddCategory');
    
    if (showFormButton && addCategoryForm && cancelButton) {
        showFormButton.addEventListener('click', function() {
            addCategoryForm.style.display = 'block';
            showFormButton.style.display = 'none';
        });
        
        cancelButton.addEventListener('click', function() {
            addCategoryForm.style.display = 'none';
            showFormButton.style.display = 'inline-block';
        });
    }

    const deleteButtons = document.querySelectorAll('a[href*="delete="]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this category?')) {
                e.preventDefault();
            }
        });
    });
});