<?php
// messages.php - Reusable message component

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize message array
$messages = [];

// Check for session messages
if (isset($_SESSION['messages']) && !empty($_SESSION['messages'])) {
    $messages = (array)$_SESSION['messages'];
    unset($_SESSION['messages']); // Clear after displaying
}

// Function to add message (can be used from other files)
function addMessage($text, $type = 'info') {
    if (!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = [];
    }
    $_SESSION['messages'][] = [
        'text' => $text,
        'type' => $type
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kickster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --success-gradient: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --error-gradient: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        --dark-gradient: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
        --text-primary: #ffffff;
        --text-secondary: rgba(255, 255, 255, 0.8);
        --text-muted: rgba(255, 255, 255, 0.6);
        --shadow-soft: 0 8px 32px rgba(31, 38, 135, 0.37);
        --shadow-strong: 0 20px 40px rgba(0, 0, 0, 0.3);
        --border-radius: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--dark-gradient);
        color: var(--text-primary);
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    /* Animated background particles */
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.2) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.2) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.15) 0%, transparent 50%);
        animation: floatingBg 20s ease-in-out infinite;
        z-index: -1;
    }

    @keyframes floatingBg {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        33% { transform: translate(-20px, -20px) rotate(120deg); }
        66% { transform: translate(20px, -10px) rotate(240deg); }
    }

    /* Messages Container */
    .messages-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 400px;
    }

    .message {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-soft);
        animation: slideInRight 0.5s ease-out;
        transition: var(--transition);
        border: 1px solid;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .message.info {
        background: rgba(79, 172, 254, 0.2);
        border-color: rgba(79, 172, 254, 0.3);
        color: #7dd3fc;
    }

    .message.success {
        background: rgba(34, 197, 94, 0.2);
        border-color: rgba(34, 197, 94, 0.3);
        color: #86efac;
    }

    .message.warning {
        background: rgba(251, 191, 36, 0.2);
        border-color: rgba(251, 191, 36, 0.3);
        color: #fde047;
    }

    .message.error {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.3);
        color: #fca5a5;
    }

    .message i.fa-times {
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 6px;
        transition: var(--transition);
        margin-left: 1rem;
    }

    .message i.fa-times:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: scale(1.1);
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Header */
    .header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: var(--glass-bg);
        border-bottom: 1px solid var(--glass-border);
        backdrop-filter: blur(20px);
        padding: 1rem 2rem;
        transition: var(--transition);
    }

    .flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Logo */
    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        transition: var(--transition);
        position: relative;
    }

    .logo:hover {
        transform: translateY(-2px);
    }

    .logo i {
        font-size: 1.8rem;
        background: var(--accent-gradient);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: rotateLogo 8s infinite linear;
    }

    .logo span {
        font-size: 1.8rem;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: waveText 3s ease-in-out infinite;
    }

    @keyframes rotateLogo {
        0% { transform: rotateY(0deg); }
        100% { transform: rotateY(360deg); }
    }

    @keyframes waveText {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }

    /* Navigation */
    .navbar {
        display: flex;
        align-items: center;
        gap: 2rem;
        list-style: none;
    }

    .nav-link {
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        position: relative;
        transition: var(--transition);
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--primary-gradient);
        transition: left 0.3s ease;
        border-radius: 25px;
        z-index: -1;
    }

    .nav-link:hover::before {
        left: 0;
    }

    .nav-link:hover {
        color: var(--text-primary);
        transform: translateY(-2px);
        box-shadow: var(--shadow-soft);
    }

    .nav-link.active {
        background: var(--glass-bg);
        color: var(--text-primary);
        border: 1px solid var(--glass-border);
    }

    /* Icons Section */
    .icons {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        position: relative;
    }

    .hamburger {
        display: none;
        font-size: 1.5rem;
        color: var(--text-secondary);
        cursor: pointer;
        transition: var(--transition);
    }

    .hamburger:hover {
        color: var(--text-primary);
        transform: scale(1.1);
    }

    .icon-link {
        position: relative;
        color: var(--text-secondary);
        font-size: 1.2rem;
        text-decoration: none;
        padding: 0.75rem;
        border-radius: 12px;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .icon-link:hover {
        color: var(--text-primary);
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: var(--shadow-soft);
    }

    .user-icon {
        font-size: 1.2rem;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 0.75rem;
        border-radius: 12px;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .user-icon:hover {
        color: var(--text-primary);
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: var(--shadow-soft);
    }

    /* Badge */
    .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--secondary-gradient);
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.4rem;
        border-radius: 10px;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(240, 147, 251, 0.4);
    }

    .badge.pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    /* Profile Card */
    .profile-card {
        position: absolute;
        top: calc(100% + 20px);
        right: 0;
        width: 280px;
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius);
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-strong);
        padding: 1.5rem;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-20px);
        transition: var(--transition);
    }

    .profile-card.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .avatar {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        text-transform: uppercase;
        box-shadow: var(--shadow-soft);
    }

    .username {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 1.1rem;
    }

    .profile-btn,
    .option-btn,
    .logout-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: var(--transition);
        margin-bottom: 0.5rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-btn {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-secondary);
    }

    .profile-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
        transform: translateX(5px);
    }

    .flex-btn {
        display: flex;
        gap: 0.5rem;
        margin: 1rem 0;
    }

    .option-btn {
        flex: 1;
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-secondary);
        margin-bottom: 0;
        justify-content: center;
        font-size: 0.8rem;
        padding: 0.6rem;
    }

    .option-btn.gradient-btn {
        background: var(--primary-gradient);
        color: white;
        border: none;
    }

    .option-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
        transform: translateY(-2px);
    }

    .option-btn.gradient-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-soft);
    }

    .logout-btn {
        background: rgba(239, 68, 68, 0.2);
        color: #fca5a5;
        border-color: rgba(239, 68, 68, 0.3);
        margin-top: 1rem;
        justify-content: center;
    }

    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.3);
        color: #ffffff;
        transform: translateY(-2px);
    }

    .login-prompt {
        text-align: center;
        color: var(--text-secondary);
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
        .header {
            padding: 1rem;
        }

        .navbar {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            flex-direction: column;
            gap: 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-20px);
            transition: var(--transition);
            padding: 1rem 0;
        }

        .navbar.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .nav-link {
            width: 100%;
            text-align: center;
            padding: 1rem;
            border-radius: 0;
        }

        .nav-link::before {
            border-radius: 0;
        }

        .hamburger {
            display: block;
        }

        .profile-card {
            right: 1rem;
            width: calc(100% - 2rem);
            max-width: 280px;
        }

        .messages-container {
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }

    @media (max-width: 480px) {
        .logo span {
            font-size: 1.5rem;
        }

        .logo i {
            font-size: 1.5rem;
        }

        .icons {
            gap: 1rem;
        }

        .icon-link,
        .user-icon {
            padding: 0.5rem;
            font-size: 1.1rem;
        }
    }

    /* Scroll Effect */
    .header.scrolled {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(25px);
        box-shadow: var(--shadow-soft);
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-gradient);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-gradient);
    }
    </style>
</head>
<body>
    <?php if (!empty($messages)): ?>
        <div class="messages-container">
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= htmlspecialchars($msg['type'], ENT_QUOTES) ?>">
                    <span><?= htmlspecialchars($msg['text'], ENT_QUOTES) ?></span>
                    <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <header class="header">
        <section class="flex">
            <a href="home.php" class="logo">
                <i class="fas fa-shoe-prints"></i>
                <span>Kickster</span>
            </a>

            <nav class="navbar">
                <a href="home.php" class="nav-link">Home</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="shop.php" class="nav-link">Shop</a>
            </nav>

            <div class="icons">
                <?php
                $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
                $count_wishlist_items->execute([$user_id]);
                $total_wishlist_counts = $count_wishlist_items->rowCount();

                $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $count_cart_items->execute([$user_id]);
                $total_cart_counts = $count_cart_items->rowCount();
                ?>
                <div id="menu-btn" class="fas fa-bars hamburger"></div>
                <a href="search_page.php" class="icon-link" title="Search"><i class="fas fa-search"></i></a>
                <a href="wishlist.php" class="icon-link" title="Wishlist">
                    <i class="fas fa-heart"></i>
                    <span><?= $total_wishlist_counts; ?></span>
                </a>
                <a href="cart.php" class="icon-link" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span><?= $total_cart_counts; ?></span>
                </a>
                <div id="user-btn" class="fas fa-user user-icon"></div>
            </div>

            <div class="profile-card">
                <?php          
                $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                $select_profile->execute([$user_id]);
                if($select_profile->rowCount() > 0) {
                    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="profile-header">
                    <div class="avatar">
                        <?= substr($fetch_profile["name"], 0, 1); ?>
                    </div>
                    <p class="username"><?= $fetch_profile["name"]; ?></p>
                </div>                
                <div class="flex-btn">
                    <a href="user_register.php" class="option-btn"><i class="fas fa-user-plus"></i> Register</a>
                    <a href="user_login.php" class="option-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
                </div>
                <a href="components/user_logout.php" class="logout-btn" onclick="return confirm('Logout from the website?');">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a> 
                <?php } else { ?>
                <p class="login-prompt">Please login or Signup first!</p>
                <div class="flex-btn">
                    <a href="user_register.php" class="option-btn gradient-btn"><i class="fas fa-user-plus"></i> Register</a>
                    <a href="user_login.php" class="option-btn gradient-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
                </div>
                <?php } ?>      
            </div>
        </section>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Message handling
        const messages = document.querySelectorAll('.message');
        
        messages.forEach(message => {
            // Auto-close after 5 seconds
            const autoCloseTimer = setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }, 5000);
            
            // Manual close
            const closeBtn = message.querySelector('.fa-times');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    clearTimeout(autoCloseTimer);
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 500);
                });
            }
        });
        
        // Header functionality
        const header = document.querySelector('.header');
        const userBtn = document.getElementById('user-btn');
        const profileCard = document.querySelector('.profile-card');
        const menuBtn = document.getElementById('menu-btn');
        const navbar = document.querySelector('.navbar');
        
        // Scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Toggle profile dropdown
        if (userBtn && profileCard) {
            userBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileCard.classList.toggle('active');
                // Close mobile menu if open
                navbar.classList.remove('active');
                menuBtn.classList.remove('fa-times');
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-icon') && !e.target.closest('.profile-card')) {
                if (profileCard) profileCard.classList.remove('active');
            }
        });
        
        // Mobile menu toggle
        if (menuBtn && navbar) {
            menuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                navbar.classList.toggle('active');
                menuBtn.classList.toggle('fa-times');
                // Close profile dropdown if open
                profileCard.classList.remove('active');
            });
        }
        
        // Close mobile menu when clicking on nav links
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navbar.classList.remove('active');
                menuBtn.classList.remove('fa-times');
            });
        });
        
        // Highlight active nav link based on current page
        const currentPage = window.location.pathname.split('/').pop() || 'home.php';
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (linkPage === currentPage || (currentPage === '' && linkPage === 'home.php')) {
                link.classList.add('active');
            }
        });
        
        // Add hover effects to icon links
        const iconLinks = document.querySelectorAll('.icon-link, .user-icon');
        iconLinks.forEach(icon => {
            icon.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.05)';
            });
            
            icon.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Smooth scroll for internal links
        const internalLinks = document.querySelectorAll('a[href^="#"]');
        internalLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Add loading states to profile links
        const profileLinks = document.querySelectorAll('.profile-btn, .option-btn:not(.gradient-btn), .logout-btn');
        profileLinks.forEach(link => {
            link.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (icon && !this.classList.contains('logout-btn')) {
                    icon.className = 'fas fa-spinner fa-spin';
                }
            });
        });
        
        // Badge animation on count change
        const badges = document.querySelectorAll('.badge');
        badges.forEach(badge => {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        badge.style.animation = 'none';
                        setTimeout(() => {
                            badge.style.animation = 'pulse 2s infinite';
                        }, 100);
                    }
                });
            });
            
            observer.observe(badge, { childList: true });
        });
    });
    </script>
</body>
</html>