<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Anime Template">
    <meta name="keywords" content="Anime, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>Anime | Sign Up</title>

    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/plyr.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" type="text/css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
 c
    <link rel="icon" type="image/png" href="{{ asset('animekulogo1.png') }}">
    <style>
        /* === CUSTOM GOOGLE LOGIN UI === */
        .login__social__divider {
            position: relative;
            text-align: center;
            margin: 30px 0;
        }
        .login__social__divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
            z-index: 0;
        }
        .login__social__divider span {
            background: #0b0c2a; 
            padding: 0 15px;
            color: #b7b7b7;
            font-size: 12px;
            font-weight: 600;
            position: relative;
            z-index: 1;
            text-transform: uppercase;
        }

        .google-btn-custom {
            width: 100%;
            padding: 14px;
            background-color: #ffffff;
            color: #333333;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .google-btn-custom i {
            margin-right: 12px;
            font-size: 20px;
            color: #db4437;
            transition: 0.4s;
        }

        .google-btn-custom:hover {
            background-color: #E53637;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(229, 54, 55, 0.6);
        }

        .google-btn-custom:hover i {
            color: #ffffff;
        }

        /* === CUSTOM SWEETALERT THEME (Dark Mode) === */
        div:where(.swal2-container) div:where(.swal2-popup) {
            background: #0b0c2a !important;
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        div:where(.swal2-container) .swal2-title {
            color: #ffffff !important;
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
        }
        div:where(.swal2-container) .swal2-html-container {
            color: #b7b7b7 !important;
            font-family: 'Mulish', sans-serif;
        }
        div:where(.swal2-container) input.swal2-input {
            background: #0b0c2a !important;
            border: 1px solid #444 !important;
            color: #fff !important;
            margin: 15px auto;
        }
        div:where(.swal2-container) button.swal2-confirm {
            background-color: #E53637 !important;
            border-radius: 4px;
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
            box-shadow: none !important;
        }
    </style>
</head>

<body>
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-lg-2">
                    <div class="header__logo">
                        <a href="/">
                            <img src="{{ asset('img/logo2.png') }}" alt="" style="width: 100px;">
                        </a>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="header__nav">
                        <nav class="header__menu mobile-menu">
                            <ul>
                                <li><a href="/">Homepage</a></li>
                                <li><a href="#">Categories <span class="arrow_carrot-down"></span></a>
                                    <ul class="dropdown">
                                        <li><a href="./genres">Genres</a></li>
                                        <li><a href="/signup.html">Sign Up</a></li>
                                        <li><a href="/login.html">Login</a></li>
                                    </ul>
                                </li>
                                <li><a href="/contact">Contacts</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="header__right">
                        <a href="#" class="search-switch"><span class="icon_search"></span></a>
                        <a href="/login.html"><span class="icon_profile"></span></a>
                    </div>
                </div>
            </div>
            <div id="mobile-menu-wrap"></div>
        </div>
    </header>

    <section class="normal-breadcrumb set-bg" data-setbg="{{ asset('img/normal-breadcrumb.jpg') }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="normal__breadcrumb__text">
                        <h2>Sign Up</h2>
                        <p>Welcome to the official Anime blog.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="signup spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="login__form">
                        <h3>Sign Up</h3>
                        <form id="registerForm">
                            @csrf
                            <div class="input__item">
                                <input type="email" id="email" name="email" placeholder="Email address" required>
                                <span class="icon_mail"></span>
                            </div>
                            <div class="input__item">
                                <input type="text" id="name" name="name" placeholder="Your Name" required>
                                <span class="icon_profile"></span>
                            </div>
                            <div class="input__item">
                                <input type="password" id="password" name="password" placeholder="Password" required>
                                <span class="icon_lock"></span>
                            </div>
                            <button type="submit" class="site-btn">Register Now</button>
                        </form>
                        
                        <div id="registerMessage" style="margin-top: 15px; font-weight: bold;"></div>

                        <h5>Already have an account? <a href="/login.html">Log In!</a></h5>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="login__social__divider">
                        <span>OR CONTINUE WITH</span>
                    </div>

                    <div class="login__social__links">
                        <a href="{{ route('google.login') }}" class="google-btn-custom">
                            <i class="fa fa-google"></i> Sign up with Google
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="page-up">
            <a href="#" id="scrollToTopButton"><span class="arrow_carrot-up"></span></a>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="footer__logo">
                        <a href="/">
                            <img src="{{ asset('img/logo2.png') }}" alt="" style="width: 180px;">
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="footer__nav">
                        <ul>
                            <li class="active"><a href="/">Homepage</a></li>
                            <li><a href="categories.html">Categories</a></li>
                            <li><a href="nlogblog.html">Our Blog</a></li>
                            <li><a href="/contact">Contacts</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3">
                    <p>Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved</p>
                </div>
            </div>
        </div>
    </footer>

    <div class="search-model">
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="search-close-switch"><i class="icon_close"></i></div>
            <form class="search-model-form">
                <input type="text" id="search-input" placeholder="Search here.....">
            </form>
        </div>
    </div>

    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/player.js') }}"></script>
    <script src="{{ asset('js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('js/mixitup.min.js') }}"></script>
    <script src="{{ asset('js/jquery.slicknav.js') }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Setup Global CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                
                let formData = $(this).serialize();
                let submitBtn = $(this).find('button[type="submit"]');
                
                submitBtn.prop('disabled', true).text('Processing...');
                $('#registerMessage').html(''); 

                $.ajax({
                    url: "{{ route('register.process') }}", 
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        submitBtn.prop('disabled', false).text('Register Now');

                        // SKENARIO 1: EMAIL SUDAH ADA (CUSTOM POPUP)
                        if (response.status === 'exists') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Akun Sudah Ada',
                                text: response.message, // "Silahkan verifikasi akun Anda"
                                confirmButtonText: 'Ke Halaman Login',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Redirect ke Login agar user bisa input OTP di sana
                                    window.location.href = "{{ route('login') }}";
                                }
                            });
                        }
                        // SKENARIO 2: REGISTER SUKSES (MUNCULKAN OTP DI SINI)
                        else if (response.status === 'verify_needed') {
                            let userEmail = response.email;
                            
                            Swal.fire({
                                title: 'VERIFIKASI EMAIL',
                                text: 'Kode verifikasi telah dikirim ke ' + userEmail,
                                input: 'text',
                                inputPlaceholder: 'Masukkan Kode OTP',
                                inputAttributes: { maxlength: 6, style: 'text-align:center; letter-spacing: 2px;' },
                                showCancelButton: false,
                                confirmButtonText: 'VERIFIKASI',
                                showLoaderOnConfirm: true,
                                allowOutsideClick: false,
                                preConfirm: (otp) => {
                                    if (!otp) return Swal.showValidationMessage('Kode OTP wajib diisi');
                                    
                                    return $.ajax({
                                        url: "{{ route('register.verify') }}",
                                        type: 'POST',
                                        data: { email: userEmail, otp: otp }
                                    }).then(res => {
                                        if (res.status !== 'success') throw new Error(res.message);
                                        return res;
                                    }).catch(error => {
                                        Swal.showValidationMessage(`Gagal: ${error.responseJSON ? error.responseJSON.message : error.message}`);
                                    });
                                },
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'BERHASIL!',
                                        text: 'Akun Anda sudah aktif. Silakan Login.',
                                        confirmButtonText: 'Ke Halaman Login'
                                    }).then(() => {
                                        window.location.href = "{{ route('login') }}";
                                    });
                                }
                            });

                        } else if (response.status === 'success') {
                            Swal.fire('Sukses', response.message, 'success');
                            window.location.href = "{{ route('login') }}";
                        } else {
                            $('#registerMessage').html('<span class="text-danger">'+response.message+'</span>');
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).text('Register Now');
                        let err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                        $('#registerMessage').html('<span class="text-danger">'+err+'</span>');
                    }
                });
            });
        });
    </script>
</body>
</html>