document.addEventListener('DOMContentLoaded', function() {
    // Calendar functionality
    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');
    const monthDisplay = document.querySelector('.calendar-month');
    
    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
            // Previous month logic
            console.log('Previous month clicked');
        });
        
        nextBtn.addEventListener('click', function() {
            // Next month logic
            console.log('Next month clicked');
        });
    }
    
    // Time slot selection
    const timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            // Remove selected class from all slots
            timeSlots.forEach(s => s.classList.remove('selected'));
            // Add selected class to clicked slot
            this.classList.add('selected');
        });
    });
    
    // Calendar day selection
    const calendarDays = document.querySelectorAll('.calendar-days span');
    calendarDays.forEach(day => {
        if (day.textContent.trim() !== '') {
            day.addEventListener('click', function() {
                // Remove available class from all days
                calendarDays.forEach(d => d.classList.remove('available'));
                // Add available class to clicked day
                this.classList.add('available');
            });
        }
    });
    
    // Book now button
    const bookBtn = document.querySelector('.book-now-btn');
    if (bookBtn) {
        bookBtn.addEventListener('click', function() {
            const selectedDate = document.querySelector('.calendar-days span.available');
            const selectedTime = document.querySelector('.time-slot.selected');
            
            if (selectedDate && selectedTime) {
                console.log(`Booking appointment for ${selectedDate.textContent} at ${selectedTime.textContent}`);
                // Process booking here
            } else {
                console.log('Please select both a date and time slot');
                // Show validation message without alert
            }
        });
    }
});