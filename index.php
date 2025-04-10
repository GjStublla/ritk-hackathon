<?php
session_start();

$is_logged_in = $_SESSION['is_logged_in'] ?? $_COOKIE['is_logged_in'] ?? false;
$username = $_SESSION['username'] ?? $_COOKIE['username'] ?? null;
$user_role = $_SESSION['role'] ?? $_COOKIE['role'] ?? null;

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

if(isset($_GET['action'])) {
    if($_GET['action'] === "logout") {

        unset($_SESSION['username']);
        unset($_SESSION['is_logged_in']);
        unset($_SESSION['role']);
        session_destroy();


        setcookie("user_email", null, -1);
        setcookie("is_logged_in", null, -1);
        setcookie("role", null, -1);

        header("Location: index.php");
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hackathon Template</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
<section id="hero">
    <?php include './assets/components/navbar.php' ?>
    <div class="position-relative overflow-hidden hero d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row d-flex py-5 text-center align-items-center">
                <div class="col-md-6 text-white">
                    <h1 class="display-3 fw-bold">Find the Right Job. Anytime, Anywhere.</h1>
                    <p class="lead">Upload your resume, explore company reviews, and apply on the go—all in one place.</p>
                    <a href="#steps" class="btn btn-light mt-2 mx-auto hero-btn">Browse</a>
                </div>
                <div class="col-md-6 d-flex justify-content-center">
                    <img src="./assets/images/iphone.png" alt="iPhone mockup" class="img-fluid"
                         style="max-height: 800px;" />
                </div>
            </div>
        </div>
    </div>
</section>
<section id="cards">
    <h2 class="text-center py-2 mt-1">Sponsored</h2>
    <div class="container mt-2">
        <div class="panel active"
             style="background-image: url('./assets/images/ipko.png')">
        </div>
        <div class="panel"
             style="background-image: url('./assets/images/kodelabs.png')">
        </div>
        <div class="panel"
             style="background-image: url('./assets/images/milkyway.png')">
        </div>
        <div class="panel"
             style="background-image: url('./assets/images/pentester.png')">
        </div>
        <div class="panel"
             style="background-image: url('./assets/images/linkedplus.png')">
        </div>
    </div>
</section>

<section id="steps">
    <div class="container">
        <!-- Introduction -->
        <div class="row py-4">
            <div class="col-12 text-center">
                <h2>Simple Steps to Better Hiring & Career Growth</h2>
                <p>
                    The world of work is evolving fast — and navigating it can feel overwhelming. With countless platforms, buzzwords, and shifting trends, it's easy to lose sight of what truly matters in building a meaningful career or finding the right hire.
                    <br><br>
                    But success isn't just about flashy job titles or big budgets. It's about clarity, purpose, and smart action. Whether you're an employer looking to connect with great talent or a job seeker ready to make your next move — your approach makes all the difference.
                    <br><br>
                    Here are ten focused steps to help you hire better, grow smarter, and make the most of your journey.
                </p>
            </div>
        </div>

        <!-- Steps 1 and 2 -->
        <div class="row py-3">
            <!-- Step 1 -->
            <div class="col-md-6">
                <h2>1. Create Value for Others</h2>
                <p>Success in the hiring world starts with giving. Whether you're a company providing meaningful work or a candidate offering your skills, value flows both ways. Support, clarity, and respect build trust — and trust builds great teams.</p>
                <ul>
                    <h3>Action ideas</h3>
                    <li>Write clear, honest job descriptions that reflect your values and culture.</li>
                    <li>Offer feedback to candidates — even if they’re not the right fit. It builds your reputation.</li>
                </ul>
            </div>

            <!-- Image -->
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1726137569906-14f8079861fa?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDF8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                     class="img-fluid rounded-4" alt="Teamwork and growth">
            </div>
        </div>

        <!-- Steps 3 and 4 -->
        <div class="row py-5">
            <!-- Step 3 -->
            <div class="col-md-6">
                <h2>3. Invest in Your Wellbeing</h2>
                <p>Hiring and job searching can be demanding — don’t burn out. Your energy, clarity, and mindset shape how you show up. Taking care of yourself helps you make better decisions, stay resilient, and attract the right opportunities or talent.</p>
                <ul>
                    <h3>Action ideas</h3>
                    <li>Schedule focused time to review applications or prep for interviews — without distractions.</li>
                    <li>Stay active, eat well, and rest. You make sharper moves when you’re at your best.</li>
                </ul>
            </div>

            <!-- Step 4 -->
            <div class="col-md-6">
                <h2>4. Stay Aware of the Landscape</h2>
                <p>It’s easy to focus inward when hiring or job hunting. But awareness is your superpower. Pay attention to industry shifts, candidate expectations, and what other companies or professionals are doing. That insight helps you stand out.</p>
                <ul>
                    <h3>Action ideas</h3>
                    <li>Review top listings in your field. What makes them effective? How can you stand apart?</li>
                    <li>Pause regularly to reflect: What’s working in your process? What needs refining?</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section id="pricing">
    <div class="container py-2">
        <div class="row text-center mb-5">
            <div class="col">
                <h2>Choose Your Plan</h2>
                <p class="text-muted">Select the perfect plan for your needs</p>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Basic Plan -->
            <div class="col">
                <div class="card h-100 pricing-card shadow-sm">
                    <div class="card-body p-5">
                        <h5 class="card-title text-muted text-uppercase mb-4">Starter</h5>
                        <h1 class="display-5 mb-4">$19<small class="text-muted fw-light">/mo</small></h1>
                        <ul class="list-unstyled feature-list">
                            <li><i class="bi bi-check2 text-primary me-2"></i>Job visible for 30 days</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Included in job listings index</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Standard social media promotion</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>No featured placement or refresh</li>
                        </ul>
                        <a href="#contact" class="btn btn-outline-primary btn-lg w-100 mt-4">Get Started</a>

                    </div>
                </div>
            </div>

            <!-- Pro Plan -->
            <div class="col">
                <div class="card h-100 pricing-card shadow position-relative">
                    <span class="badge gradient-custom text-white popular-badge px-4 py-2">Popular</span>
                    <div class="card-body p-5">
                        <h5 class="card-title text-primary text-uppercase mb-4">Professional</h5>
                        <h1 class="display-5 mb-4">$49<small class="text-muted fw-light">/mo</small></h1>
                        <ul class="list-unstyled feature-list">
                            <li><i class="bi bi-check2 text-primary me-2"></i>Job stays live for 45 days</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Index listing + homepage slot</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Posted on our social media</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>1 automatic refresh (after 7 weeks)</li>
                        </ul>
                        <a href="#contact" class="btn gradient-custom text-white btn-lg w-100 mt-4">Get Started</a>

                    </div>
                </div>
            </div>

            <!-- Enterprise Plan -->
            <div class="col">
                <div class="card h-100 pricing-card shadow-sm">
                    <div class="card-body p-5">
                        <h5 class="card-title text-muted text-uppercase mb-4">Enterprise</h5>
                        <h1 class="display-5 mb-4">$99<small class="text-muted fw-light">/mo</small></h1>
                        <ul class="list-unstyled feature-list">
                            <li><i class="bi bi-check2 text-primary me-2"></i>Job stays live for 60 days</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Top of index + homepage spotlight</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Social media & newsletter promotion</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Auto-refresh every 7 weeks</li>
                        </ul>
                        <a href="#contact" class="btn btn-outline-primary btn-lg w-100 mt-4">Get Started</a>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="testimonials">
    <div class="container py-5">
        <h2 class="text-center mb-5">What Our Customers Say</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="testimonial-card p-4 p-md-5">
                                <div class="text-center mb-4">
                                    <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Avatar"
                                         class="avatar mb-3">
                                    <h5 class="mb-1">Emma Thompson</h5>
                                    <p class="text-muted mb-0">Marketing Manager</p>
                                </div>
                                <p class="lead text-center mb-0">"This product has completely transformed our
                                    workflow.
                                    It's intuitive, powerful, and a joy to use every day. I can't imagine
                                    running our
                                    business without it now."</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="testimonial-card p-4 p-md-5">
                                <div class="text-center mb-4">
                                    <img src="https://randomuser.me/api/portraits/men/47.jpg" alt="Avatar"
                                         class="avatar mb-3">
                                    <h5 class="mb-1">Michael Chen</h5>
                                    <p class="text-muted mb-0">Software Engineer</p>
                                </div>
                                <p class="lead text-center mb-0">"The level of customer support is outstanding.
                                    Whenever
                                    I've had a question or issue, the team has been quick to respond and always
                                    goes
                                    above and beyond to help."</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="testimonial-card p-4 p-md-5">
                                <div class="text-center mb-4">
                                    <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Avatar"
                                         class="avatar mb-3">
                                    <h5 class="mb-1">Sophia Rodriguez</h5>
                                    <p class="text-muted mb-0">Small Business Owner</p>
                                </div>
                                <p class="lead text-center mb-0">"As a small business owner, I was hesitant to
                                    invest in
                                    new software, but this has paid for itself many times over. It's been a
                                    game-changer
                                    for my company's efficiency."</p>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel"
                            data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel"
                            data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="0"
                                class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="1"
                                aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="2"
                                aria-label="Slide 3"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact">
    <div class="container py-5">
        <h2 class="text-center">Contact Us</h2>
        <div class="row pt-3">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <form class="row g-3">
                    <div class="col-md-6">
                        <label for="inputFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="inputFirstName" placeholder="John">
                    </div>
                    <div class="col-md-6">
                        <label for="inputLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="inputLastName" placeholder="Doe">
                    </div>
                    <div class="col-12">
                        <label for="inputEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="inputEmail" placeholder="example@email.com">
                    </div>
                    <div class="mb-2">
                        <label for="exampleFormControlTextarea1" class="form-label">Message</label>
                        <textarea class="form-control" id="exampleFormControlTextarea1" rows="4"></textarea>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-dark mb-3" style="width: 80px;">Send</button>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2933.576199222431!2d21.189687275986923!3d42.670335471165785!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x135498cd75256c27%3A0xbbf87d6b640faf83!2sRIT%20Kosovo%20(A.U.K)!5e0!3m2!1sen!2s!4v1744061160058!5m2!1sen!2s"
                        height="450" style="border:0; width:100%; border-radius: 20px;" allowfullscreen=""
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Google Maps"></iframe>
            </div>
        </div>
        <!-- <div class="row">
            <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2933.576199222431!2d21.189687275986923!3d42.670335471165785!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x135498cd75256c27%3A0xbbf87d6b640faf83!2sRIT%20Kosovo%20(A.U.K)!5e0!3m2!1sen!2s!4v1744061160058!5m2!1sen!2s"
            height="450" style="border:0; width:100%; border-radius: 20px;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div> -->
    </div>
</section>

<?php include './assets/components/footer.php' ?>

<!-- Back to Top Button -->
<a id="backtotop-button" style="text-decoration: none">↑</a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
</script>
<script>
    const btn = document.querySelector("#backtotop-button");

    window.addEventListener("scroll", () => {
        if (window.scrollY > 300) {
            btn.classList.add("show");
        } else {
            btn.classList.remove("show");
        }
    });

    btn.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
</script>
<script>
    window.addEventListener('scroll', function () {

        var scrollPosition = document.documentElement.scrollTop || document.body.scrollTop;

        if (scrollPosition > 100) {
            document.querySelector('nav').classList.add('scrolled');
            var navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function (link) {
                link.classList.add('scrolled');
            });
        } else {
            document.querySelector('nav').classList.remove('scrolled');
            var navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function (link) {
                link.classList.remove('scrolled');
            });
        }
    });
</script>
<script>
    const panels = document.querySelectorAll('.panel')

    panels.forEach(panel => {
        panel.addEventListener('click', () => {
            removeActiveClasses()
            panel.classList.add('active')
        })
    })

    function removeActiveClasses() {
        panels.forEach(panel => {
            panel.classList.remove('active')
        })
    }
</script>

</body>

</html>