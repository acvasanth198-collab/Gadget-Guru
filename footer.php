<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Note: You might need to link Font Awesome here if not already linked in the main page -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* 
         * This style block should ideally be in your main CSS file (like shome.css)
         * to avoid repetition. It's placed here directly as requested.
        */

        /* CSS Variables for theme consistency (assuming they are defined in a parent scope) */
        :root {
            --primary-color: #e74c3c;
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --border-color: #333333;
            --font-family-headings: 'Roboto', sans-serif;
            --font-family-body: 'Poppins', sans-serif;
        }
        
        /* --- SITE FOOTER STYLES (DARK THEME) --- */
        .site-footer {
            background-color: #000000; /* Pure black background for the footer area */
            color: var(--text-secondary, #a0a0a0); /* Default text color for the footer */
            padding-top: 3rem;
            border-top: 1px solid var(--border-color, #333333);
            margin-top: 4rem; /* Adds space above the footer */
        }

        .site-footer .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 2rem 2rem;
            display: grid;
            /* Creates three columns, with the first being wider */
            grid-template-columns: 2fr 1fr 1fr;
            gap: 2.5rem;
        }

        /* Styling for the 'about' column, including the logo and paragraph */
        .site-footer .footer-column.about .logo {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
            text-decoration: none;
        }

        .site-footer .footer-column .logo-icon {
            color: var(--primary-color, #e74c3c); /* Red icon */
            font-size: 2rem;
        }

        .site-footer .footer-column .logo-text {
            color: var(--text-primary, #ffffff); /* White text */
            font-size: 1.7rem;
            font-weight: 700;
            font-family: var(--font-family-headings, 'Roboto', sans-serif);
        }

        .site-footer .footer-column .logo-text span {
            color: var(--primary-color, #e74c3c); /* Red accent on "Guru" */
        }

        .site-footer .footer-column.about p {
            line-height: 1.7;
            max-width: 400px;
        }

        /* General styling for footer column headings */
        .site-footer .footer-column h4 {
            color: var(--text-primary, #ffffff);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Styling for the quick links */
        .site-footer .footer-column.links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .site-footer .footer-column.links li {
            margin-bottom: 0.8rem;
        }

        .site-footer .footer-column.links a {
            color: var(--text-secondary, #a0a0a0);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .site-footer .footer-column.links a:hover {
            color: var(--primary-color, #e74c3c); /* Link turns red on hover */
            padding-left: 8px; /* Adds a nice indent effect on hover */
        }

        /* Styling for the social media icons */
        .site-footer .social-links {
            display: flex;
            gap: 1rem;
        }

        .site-footer .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background-color: var(--card-bg, #1e1e1e); /* Dark background for icons */
            color: var(--text-primary, #ffffff);
            font-size: 1.1rem;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .site-footer .social-links a:hover {
            background-color: var(--primary-color, #e74c3c); /* Icon background turns red */
            color: #fff;
            transform: translateY(-4px); /* Adds a subtle "lift" effect on hover */
        }

        /* Styling for the bottom copyright bar */
        .site-footer .footer-bottom {
            text-align: center;
            padding: 1.5rem 0;
            margin-top: 2rem;
            border-top: 1px solid var(--border-color, #333333);
            font-size: 0.9rem;
        }

        /* --- RESPONSIVE DESIGN FOR FOOTER --- */
        @media (max-width: 820px) {
            .site-footer .footer-content {
                /* Stacks the columns on top of each other on smaller screens */
                grid-template-columns: 1fr;
                text-align: center; /* Centers text for all columns */
            }

            .site-footer .footer-column.about p {
                margin-left: auto;
                margin-right: auto;
            }

            .site-footer .footer-column.about .logo,
            .site-footer .social-links {
                /* Centers the logo and social icons within their container */
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    
    <!-- Your main page content would go here -->

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-column about">
                <div class="logo">
                    <span class="logo-icon"><i class="fas fa-mobile-alt"></i></span>
                    <span class="logo-text">Gadget <span>Guru</span></span>
                </div>
                <p>Your ultimate destination for unbiased mobile phone comparison , reviews and specifications. Helping you find the perfect gadget since 2025.</p>
            </div>
            <div class="footer-column links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="shome.php">Home</a></li>
                    <li><a href="bvp.php">Best Value Phones</a></li>
                    <li><a href="bcp.php">Best Camera Phones</a></li>
                    <li><a href="trp.php">Top Rated Phones</a></li>
                </ul>
            </div>
            <div class="footer-column social">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> Gadget Guru. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>