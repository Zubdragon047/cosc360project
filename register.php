<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COSC360 Project - Book Exchange</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="scripts/registerscript.js"></script>
</head>

<body>
    <header>
        <h1>Book Exchange</h1>
        <div class="header-rightside">
            <?php session_start(); ?>
            <?php if (isset($_SESSION['username'])): ?>
                <nav class="header-nav-top">
                    <?php echo "<h3>Welcome ".$_SESSION['username']."</h3>"; ?>
                    <a href="logout.php">Logout</a>
                    <a href="myaccount.php">My Account</a>
                </nav>
            <?php else: ?>
                <nav class="header-nav-top">
                    <a href="login.php">Login</a> /
                    <a href="register.php">Register</a>
                </nav>
            <?php endif; ?>
            <nav class="header-nav-bottom">
                <a href="home.php"><button>Home</button></a>
                <a href="about.php"><button>About</button></a>
                <a href="browse.php"><button>Browse</button></a>
                <a href="dashboard.php"><button>Dashboard</button></a>
            </nav>
        </div>
    </header>

<div class="main-container">
    <div>
        <h2>Register</h2>
        <h3>Create a user account</h3>
        <form id="register-form" method="post"
            action="register_form.php"
            enctype="multipart/form-data"
            novalidate>
            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" id="register-username" name="username" placeholder="Enter login name." required>
                <span id="username-error-message"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="register-password" name="password" placeholder="Enter login password." required>
                <span id="password-error-message"></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="register-email" name="email" placeholder="Enter login email." required>
                <span id="email-error-message"></span>
            </div>
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="register-firstname" name="firstname" placeholder="Enter your first name (OPTIONAL)">
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="register-lastname" name="lastname" placeholder="Enter your last name (OPTIONAL)">
            </div>
            <div class="form-group">
                <label for="profilepic">Profile Picture</label>
                <label>(jpg/gif/bmp/png less than 10mb)</label>
                <input type="file" id="register-profilepic" name="profilepic">
            </div>
            <div class="form-buttons">
                <button type="submit" class="submit">Submit</button>
            </div>
        </form>
    </div>
    
</div>

<footer>
    <nav>
        <a href="home.php">| Home |</a>
        <a href="about.php"> About |</a>
        <a href="browse.php"> Browse |</a>
        <a href="dashboard.php"> Dashboard |</a>
    </nav>
</footer>
</body>
</html>