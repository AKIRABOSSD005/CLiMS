document.addEventListener('DOMContentLoaded', function() {
    const reportTypeDropdown = document.getElementById('report-type-dropdown');
    const monthMenu = document.getElementById('month-menu');
    const monthDropdown = document.getElementById('month-dropdown');
    const quarterDropdown = document.getElementById('quarter-dropdown');
    const yearDropdown = document.getElementById('year-dropdown');
    const selectionLabel = document.getElementById('month-selection-label');
    const quarterSelectionLabel = document.getElementById('quarter-selection-label');
    const yearSelectionLabel = document.getElementById('year-selection-label');

    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const currentYear = new Date().getFullYear();

    // Populate month options
    months.forEach((month, index) => {
        const li = document.createElement('li');
        li.innerHTML = `<a class='dropdown-item' href='#' data-month='${index + 1}'>${month}</a>`;
        monthMenu.appendChild(li);
    });

    // Populate year options
    for (let year = currentYear; year >= 1952; year--) {
        const li = document.createElement('li');
        li.innerHTML = `<a class='dropdown-item' href='#' data-year='${year}'>${year}</a>`;
        document.getElementById('year-menu').appendChild(li);
    }

    // Add the yearDropdown change event listener here
    yearDropdown.addEventListener('change', function() {
        const selectedYear = yearDropdown.value; // Get the value directly
        document.getElementById('selectedQuarterYear').value = selectedYear; // Set the value for the backend
    });

    function updateDropdownVisibility(reportType) {
        monthDropdown.style.display = 'none';
        quarterDropdown.style.display = 'none';
        yearDropdown.style.display = 'none';
        selectionLabel.style.display = 'none';
        quarterSelectionLabel.style.display = 'none';
        yearSelectionLabel.style.display = 'none';

        if (reportType === 'monthly') {
            monthDropdown.style.display = 'block';
            selectionLabel.style.display = 'block';
            yearDropdown.style.display = 'block'; // Show year dropdown for monthly
            yearSelectionLabel.style.display = 'block'; // Show year label for monthly
        } else if (reportType === 'quarterly') {
            quarterDropdown.style.display = 'block';
            quarterSelectionLabel.style.display = 'block';
            yearDropdown.style.display = 'block'; // Show year dropdown for quarterly
            yearSelectionLabel.style.display = 'block';
        } else if (reportType === 'annual') {
            yearDropdown.style.display = 'block';
            yearSelectionLabel.style.display = 'block';
        }
    }

    // Function to handle report type selection
    function updateReportTypeSelection(reportType) {
        reportTypeDropdown.textContent = reportType.charAt(0).toUpperCase() + reportType.slice(1);
        document.getElementById('reportType').value = reportType;
        updateDropdownVisibility(reportType);
    }

    // Event listener for report type selection
    document.querySelectorAll('.dropdown-item[data-report-type]').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            const reportType = this.getAttribute('data-report-type');
            updateReportTypeSelection(reportType);
            console.log('Selected Report Type:', reportType); // Debug log
        });
    });

    // Event listener for month selection
    monthMenu.addEventListener('click', function(event) {
        if (event.target.classList.contains('dropdown-item')) {
            event.preventDefault();
            const month = event.target.getAttribute('data-month');
            document.getElementById('selectedMonth').value = month;
            document.querySelector('#month-dropdown .dropdown-toggle').textContent = event.target.textContent; // Update button text
            console.log('Selected Month:', month); // Debug log
        }
    });

    // Event listener for year selection
    document.getElementById('year-menu').addEventListener('click', function(event) {
        if (event.target.classList.contains('dropdown-item')) {
            event.preventDefault();
            const year = event.target.getAttribute('data-year');
            document.getElementById('selectedYear').value = year;
            document.querySelector('#year-dropdown .dropdown-toggle').textContent = year; // Update button text

            // Set the quarter year hidden input
            document.getElementById('selected_quarter_year').value = year; // Set the value for backend
            console.log('Selected Year:', year); // Debug log
        }
    });

    // Event listener for quarter selection
    document.getElementById('quarter-menu').addEventListener('click', function(event) {
        if (event.target.classList.contains('dropdown-item')) {
            event.preventDefault();
            const quarter = event.target.getAttribute('data-quarter');
            document.getElementById('selectedQuarter').value = quarter;
            document.querySelector('#quarter-dropdown .dropdown-toggle').textContent = quarter; // Update button text

            // Set the quarter year based on the year dropdown value
            document.getElementById('selected_quarter_year').value = document.getElementById('selectedYear').value; // Ensure this references the correct input
            console.log('Selected Quarter:', quarter); // Debug log
            console.log('Quarter Year Set To:', document.getElementById('selected_quarter_year').value); // Debug log
        }
    });

    // Event listener for form submission
    document.getElementById('monthlyForm').addEventListener('submit', function(event) {
        console.log('Report Type:', document.getElementById('reportType').value);

        const selectedMonthElement = document.getElementById('selectedMonth');
        const selectedQuarterElement = document.getElementById('selectedQuarter');
        const selectedYearElement = document.getElementById('selectedYear');
        const selectedQuarterYearElement = document.getElementById('selected_quarter_year');

        // Check if the selectedMonthElement exists before trying to access its value
        if (selectedMonthElement && selectedMonthElement.value) {
            console.log('Selected Month:', selectedMonthElement.value);
        } else {
            console.log('No month selected');
        }

        // Check if the selectedQuarterElement exists before trying to access its value
        if (selectedQuarterElement && selectedQuarterElement.value) {
            console.log('Selected Quarter:', selectedQuarterElement.value);
        } else {
            console.log('No quarter selected');
        }

        // Check if the selectedYearElement exists before trying to access its value
        if (selectedYearElement && selectedYearElement.value) {
            console.log('Selected Year:', selectedYearElement.value);
        } else {
            console.log('No year selected');
        }

        // Check if both quarter and quarter year elements exist
        if (selectedQuarterElement && selectedQuarterYearElement && selectedQuarterElement.value && selectedQuarterYearElement.value) {
            console.log("Selected Quarter:", selectedQuarterElement.value);
            console.log("Selected Quarter Year:", selectedQuarterYearElement.value);
        } else {
            console.log("Quarter or quarter year not selected");
        }
    });
}); // <-- This closing parenthesis was missing
