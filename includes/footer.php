    <footer>
        <nav>
            <a href="home.php">| Home |</a>
            <a href="about.php"> About |</a>
            <a href="browse.php"> Browse |</a>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="dashboard.php"> Dashboard |</a>
                <a href="threads.php"> Discussions |</a>
                <?php if (isset($_SESSION['type']) && $_SESSION['type'] === 'admin'): ?>
                    <a href="admin.php"> Admin |</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </footer>
</body>
</html> 