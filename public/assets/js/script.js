// Hamburger Menu - WIP - to debug

document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navUl = document.querySelector('nav ul');

    hamburger.addEventListener('click', function() {
        navUl.classList.toggle('show');
        hamburger.classList.toggle('active');
    });
});

// Cohort Filters - WIP - to debug

document.addEventListener('DOMContentLoaded', function() {
    const cohortButtons = document.querySelectorAll('.cohort-filters .neumorphic-button');
    const studentsTable = document.getElementById('students-table');
    const studentRows = studentsTable.querySelectorAll('tbody tr');

    cohortButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cohortId = this.getAttribute('data-cohort');
            
            cohortButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            studentRows.forEach(row => {
                if (cohortId === 'all' || row.getAttribute('data-cohort') === cohortId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});