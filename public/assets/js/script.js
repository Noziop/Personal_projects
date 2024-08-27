// Hamburger Menu - WIP - to debug

document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navUl = document.querySelector('nav ul');

    hamburger.addEventListener('click', function() {
        navUl.classList.toggle('show');
        hamburger.classList.toggle('active');
    });
});

// Cohort Filters - allows button cohorts to filter students in student.index

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

// FlatPicker

document.addEventListener('DOMContentLoaded', function() {
    flatpickr("#date-range", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                document.getElementById('start_date').value = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                document.getElementById('end_date').value = flatpickr.formatDate(selectedDates[1], "Y-m-d");
            }
        }
    });
});

//Feedback Manager Filter
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const dateInput = document.getElementById('date');
    const studentSelect = document.getElementById('student_id');
    const feedbackTable = document.getElementById('feedback-table');
    const feedbackRows = feedbackTable.querySelectorAll('tbody tr');

    function applyFilters() {
        const selectedType = typeSelect.value;
        const selectedDate = dateInput.value;
        const selectedStudent = studentSelect.value;

        feedbackRows.forEach(row => {
            const rowType = row.getAttribute('data-type');
            const rowDate = row.getAttribute('data-date');
            const rowStudent = row.getAttribute('data-student');

            const typeMatch = selectedType === '' || rowType === selectedType;
            const dateMatch = selectedDate === '' || rowDate === selectedDate;
            const studentMatch = selectedStudent === '' || rowStudent === selectedStudent;

            if (typeMatch && dateMatch && studentMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    typeSelect.addEventListener('change', applyFilters);
    dateInput.addEventListener('change', applyFilters);
    studentSelect.addEventListener('change', applyFilters);

    // Initial filter application
    applyFilters();
});