document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(now.getDate() + 7); 
put
    const formatDate = (date) => {
        return date.toISOString().slice(0, 16); 
    };
    
    document.getElementById('borrow_date').value = formatDate(now);
    document.getElementById('due_date').value = formatDate(nextWeek);
});

document.addEventListener('DOMContentLoaded', function() {
    const denyButtons = document.querySelectorAll('.deny-button');
    denyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const formId = this.getAttribute('data-form-id');
            document.getElementById('deny-form-' + formId).style.display = 'block';
        });
    });
});