<?php
include 'components/connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About Us | Kickster</title>

   <!-- Swiper CSS -->
   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <!-- Animate.css -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
   
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">

   <style>
      :root {
         --primary: #4361ee;
         --primary-light: #5a75f0;
         --secondary: #dc2f2f;
         --accent: #fca311;
         --light: #f8f9fa;
         --dark: #14213d;
         --gray: #6c757d;
         --nepal-red: #ce0000;
         --nepal-blue: #003893;
         --nepal-green: #006a4e;
         --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      }
      
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
      }
      
      body {
         background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
         color: #333;
         overflow-x: hidden;
      }
      
      
      
      /* Hero Section */
      .hero {
         height: 60vh;
         background: linear-gradient(rgba(20, 33, 61, 0.8), rgba(20, 33, 61, 0.8)), 
                     url('shoes collection picture/other/aboutus.jpg') center/cover no-repeat;
         display: flex;
         align-items: center;
         justify-content: center;
         text-align: center;
         padding: 0 20px;
         position: relative;
         overflow: hidden;
      }
      
      .hero::after {
         content: "";
         position: absolute;
         bottom: 0;
         left: 0;
         width: 100%;
         height: 100px;
         background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="%23f8f9fa"></path></svg>');
         background-size: cover;
         background-position: center;
      }
      
      .hero-content {
         max-width: 800px;
         z-index: 1;
      }
      
      .hero h1 {
         font-size: 4rem;
         color: white;
         margin-bottom: 1rem;
         font-weight: 800;
         text-shadow: 0 2px 10px rgba(0,0,0,0.3);
         font-family: 'Montserrat', sans-serif;
      }
      
      .hero p {
         font-size: 1.5rem;
         color: rgba(255,255,255,0.9);
         max-width: 700px;
         margin: 0 auto 2rem;
      }
      
      /* About Section */
      .about {
         padding: 7rem 7%;
         background: #f8f9fa;
      }
      
      .about .row {
         display: flex;
         align-items: center;
         flex-wrap: wrap;
         gap: 5rem;
      }
      
      .about .row .image {
         flex: 1 1 40rem;
         overflow: hidden;
         border-radius: 20px;
         box-shadow: 0 20px 40px rgba(0,0,0,0.15);
         animation: fadeInLeft 1s ease;
         position: relative;
         border: 10px solid white;
      }
      
      .about .row .image img {
         width: 100%;
         height: 100%;
         object-fit: cover;
         transition: transform 0.8s ease;
      }
      
      .about .row .image:hover img {
         transform: scale(1.05);
      }
      
      .about .row .content {
         flex: 1 1 40rem;
         animation: fadeInRight 1s ease;
      }
      
      .about .row .content h3 {
         font-size: 3rem;
         color: var(--dark);
         margin-bottom: 1.5rem;
         position: relative;
         display: inline-block;
      }
      
      .about .row .content h3::after {
         content: "";
         position: absolute;
         bottom: -10px;
         left: 0;
         width: 70px;
         height: 4px;
         background: var(--gradient);
         border-radius: 2px;
      }
      
      .about .row .content p {
         font-size: 1.6rem;
         line-height: 1.8;
         color: #666;
         padding: 1.5rem 0;
      }
      
      .about .row .content .btn {
         display: inline-block;
         margin-top: 2rem;
         background: var(--gradient);
         color: white;
         padding: 1.2rem 3.5rem;
         border-radius: 50px;
         transition: all 0.4s ease;
         box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
         font-weight: 600;
         font-size: 1.6rem;
         border: none;
         cursor: pointer;
         text-decoration: none;
      }
      
      .about .row .content .btn:hover {
         transform: translateY(-5px);
         box-shadow: 0 15px 30px rgba(67, 97, 238, 0.4);
      }
      
      /* Stats Section */
      .stats {
         padding: 7rem 7%;
         background: linear-gradient(135deg, var(--dark) 0%, var(--primary) 100%);
         color: white;
         text-align: center;
         position: relative;
      }
      
      .stats::before {
         content: "";
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100px;
         background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="%23f8f9fa"></path></svg>');
         background-size: cover;
         transform: rotate(180deg);
      }
      
      .stats::after {
         content: "";
         position: absolute;
         bottom: 0;
         left: 0;
         width: 100%;
         height: 100px;
         background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="%23f8f9fa"></path></svg>');
         background-size: cover;
      }
      
      .stats .heading {
         margin-bottom: 5rem;
      }
      
      .stats .heading h2 {
         font-size: 3.5rem;
         margin-bottom: 1.5rem;
      }
      
      .stats .heading p {
         font-size: 1.6rem;
         max-width: 700px;
         margin: 0 auto;
         opacity: 0.8;
      }
      
      .stats .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
         gap: 3rem;
      }
      
      .stats .box {
         padding: 4rem 2rem;
         background: rgba(255,255,255,0.1);
         border-radius: 15px;
         backdrop-filter: blur(10px);
         border: 1px solid rgba(255,255,255,0.2);
         transition: all 0.4s ease;
         z-index: 1;
      }
      
      .stats .box:hover {
         transform: translateY(-15px);
         background: rgba(255,255,255,0.15);
         box-shadow: 0 15px 30px rgba(0,0,0,0.2);
      }
      
      .stats .box i {
         font-size: 4.5rem;
         margin-bottom: 2rem;
         color: white;
         background: var(--gradient);
         width: 90px;
         height: 90px;
         line-height: 90px;
         border-radius: 50%;
         text-align: center;
      }
      
      .stats .box h3 {
         font-size: 4.5rem;
         margin-bottom: 0.5rem;
         font-weight: 700;
      }
      
      .stats .box p {
         font-size: 1.8rem;
         opacity: 0.9;
         font-weight: 500;
      }
      
      /* Team Section */
      .team {
         padding: 7rem 7%;
         background: #f8f9fa;
      }
      
      .team .heading {
         text-align: center;
         margin-bottom: 6rem;
      }
      
      .team .heading h1 {
         font-size: 3.5rem;
         color: var(--dark);
         margin-bottom: 1.5rem;
         position: relative;
         display: inline-block;
      }
      
      .team .heading h1::after {
         content: "";
         position: absolute;
         bottom: -10px;
         left: 50%;
         transform: translateX(-50%);
         width: 80px;
         height: 4px;
         background: var(--gradient);
         border-radius: 2px;
      }
      
      .team .heading p {
         font-size: 1.6rem;
         color: #666;
         max-width: 700px;
         margin: 2rem auto 0;
      }
      
      .team .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(27rem, 1fr));
         gap: 4rem;
      }
      
      .team .box {
         background: white;
         border-radius: 20px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(0,0,0,0.1);
         transition: all 0.4s ease;
         text-align: center;
         position: relative;
         z-index: 1;
      }
      
      .team .box:hover {
         transform: translateY(-15px);
         box-shadow: 0 20px 40px rgba(0,0,0,0.15);
      }
      
      .team .box::before {
         content: "";
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 10px;
         background: var(--gradient);
      }
      
      .team .box .image-container {
         padding: 3rem 3rem 2rem;
         position: relative;
      }
      
      .team .box img {
         width: 18rem;
         height: 18rem;
         object-fit: cover;
         border-radius: 50%;
         border: 5px solid #f0f0f0;
         transition: all 0.4s ease;
      }
      
      .team .box:hover img {
         transform: scale(1.05);
         border-color: var(--primary-light);
      }
      
      .team .box .content {
         padding: 0 3rem 3rem;
      }
      
      .team .box h3 {
         font-size: 2.2rem;
         color: var(--dark);
         margin-bottom: 0.5rem;
      }
      
      .team .box span {
         font-size: 1.5rem;
         color: var(--primary);
         display: block;
         margin-bottom: 2rem;
         font-weight: 600;
      }
      
      .team .box .share {
         display: flex;
         justify-content: center;
         gap: 1.5rem;
      }
      
      .team .box .share a {
         width: 4.5rem;
         height: 4.5rem;
         line-height: 4.5rem;
         text-align: center;
         background: #f0f0f0;
         border-radius: 50%;
         font-size: 1.8rem;
         color: var(--dark);
         transition: all 0.3s ease;
      }
      
      .team .box .share a:hover {
         background: var(--gradient);
         color: white;
         transform: translateY(-5px);
      }
      
      /* Reviews Section */
      .reviews {
         padding: 7rem 7%;
         background: linear-gradient(135deg, var(--dark) 0%, var(--primary) 100%);
         color: white;
         position: relative;
      }
      
      .reviews::before {
         content: "";
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100px;
         background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="%23f8f9fa"></path></svg>');
         background-size: cover;
         transform: rotate(180deg);
      }
      
      .reviews .heading {
         text-align: center;
         margin-bottom: 6rem;
         position: relative;
         z-index: 2;
      }
      
      .reviews .heading h1 {
         font-size: 3.5rem;
         margin-bottom: 1.5rem;
      }
      
      .reviews .heading p {
         font-size: 1.6rem;
         max-width: 700px;
         margin: 0 auto;
         opacity: 0.8;
      }
      
      .reviews .swiper {
         padding: 3rem 1rem;
         position: relative;
         z-index: 2;
      }
      
      .reviews .slide {
         background: rgba(255,255,255,0.1);
         border-radius: 20px;
         padding: 4rem 3rem;
         box-shadow: 0 10px 30px rgba(0,0,0,0.15);
         transition: all 0.4s ease;
         text-align: center;
         margin-bottom: 3rem;
         backdrop-filter: blur(10px);
         border: 1px solid rgba(255,255,255,0.2);
      }
      
      .reviews .slide:hover {
         transform: translateY(-10px);
         background: rgba(255,255,255,0.15);
         box-shadow: 0 15px 35px rgba(0,0,0,0.25);
      }
      
      .reviews .slide .image-container {
         width: 12rem;
         height: 12rem;
         margin: 0 auto 2rem;
         border-radius: 50%;
         border: 5px solid rgba(255,255,255,0.2);
         overflow: hidden;
         transition: all 0.4s ease;
      }
      
      .reviews .slide:hover .image-container {
         border-color: var(--accent);
         transform: scale(1.05);
      }
      
      .reviews .slide img {
         width: 100%;
         height: 100%;
         object-fit: cover;
      }
      
      .reviews .slide p {
         font-size: 1.6rem;
         line-height: 1.8;
         color: rgba(255,255,255,0.9);
         margin-bottom: 2rem;
         font-style: italic;
         position: relative;
      }
      
      .reviews .slide p::before,
      .reviews .slide p::after {
         content: """;
         font-size: 4rem;
         color: rgba(255,255,255,0.3);
         position: absolute;
      }
      
      .reviews .slide p::before {
         top: -15px;
         left: -15px;
      }
      
      .reviews .slide p::after {
         bottom: -25px;
         right: -15px;
      }
      
      .reviews .slide .stars {
         margin-bottom: 2rem;
      }
      
      .reviews .slide .stars i {
         font-size: 2rem;
         color: var(--accent);
         margin: 0 2px;
      }
      
      .reviews .slide h3 {
         font-size: 2.2rem;
         color: white;
         font-weight: 600;
      }
      
      /* Footer */
      .footer {
         background: var(--dark);
         color: white;
         padding: 5rem 7% 2rem;
         text-align: center;
      }
      
      .footer p {
         font-size: 1.6rem;
         padding: 1.5rem 0;
      }
      
      .footer p span {
         color: var(--accent);
      }
      
      /* Animations */
      @keyframes fadeInLeft {
         from { opacity: 0; transform: translateX(-50px); }
         to { opacity: 1; transform: translateX(0); }
      }
      
      @keyframes fadeInRight {
         from { opacity: 0; transform: translateX(50px); }
         to { opacity: 1; transform: translateX(0); }
      }
      
      /* Responsive */
      @media (max-width: 991px) {
         .hero h1 { font-size: 3.5rem; }
         .hero p { font-size: 1.4rem; }
         .header { padding: 1.5rem 5%; }
         .about, .stats, .team, .reviews { padding: 6rem 5%; }
      }
      
      @media (max-width: 768px) {
         .about .row {
            flex-direction: column;
         }
         
         .about .row .image {
            flex: 1 1 100%;
         }
         
         .about .row .content {
            flex: 1 1 100%;
            text-align: center;
         }
         
         .about .row .content h3::after {
            left: 50%;
            transform: translateX(-50%);
         }
         
         .hero h1 { font-size: 3rem; }
         .hero p { font-size: 1.3rem; }
      }
      
      @media (max-width: 450px) {
         .hero { height: 50vh; }
         .hero h1 { font-size: 2.5rem; }
         .hero p { font-size: 1.2rem; }
         .stats .box h3 { font-size: 3.5rem; }
         .stats .box p { font-size: 1.5rem; }
         .header { padding: 1.2rem 5%; }
         .about, .stats, .team, .reviews { padding: 5rem 5%; }
      }
   </style>
</head>
<body>
   
<!-- Header -->
<?php include 'components/user_header.php'; ?>

<!-- Hero Section -->
<section class="hero">
   <div class="hero-content">
      <h1 class="animate__animated animate__fadeInDown">Our Story</h1>
      <p class="animate__animated animate__fadeInUp animate__delay-1s">Discover the passion behind Nepal's premier footwear destination</p>
   </div>
</section>

<!-- About Section -->
<section class="about">
   <div class="row">
      <div class="image animate__animated animate__fadeInLeft">
         <img src="shoes collection picture/other/aboutus.jpg" alt="About Us">
      </div>
      <div class="content animate__animated animate__fadeInRight">
         <h3>Why Choose Kickster?</h3>
         <p>At <strong>Kickster</strong>, we bring you the latest and most stylish footwear at unbeatable prices. Whether you're looking for performance sneakers, everyday kicks, or fashion-forward shoes — we've got you covered.</p>
         <p>We partner directly with trusted brands and manufacturers to ensure 100% authenticity. Plus, we back every order with a <strong>30-day money-back guarantee</strong> and fast, reliable shipping. Your perfect pair is just a step away!</p>
         <a href="contact.php" class="btn">Contact Us</a>
      </div>
   </div>
</section>

<!-- Stats Section -->
<section class="stats">
   <div class="heading">
      <h2>Our Impact in Numbers</h2>
      <p>We take pride in our journey and the milestones we've achieved along the way</p>
   </div>
   
   <div class="box-container">
      <div class="box">
         <i class="fas fa-users"></i>
         <h3 class="count" data-count="10000">0</h3>
         <p>Happy Customers</p>
      </div>
      
      <div class="box">
         <i class="fas fa-shoe-prints"></i>
         <h3 class="count" data-count="25000">0</h3>
         <p>Shoes Sold</p>
      </div>
      
      <div class="box">
         <i class="fas fa-store"></i>
         <h3 class="count" data-count="15">0</h3>
         <p>Retail Stores</p>
      </div>
      
      <div class="box">
         <i class="fas fa-globe-asia"></i>
         <h3 class="count" data-count="45">0</h3>
         <p>Brands Available</p>
      </div>
   </div>
</section>

<!-- Team Section -->
<section class="team">
   <div class="heading">
      <h1>Meet Our Team</h1>
      <p>Our dedicated team of professionals works around the clock to ensure you get the best shopping experience</p>
   </div>
   
   <div class="box-container">
      <div class="box animate__animated">
         <div class="image-container">
            <img src="shoes collection picture/person/raj.jpg" alt="Raj Krishna Silwal">
         </div>
         <div class="content">
            <h3>Raj Krishna Silwal</h3>
            <span>Backend Developer</span>
            <div class="share">
               <a href="https://www.facebook.com/raz.silwal.397900"><i class="fab fa-facebook-f"></i></a>
               <a href="#"><i class="fab fa-twitter"></i></a>
               <a href="#"><i class="fab fa-instagram"></i></a>
               <a href="https://www.linkedin.com/in/raz-silwal-74812a198"><i class="fab fa-linkedin-in"></i></a>
            </div>
         </div>
      </div>
      
      <div class="box animate__animated">
         <div class="image-container">
            <img src="shoes collection picture/person/krishna.jpg" alt="Krishna Adhikari">
         </div>
         <div class="content">
            <h3>Krishna Adhikari</h3>
            <span>Frontend Developer</span>
            <div class="share">
               <a href="https://www.facebook.com/krishna.adhikari.198369"><i class="fab fa-facebook-f"></i></a>
               <a href="#"><i class="fab fa-twitter"></i></a>
               <a href="#"><i class="fab fa-instagram"></i></a>
               <a href="https://www.linkedin.com/in/krishna-adhikari-640b40209/"><i class="fab fa-linkedin-in"></i></a>
            </div>
         </div>
      </div>
   </div>
</section>

<!-- Reviews Section -->
<section class="reviews">
   <div class="heading">
      <h1>Client's Reviews</h1>
      <p>What our customers say about us</p>
   </div>

   <div class="swiper reviews-slider">
      <div class="swiper-wrapper">
         <div class="swiper-slide slide">
            <div class="image-container">
               <img src="shoes collection picture/person/1.jpg" alt="Customer Review">
            </div>
            <p>"Better shoes product than other competitors in market. The quality is exceptional!"</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
            </div>
            <h3>Mahesh Basnet</h3>
         </div>

         <div class="swiper-slide slide">
            <div class="image-container">
               <img src="shoes collection picture/person/2.jpg" alt="Customer Review">
            </div>
            <p>"Amazing shoes quality and the prices are very reasonable. Will shop again!"</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Sobraj Poudel</h3>
         </div>

         <div class="swiper-slide slide">
            <div class="image-container">
               <img src="shoes collection picture/person/3.jpg" alt="Customer Review">
            </div>
            <p>"The customer support team was very helpful when I had questions about my order."</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
            </div>
            <h3>Bhakta Tamanag</h3>
         </div>

         <div class="swiper-slide slide">
            <div class="image-container">
               <img src="shoes collection picture/person/4.jpg" alt="Customer Review">
            </div>
            <p>"I've ordered multiple times. The products always match the descriptions perfectly."</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
            </div>
            <h3>Prakash Chettri</h3>
         </div>

         <div class="swiper-slide slide">
            <div class="image-container">
               <img src="shoes collection picture/person/5.jpg" alt="Customer Review">
            </div>
            <p>"Best for shoes you want to buy original products. Fast delivery and great packaging!"</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Ramesh Sharma</h3>
         </div>
      </div>
      <div class="swiper-pagination"></div>
   </div>
</section>

<!-- Footer -->
<footer class="footer">
   <p>© <span>Kickster</span> 2023. All Rights Reserved</p>
</footer>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
<script>
// Initialize Swiper
const reviewsSwiper = new Swiper(".reviews-slider", {
   loop: true,
   spaceBetween: 30,
   autoplay: {
      delay: 5000,
      disableOnInteraction: false,
   },
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
   },
   breakpoints: {
      0: { slidesPerView: 1 },
      768: { slidesPerView: 2 },
      1024: { slidesPerView: 3 },
   },
});

// Counter Animation
document.addEventListener('DOMContentLoaded', () => {
   const counters = document.querySelectorAll('.count');
   const animationSpeed = 200; // Lower = faster animation

   counters.forEach(counter => {
      let hasAnimated = false;

      const animateCount = () => {
         const target = +counter.getAttribute('data-count');
         let current = 0;
         const step = Math.max(1, Math.ceil(target / animationSpeed));

         const updateCounter = () => {
            current += step;
            if (current < target) {
               counter.innerText = current;
               requestAnimationFrame(updateCounter);
            } else {
               counter.innerText = target;
            }
         };

         updateCounter();
      };

      const counterObserver = new IntersectionObserver((entries, obs) => {
         const entry = entries[0];
         if (entry.isIntersecting && !hasAnimated) {
            animateCount();
            hasAnimated = true;
            obs.unobserve(counter);
         }
      }, { threshold: 0.6 });

      counterObserver.observe(counter);
   });

   // Scroll-triggered animations for team boxes
   const teamBoxes = document.querySelectorAll('.team .box');
   teamBoxes.forEach((box, index) => {
      box.dataset.animate = 'animate__fadeInUp';
      box.style.animationDelay = `${index * 0.2}s`;
   });

   const animationObserver = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
         if (entry.isIntersecting) {
            const el = entry.target;
            const animationClass = el.dataset.animate;
            if (animationClass) {
               el.classList.add('animate__animated', animationClass);
               obs.unobserve(el);
            }
         }
      });
   }, { threshold: 0.1 });

   teamBoxes.forEach(el => {
      animationObserver.observe(el);
   });
});

// Header scroll effect
window.addEventListener('scroll', () => {
   const header = document.querySelector('.header');
   if (window.scrollY > 100) {
      header.style.background = 'var(--dark)';
      header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.2)';
   } else {
      header.style.background = 'linear-gradient(135deg, var(--dark) 0%, var(--primary) 100%)';
      header.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
   }
});

// Initialize GSAP animations
gsap.from('.hero-content', {
   duration: 1.5,
   y: 50,
   opacity: 0,
   ease: "power3.out"
});
</script>

</body>
</html>