    <script>
        // MAIN MENU TOGGLE
        document.querySelectorAll(".has-dropdown > .menu-link").forEach(link => {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                const parent = this.parentElement;
                const isActive = parent.classList.contains("active");

                // Close all open menus
                document.querySelectorAll(".menu-item.active").forEach(item => item.classList.remove("active"));
                document.querySelectorAll(".submenu.active").forEach(s => s.classList.remove("active"));

                if (!isActive) {
                    parent.classList.add("active");
                }
            });
        });

        // SUBMENU TOGGLE
        document.querySelectorAll(".submenu-link").forEach(link => {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                const parent = this.parentElement;
                const isActive = parent.classList.contains("active");

                document.querySelectorAll(".submenu.active").forEach(s => s.classList.remove("active"));

                if (!isActive) {
                    parent.classList.add("active");
                }
            });
        });

        // CLOSE ON OUTSIDE CLICK
        document.addEventListener("click", function(e) {
            if (!e.target.closest(".menu-item")) {
                document.querySelectorAll(".menu-item.active").forEach(item => item.classList.remove("active"));
                document.querySelectorAll(".submenu.active").forEach(s => s.classList.remove("active"));
            }
        });

        // MOBILE TOGGLE
        const menuToggle = document.getElementById("menuToggle");
        const mainMenu = document.getElementById("mainMenu");
        if(menuToggle && mainMenu) {
            menuToggle.addEventListener("click", function(e) {
                e.stopPropagation();
                mainMenu.classList.toggle("open");
            });
        }
    </script>
</body>
</html>
