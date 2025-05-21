document.addEventListener("DOMContentLoaded", function () {
  function startCountdown(countdownElements) {
      // Calculate the initial target date (7 days from now)
      function getNewTargetDate() {
          let date = new Date();
          date.setDate(date.getDate() + 7); // Add 7 days
          return date.getTime();
      }
      
      // Initialize target date
      let targetDate = getNewTargetDate();
      
      // Check for previously saved target date in localStorage
      const savedTargetDate = localStorage.getItem('countdownTargetDate');
      if (savedTargetDate) {
          targetDate = parseInt(savedTargetDate);
          
          // If the saved date is in the past, create a new target date
          if (targetDate - new Date().getTime() < 0) {
              targetDate = getNewTargetDate();
              localStorage.setItem('countdownTargetDate', targetDate);
          }
      } else {
          // Save initial target date
          localStorage.setItem('countdownTargetDate', targetDate);
      }

      function updateCountdown() {
          let now = new Date().getTime();
          let timeLeft = targetDate - now;

          // If countdown finished, reset to a new 7-day period
          if (timeLeft < 0) {
              targetDate = getNewTargetDate();
              localStorage.setItem('countdownTargetDate', targetDate);
              timeLeft = targetDate - now;
          }

          let days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
          let hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          let minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
          let seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

          // Ensure values are two digits
          hours = hours < 10 ? "0" + hours : hours;
          minutes = minutes < 10 ? "0" + minutes : minutes;
          seconds = seconds < 10 ? "0" + seconds : seconds;

          countdownElements.forEach(el => {
              el.innerHTML = `
                  <span>${days}</span>d |
                  <span>${hours}</span>h |
                  <span>${minutes}</span>m |
                  <span>${seconds}</span>s
              `;
          });
      }

      updateCountdown(); // Run immediately
      setInterval(updateCountdown, 1000); // Update every second
  }

  // Select all countdown elements (for mobile & desktop)
  let countdownElements = document.querySelectorAll(".countdown");
  if (countdownElements.length) {
      startCountdown(countdownElements);
  }
});