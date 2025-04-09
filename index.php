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
                        <h1 class="display-3 fw-bold">Modern Day Website</h1>
                        <p class="lead">By Mark Williamson and Vanessa King</p>
                        <a href="#steps" class="btn btn-light mt-2 mx-auto hero-btn">Browse</a>
                    </div>
                    <div class="col-md-6 d-flex justify-content-center">
                        <img src="./assets/images/iphone.png" alt="iPhone mockup" class="img-fluid"
                            style="max-height: 700px;" />
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="cards">
        <div class="container">
            <div class="panel active"
                style="background-image: url('https://images.unsplash.com/photo-1558979158-65a1eaa08691?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1350&q=80')">
                <h3>Explore The World</h3>
            </div>
            <div class="panel"
                style="background-image: url('https://images.unsplash.com/photo-1572276596237-5db2c3e16c5d?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1350&q=80')">
                <h3>Wild Forest</h3>
            </div>
            <div class="panel"
                style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1353&q=80')">
                <h3>Sunny Beach</h3>
            </div>
            <div class="panel"
                style="background-image: url('https://images.unsplash.com/photo-1551009175-8a68da93d5f9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1351&q=80')">
                <h3>City on Winter</h3>
            </div>
            <div class="panel"
                style="background-image: url('https://images.unsplash.com/photo-1549880338-65ddcdfd017b?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1350&q=80')">
                <h3>Mountains - Clouds</h3>
            </div>
        </div>
    </section>

    <section id="steps">
        <div class="container">
            <!-- Bit of background part -->
            <div class="row py-4">
                <div class="col-12 text-center">
                    <h2>Ten Easy Steps to Better Living</h2>
                    <p>
                        We’re constantly bombarded with messages about what makes for a good life. Advertisers tell us
                        it
                        comes from owning and consuming their products. The media associate it with wealth, beauty or
                        fame.
                        And politicians claim that nothing matters more than growing the economy. But do any of these
                        things
                        really bring lasting happiness?

                        For thousands of years, people have looked to philosophy, religion and grandmotherly wisdom for
                        answers to such questions. But in recent decades this ancient wisdom has been tested by
                        scientific
                        research.

                        Scientists have found that although our genes and circumstances matter, a huge proportion of the
                        variations in happiness between us come from our choices and activities. So although we may not
                        be
                        able to change our inherited characteristics or the circumstances in which we find ourselves, we
                        still have the power to change how happy we are – by the way we approach our lives.
                    </p>
                </div>
            </div>
            <!-- Steps 1 and 2 -->
            <div class="row py-3">
                <!-- Step 1: Do things for others -->
                <div class="col-md-6">
                    <h2>1. Do things for others</h2>
                    <p>Caring about others is fundamental to our happiness. Helping other people is not only good for
                        them; it’s good for us too. It makes us happier and can help to improve our health. Giving also
                        creates stronger connections between people and helps to build a happier society for everyone.
                        It’s not all about money - we can also give our time, ideas and energy. So if you want to feel
                        good, do good.</p>
                    <ul>
                        <h3>Action ideas</h3>
                        <li>Do three extra acts of kindness today. Offer to help, give away your change, pay a
                            compliment, or make someone smile.</li>
                        <li>Reach out to help someone who’s struggling. Give them a call or offer your support. Let them
                            know you care.</li>
                    </ul>
                </div>

                <!-- Image -->
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1726137569906-14f8079861fa?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDF8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                        class="img-fluid">
                </div>
            </div>

            <!-- Steps 3 and 4 -->
            <div class="row py-5">
                <!-- Step 3: Take care of your body -->
                <div class="col-md-6">
                    <h2>3. Take care of your body</h2>
                    <p>Our body and mind are connected. Being active makes us happier as well as healthier. It instantly
                        improves our mood and can even lift us out of depression. We don’t all have to run marathons -
                        there are simple things we can do to be more active each day. We can also boost our wellbeing by
                        spending time outdoors, eating healthily, unplugging from technology and getting enough sleep.
                    </p>
                    <ul>
                        <h3>Action ideas</h3>
                        <li>Be more active today. Get off the bus a stop early, take the stairs, turn off the TV, go for
                            a walk – anything that gets you moving.</li>
                        <li>Eat nutritious food, drink more water, catch up on sleep. Notice which healthy actions lift
                            your mood and do more of them.</li>
                    </ul>
                </div>

                <!-- Step 4: Notice the world around you -->
                <div class="col-md-6">
                    <h2>4. Notice the world around you</h2>
                    <p>Have you ever felt there must be more to life? Good news – there is. And it’s right here in front
                        of us. We just need to stop and take notice. Learning to be more mindful and aware does wonders
                        for our wellbeing, whether it’s on our walk to work, in the way we eat or in our relationships.
                        It helps us get in tune with our feelings and stops us dwelling on the past or worrying about
                        the future.</p>
                    <ul>
                        <h3>Action ideas</h3>
                        <li>Give yourself a bit of head space. At least once a day, stop and take five minutes to just
                            breathe and be in the moment.</li>
                        <li>Notice and appreciate good things around you every day, big or small. Trees, birdsong, the
                            smell of coffee, laughter perhaps?</li>
                    </ul>
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