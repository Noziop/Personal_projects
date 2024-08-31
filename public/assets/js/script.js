// Hamburger Menu - WIP - to debug

document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navUl = document.querySelector('nav ul');

    hamburger.addEventListener('click', function() {
        navUl.classList.toggle('show');
        hamburger.classList.toggle('active');
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
