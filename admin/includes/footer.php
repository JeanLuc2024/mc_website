</div><!-- End of content-body -->
        </main><!-- End of main-content -->
    </div><!-- End of admin-container -->
    
    <!-- JavaScript -->
    <script src="js/admin-script.js"></script>

    <!-- Auto-logout session timeout -->
    <script>
        let sessionTimeout;
        let warningTimeout;
        const TIMEOUT_DURATION = 10000; // 10 seconds in milliseconds
        const WARNING_DURATION = 5000; // 5 seconds warning before logout

        function resetSessionTimer() {
            // Clear existing timeouts
            clearTimeout(sessionTimeout);
            clearTimeout(warningTimeout);

            // Set warning timeout (5 seconds before logout)
            warningTimeout = setTimeout(function() {
                if (confirm('Your session will expire in 5 seconds due to inactivity. Click OK to stay logged in.')) {
                    resetSessionTimer(); // Reset if user wants to stay
                }
            }, WARNING_DURATION);

            // Set logout timeout (10 seconds total)
            sessionTimeout = setTimeout(function() {
                alert('Session expired due to inactivity. You will be logged out for security.');
                window.location.href = 'logout.php';
            }, TIMEOUT_DURATION);
        }

        // Events that reset the timer
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

        // Add event listeners
        events.forEach(function(event) {
            document.addEventListener(event, resetSessionTimer, true);
        });

        // Start the timer when page loads
        resetSessionTimer();

        // Also check session status every 5 seconds
        setInterval(function() {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        alert('Session expired. Redirecting to login page.');
                        window.location.href = 'index.php';
                    }
                })
                .catch(error => {
                    console.log('Session check failed:', error);
                });
        }, 5000);
    </script>
</body>
</html>