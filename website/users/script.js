document.addEventListener('DOMContentLoaded', function () {

    // Function to show a specific container
    function showContainer(selector) {
        document.querySelectorAll('.request-container, .profile-container, .new-request-container, .main-container')
            .forEach(c => {
                c.classList.add('hidden');
                c.classList.remove('visible');
            });

        const container = document.querySelector(selector);
        if (container) {
            container.classList.remove('hidden');
            container.classList.add('visible');
        }
    }

    // Initialize: Show main container by default
    showContainer('.main-container');

    // Handle navigation clicks
    const navElements = document.querySelectorAll(".nav-target");
    navElements.forEach(element => {
        element.addEventListener("click", () => {
            switch (element.id) {
                case "home-icon":
                case "home-label":
                    showContainer('.main-container');
                    break;
                case "book-icon":
                case "book-label":
                    showContainer('.new-request-container');
                    break;
                case "history-icon":
                case "history-label":
                    showContainer('.request-container');
                    break;
                case "profile-icon":
                    showContainer('.profile-container');
                    break;
                case "new-button":
                    showContainer('.new-request-container');
                    break;
            }
        });
    });

});