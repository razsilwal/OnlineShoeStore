<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kickster Footer</title>
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
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #fff;
        }

        .content {
            flex: 1;
            padding: 2rem;
            text-align: center;
        }

        /* Unique Footer Styles */
        .kickster-footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            padding: 4rem 2rem 2rem;
            color: #fff;
            position: relative;
            margin-top: 4rem;
            overflow: hidden;
            border-top: 3px solid #f59e0b;
        }   

        .footer-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(245, 158, 11, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            z-index: 0;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .footer-main {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .brand-section h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #f59e0b, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .brand-section p {
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .brand-highlights {
            display: flex;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .highlight-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #f59e0b;
            font-size: 0.9rem;
        }

        .highlight-item i {
            font-size: 1.1rem;
        }

        .footer-links h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #fbbf24;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-links h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 30px;
            height: 2px;
            background: #f59e0b;
            border-radius: 2px;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links ul li {
            margin-bottom: 0.8rem;
        }

        .footer-links ul li a {
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .footer-links ul li a:hover {
            color: #f59e0b;
            transform: translateX(5px);
        }

        .footer-links ul li a i {
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .footer-links ul li a:hover i {
            transform: rotate(90deg);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            color: #cbd5e1;
            font-size: 0.95rem;
        }

        .contact-item i {
            color: #f59e0b;
            font-size: 1.1rem;
            margin-top: 0.2rem;
            flex-shrink: 0;
        }

        .newsletter-section h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #fbbf24;
        }

        .newsletter-section p {
            color: #cbd5e1;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .newsletter-form {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .newsletter-form input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: 1px solid #475569;
            border-radius: 8px;
            background: #1e293b;
            color: #fff;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .newsletter-form input:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }

        .newsletter-form button {
            background: linear-gradient(45deg, #f59e0b, #fbbf24);
            color: #0f172a;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .newsletter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4);
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: #1e293b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid #475569;
        }

        .social-link:hover {
            background: #f59e0b;
            color: #0f172a;
            transform: translateY(-3px);
            border-color: #f59e0b;
        }

        .footer-bottom {
            border-top: 1px solid #334155;
            padding-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .copyright {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .copyright span {
            color: #f59e0b;
            font-weight: 600;
        }

        .footer-legal {
            display: flex;
            gap: 2rem;
        }

        .footer-legal a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-legal a:hover {
            color: #f59e0b;
        }

        .payment-methods {
            display: flex;
            gap: 0.8rem;
            align-items: center;
        }

        .payment-methods span {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }

        .payment-icon {
            width: 35px;
            height: 25px;
            background: #1e293b;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            font-size: 1.1rem;
            border: 1px solid #475569;
            transition: all 0.3s ease;
        }

        .payment-icon:hover {
            background: #f59e0b;
            color: #0f172a;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .footer-main {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
        }

        @media (max-width: 768px) {
            .footer-main {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .footer-legal {
                justify-content: center;
            }

            .brand-highlights {
                flex-direction: column;
                gap: 1rem;
            }

            .newsletter-form {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .kickster-footer {
                padding: 3rem 1rem 1rem;
            }

            .footer-main {
                gap: 1.5rem;
            }

            .social-links {
                justify-content: center;
            }

            .payment-methods {
                justify-content: center;
                flex-wrap: wrap;
            }
        }

        /* Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    
    <footer class="kickster-footer">
        <div class="footer-background"></div>
        <div class="footer-container">
            <div class="footer-main">
                <!-- Brand Section -->
                <div class="brand-section">
                    <h3>KICKSTER</h3>
                    <p>Step into style with Nepal's premier sneaker destination. Discover the latest trends, exclusive collections, and premium footwear that combines comfort with cutting-edge fashion.</p>
                    <div class="brand-highlights">
                        <div class="highlight-item">
                            <i class="fas fa-shipping-fast floating-icon"></i>
                            <span>Free Shipping</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-shield-alt floating-icon"></i>
                            <span>Secure Payment</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-undo floating-icon"></i>
                            <span>Easy Returns</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-links">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="home.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="shop.php"><i class="fas fa-chevron-right"></i> Shop All</a></li>
                        <li><a href="new-arrivals.php"><i class="fas fa-chevron-right"></i> New Arrivals</a></li>
                        <li><a href="best-sellers.php"><i class="fas fa-chevron-right"></i> Best Sellers</a></li>
                        <li><a href="sale.php"><i class="fas fa-chevron-right"></i> Sale</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="footer-links">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="contact.php"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
                        <li><a href="shipping.php"><i class="fas fa-chevron-right"></i> Shipping Info</a></li>
                        <li><a href="returns.php"><i class="fas fa-chevron-right"></i> Returns</a></li>
                        <li><a href="size-guide.php"><i class="fas fa-chevron-right"></i> Size Guide</a></li>
                        <li><a href="faq.php"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                    </ul>
                </div>

                <!-- Contact & Newsletter -->
                <div class="footer-links">
                    <h4>Stay Connected</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Chitwan, Nepal</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+977 9865247946</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@kickster.com</span>
                        </div>
                    </div>

                    <div class="newsletter-section">
                        <p>Get exclusive offers and sneaker news</p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Enter your email" required>
                            <button type="submit">Subscribe</button>
                        </form>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="copyright">
                    &copy; 2024 <span>Kickster</span>. All rights reserved. Crafted with passion in Nepal.
                </div>
                <div class="footer-legal">
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms of Service</a>
                    <a href="cookies.php">Cookies</a>
                </div>
                <div class="payment-methods">
                    <span>We Accept:</span>
                    <div class="payment-icon"><i class="fab fa-cc-visa"></i></div>
                    <div class="payment-icon"><i class="fab fa-cc-mastercard"></i></div>
                    <div class="payment-icon"><i class="fab fa-cc-paypal"></i></div>
                    <div class="payment-icon"><i class="fab fa-cc-apple-pay"></i></div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Simple newsletter form handling
        document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input').value;
            alert(`Thank you for subscribing with: ${email}`);
            this.reset();
        });
    </script>
</body>
</html>