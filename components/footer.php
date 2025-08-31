<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Footer Design</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
            color: #fff;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
        }

        h1 {
            font-size: 3.5rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4, #fad0c4, #a18cd1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .subtitle {
            font-size: 1.2rem;
            color: #e0e0e0;
            max-width: 700px;
            margin: 0 auto 30px;
        }

        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin-bottom: 50px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 25px;
            width: 280px;
            text-align: center;
            transition: transform 0.3s ease, background 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.12);
        }

        .feature-card i {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #ff9a9e;
        }

        .feature-card h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: #fff;
        }

        .feature-card p {
            color: #d1d1d1;
            font-size: 0.95rem;
        }

        /* Modern Footer Styles */
        .footer {
            background: linear-gradient(135deg, #0c1e3e, #1d3557);
            padding: 5rem 2rem 2rem;
            color: #fff;
            position: relative;
            overflow: hidden;
            border-radius: 20px 20px 0 0;
            margin-top: auto;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.3);
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            z-index: 0;
        }

        .footer .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .footer .box {
            padding: 2rem;
            transition: all 0.3s ease;
            color: #f9f9f9;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .footer .box:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .footer .box h3 {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 1rem;
            color: #ffffff;
        }

        .footer .box h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #ff9a9e, #fad0c4);
            border-radius: 3px;
        }

        .footer .box a {
            display: block;
            font-size: 1.1rem;
            color: #e0e0e0;
            padding: 0.8rem 0;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .footer .box a:hover {
            color: #ff9a9e;
            padding-left: 1rem;
        }

        .footer .box a i {
            margin-right: 1rem;
            transition: all 0.3s ease;
            width: 20px;
            text-align: center;
        }

        .footer .box a:hover i {
            transform: rotate(90deg);
            color: #ff9a9e;
        }

        .footer .credit {
            text-align: center;
            padding-top: 3rem;
            margin-top: 3rem;
            font-size: 1.1rem;
            color: #b1b1b1;
            border-top: 1px solid rgba(255,255,255,0.1);
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 3rem auto 0;
        }

        .footer .credit span {
            color: #ff9a9e;
            font-weight: 600;
        }

        .footer .social-icons {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .footer .social-icons a {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            transition: all 0.3s ease;
        }

        .footer .social-icons a:hover {
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #0c1e3e;
            transform: translateY(-5px) scale(1.1);
        }

        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }

        .payment-icon {
            width: 50px;
            height: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            transition: all 0.3s ease;
        }

        .payment-icon:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.2);
        }

        /* Enhanced Newsletter Corner */
        .newsletter-corner {
            position: fixed;
            top: 30px;
            left: 30px;
            z-index: 1000;
        }

        .newsletter-toggle {
            width: 65px;
            height: 65px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            cursor: pointer;
            box-shadow: 0 5px 25px rgba(255, 107, 0, 0.4);
            position: relative;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 107, 0, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(255, 107, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 107, 0, 0); }
        }

        .newsletter-toggle:hover {
            transform: scale(1.1) rotate(15deg);
            background: linear-gradient(45deg, #ff8e53, #ff6b6b);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #4361ee;
            color: white;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
            animation: bounce 1.5s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .newsletter-box {
            position: absolute;
            top: 80px;
            left: 0;
            width: 340px;
            background: linear-gradient(145deg, #1d3557, #0c1e3e);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            transform: translateY(-20px);
            opacity: 0;
            pointer-events: none;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1001;
        }

        .newsletter-corner.active .newsletter-box {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .newsletter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .newsletter-header h3 {
            color: #ff9a9e;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #aaa;
            transition: color 0.2s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close-btn:hover {
            color: #ff6b6b;
            background: rgba(255, 255, 255, 0.1);
        }

        .newsletter-form input {
            width: 100%;
            padding: 14px 20px;
            margin: 12px 0;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
        }

        .newsletter-form input::placeholder {
            color: #b1b1b1;
        }

        .newsletter-form input:focus {
            border-color: #ff6b6b;
            outline: none;
            box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.2);
            background: rgba(0, 0, 0, 0.3);
        }

        .newsletter-form .btn {
            width: 100%;
            background: linear-gradient(45deg, #4361ee, #3a0ca3);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            font-size: 16px;
        }

        .newsletter-form .btn:hover {
            background: linear-gradient(45deg, #3a0ca3, #4361ee);
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(58, 12, 163, 0.4);
        }

        .newsletter-content p {
            color: #d1d1d1;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 15px;
        }

        /* Responsive Design */
        @media (max-width: 900px) {
            .footer .grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .newsletter-corner {
                top: 20px;
                left: 20px;
            }
            
            .newsletter-box {
                width: 300px;
            }
        }

        @media (max-width: 600px) {
            .footer .grid {
                grid-template-columns: 1fr;
            }

            .footer .box {
                text-align: center;
            }

            .footer .box h3::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .footer .social-icons {
                justify-content: center;
            }
            
            .newsletter-corner {
                top: 15px;
                left: 15px;
            }
            
            .newsletter-toggle {
                width: 55px;
                height: 55px;
                font-size: 22px;
            }
            
            .newsletter-box {
                width: 280px;
                left: -20px;
            }
            
            h1 {
                font-size: 2.5rem;
            }
            
            .feature-card {
                width: 100%;
                max-width: 350px;
            }
        }
    </style>
</head>
<body>
    
    <footer class="footer">
        <section class="grid">
            <div class="box">
                <h3>Quick Links</h3>
                <a href="home.php"><i class="fas fa-chevron-right"></i> Home</a>
                <a href="about.php"><i class="fas fa-chevron-right"></i> About</a>
                <a href="shop.php"><i class="fas fa-chevron-right"></i> Shop</a>
                <a href="contact.php"><i class="fas fa-chevron-right"></i> Contact</a>
                <a href="blog.php"><i class="fas fa-chevron-right"></i> Blog</a>
            </div>

            <div class="box">
                <h3>Account</h3>
                <a href="user_login.php"><i class="fas fa-chevron-right"></i> Login</a>
                <a href="user_register.php"><i class="fas fa-chevron-right"></i> Register</a>
                <a href="cart.php"><i class="fas fa-chevron-right"></i> Cart</a>
                <a href="orders.php"><i class="fas fa-chevron-right"></i> Orders</a>
                <a href="settings.php"><i class="fas fa-chevron-right"></i> Settings</a>
            </div>

            <div class="box">
                <h3>Contact Us</h3>
                <a href="tel:9865247946"><i class="fas fa-phone"></i> +977 9865247946</a>
                <a href="tel:9865247946"><i class="fas fa-phone"></i> +977 9800454451</a>
                <a href="mailto:gkstore@gmail.com"><i class="fas fa-envelope"></i> Kickster@gmail.com</a>
                <a href="https://www.google.com/maps" target="_blank"><i class="fas fa-map-marker-alt"></i> Chitwan, Nepal</a>
                <a href="#"><i class="fas fa-clock"></i> Mon-Fri: 9AM - 6PM</a>
            </div>

            <div class="box">
                <h3>Connect With Us</h3>
                <p>Subscribe to our newsletter for updates</p>
                <div class="social-icons">
                    <a href="#" class="fab fa-facebook-f"></a>
                    <a href="#" class="fab fa-twitter"></a>
                    <a href="#" class="fab fa-instagram"></a>
                    <a href="#" class="fab fa-linkedin-in"></a>
                    <a href="#" class="fab fa-youtube"></a>
                </div>
                
                <div class="payment-methods">
                    <h4>Payment Methods:</h4>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                        <div class="payment-icon"><i class="fab fa-cc-visa"></i></div>
                        <div class="payment-icon"><i class="fab fa-cc-mastercard"></i></div>
                        <div class="payment-icon"><i class="fab fa-cc-paypal"></i></div>
                        <div class="payment-icon"><i class="fab fa-cc-apple-pay"></i></div>
                        <div class="payment-icon"><i class="fab fa-cc-amazon-pay"></i></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="credit">
            &copy; 2023 <span>Kickster</span>. All Rights Reserved. | 
            Designed by <i style="color: #ff6b6b;"></i> by <span>Raj Krishna Silwal & Krishna Adhikari</span>
        </div>
    </footer>

    
</body>
</html>