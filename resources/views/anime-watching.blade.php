<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Watching | {{ $anime->title ?? 'Anime' }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/plyr.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" type="text/css">
    <link rel="icon" type="image/png" href="{{ asset('animekulogo1.png') }}">

    <style>
        .anime__video__player .plyr__volume input[type=range] { max-width: 70px !important; min-width: 50px !important; }
        .anime__video__player .plyr__controls .plyr__controls__item.plyr__time { left: 180px !important; z-index: 99 !important; }
        .iframe-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .episode-btn-style {
            background: #2b2b2b; color: #b7b7b7; border-radius: 4px; font-size: 14px; font-weight: 700;
            text-transform: uppercase; padding: 12px 18px; margin-right: 12px; margin-bottom: 12px;
            display: inline-block; border: none; transition: all 0.3s; min-width: 80px; text-align: center;
        }
        .episode-btn-style:hover { background: #e53637; color: #fff; }
        .active-episode { background: #e53637 !important; color: #fff !important; pointer-events: none; }
        .download-container { margin-top: 25px; padding-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.15); margin-bottom: 30px; }
        .btn-download-red { background: #e53637; color: #fff; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 12px 30px; border-radius: 4px; border: none; }
        .btn-download-red:hover { color: #fff; background: #c02c2d; }
    </style>
</head>

<body>
    <div id="preloder"><div class="loader"></div></div>
    @include('partials.vip-overlay')

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
                                {{-- PERUBAHAN DISINI: class="active" DIHAPUS agar tidak merah --}}
                                <li><a href="/">Homepage</a></li>
                                <li><a href="#">Categories <span class="arrow_carrot-down"></span></a>
                                    <ul class="dropdown">
                                        <li><a href="./genres">Genres</a></li>
                                        <li><a href="/signup.html">Sign Up</a></li>
                                        <li><a href="/login.html">Login</a></li>
                                    </ul>
                                </li>
                                <li>
                                    @auth
                                        @if(Auth::user()->subscription_type == 'VIP')
                                            <a href="javascript:void(0);" style="color: #ffd700; text-shadow: 0 0 10px rgba(255, 215, 0, 0.5); cursor: default;">
                                                VIP <i class="fa-solid fa-crown"></i>
                                            </a>
                                        @else
                                            <a href="#" id="vip-btn" class="vip-mobile-fix" style="color: white;">VIP</a>
                                        @endif
                                    @else
                                        <a href="#" id="vip-btn" class="vip-mobile-fix" style="color: white;">VIP</a>
                                    @endauth
                                </li>
                                <li><a href="/contact">Contacts</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                
                <div class="col-lg-2">
                    <div class="header__right" style="display: flex; justify-content: flex-end; align-items: center; height: 100%;">
                        <a href="#" class="search-switch" style="margin-right: 15px;"><span class="icon_search"></span></a>
                    
                        @auth
                            <a href="{{ route('profile') }}" style="display: flex; align-items: center; text-decoration: none;">
                                
                                {{-- FOTO PROFIL --}}
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset(Auth::user()->avatar) }}" 
                                         alt="Profile" 
                                         style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; flex-shrink: 0;">
                                @else
                                    <div style="width: 35px; height: 35px; background: #e53637; border-radius: 50%; color: #fff; text-align: center; line-height: 35px; font-weight: bold; font-size: 18px; border: 2px solid #fff; flex-shrink: 0;">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif

                                {{-- NAMA --}}
                                <div style="margin-left: 8px; line-height: 1.2;">
                                    @if(Auth::user()->subscription_type == 'VIP')
                                        <span style="color: #ffd700; font-weight: 600; font-size: 13px; text-shadow: 0 0 5px rgba(255, 215, 0, 0.5); display: block;">
                                            {{ Str::limit(Auth::user()->name, 8, '..') }}
                                        </span>
                                    @else
                                        <span style="color: white; font-weight: 600; font-size: 13px; display: block;">
                                            {{ Str::limit(Auth::user()->name, 8, '..') }}
                                        </span>
                                    @endif
                                </div>

                            </a>
                        @else
                            <a href="{{ route('login') }}"><span class="icon_profile"></span></a>
                        @endauth
                    </div>
                </div>

            </div>
            <div id="mobile-menu-wrap"></div>
        </div>
    </header>

    <div class="breadcrumb-option">
        <div class="container"><div class="row"><div class="col-lg-12"><div class="breadcrumb__links"><a href="/"><i class="fa fa-home"></i> Home</a> <span>{{ $anime->title ?? 'Anime' }}</span></div></div></div></div>
    </div>

    <section class="anime-details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    
                    {{-- 1. AREA PLAYER --}}
                    <div class="anime__video__player" id="player-wrapper">
                        @php
                            // Ambil data yang sudah diproses oleh Controller (fungsi getVideoSource)
                            // Variabel $current_video_data dikirim dari AnimeController@watching
                            $curr_ep = request()->query('ep', 1);
                            
                            $current_video_url = $current_video_data['video_url'];
                            $current_download_url = $current_video_data['download_url']; // Link download hasil konversi
                            $is_iframe = $current_video_data['is_iframe'];
                            $is_drive = $current_video_data['is_drive']; 
                        @endphp

                        @if($is_iframe)
                            <div class="iframe-container" style="position: relative; width: 100%; padding-bottom: 56.25%; height: 0; background: #000;">
                                <iframe src="{{ $current_video_url }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen allow="autoplay"></iframe>
                            </div>
                        @else
                            <video id="player" playsinline controls data-poster="{{ $anime->image ?? asset('videos/anime-watch.jpg') }}">
                                <source src="{{ $current_video_url }}" type="video/mp4" />
                            </video>
                        @endif
                    </div>

                    {{-- 2. AREA DOWNLOAD --}}
                    <div class="download-container">
                        <p style="color: white; margin-bottom: 10px; font-weight: bold; text-transform: uppercase;">Unduh Episode Saat Ini:</p>
                        
                        {{-- LOGIKA: Jika Iframe tapi Google Drive, tetap anggap Download biasa --}}
                        <a href="{{ $current_download_url }}" 
                        id="customDownloadLink" 
                        class="btn-download-red" 
                        target="{{ ($is_iframe && !$is_drive) ? '_blank' : '_self' }}" 
                        {{ ($is_iframe && !$is_drive) ? '' : 'download' }}>
                        
                            @if($is_iframe && !$is_drive) 
                                <i class="fa fa-external-link"></i> 
                            @else 
                                <i class="fa fa-download"></i> DOWNLOAD EP {{ $curr_ep }} 
                            @endif
                        </a>
                    </div>
                    
                    {{-- 3. AREA LIST EPISODE --}}
                    <div class="section-title"><h5>LIST EPISODE</h5></div>
                    <div class="anime__details__episodes">
                        @for($i = 1; $i <= $anime->total_episodes; $i++) 
                            @php
                                $isLocked = ($i > 3 && (!Auth::check() || Auth::user()->subscription_type == 'Free'));
                                $isActiveClass = ($curr_ep == $i) ? 'active-episode' : '';
                                
                                // Logic Kalkulasi Link Per Tombol
                                $this_url = "";
                                $this_mode = "video";
                                if (isset($episodesFromDB[$i])) {
                                    $l = trim($episodesFromDB[$i]->video_link);
                                    if (strpos($l, 'http')!==false) {
                                        $this_url = $l;
                                        // PERBAIKAN LOGIKA DETEKSI IFRAME TOMBOL
                                        if (strpos($l, 'dood')!==false || strpos($l, 'drive')!==false || strpos($l, 'tape')!==false || strpos($l, 'myvidplay')!==false || strpos($l, '/e/')!==false) {
                                            $this_mode = "iframe";
                                        }
                                    } else { $this_url = asset($l); }
                                } else {
                                    $e_str = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    $this_url = asset('videos/' . $anime->id . '/ep-' . $e_str . '.mp4');
                                }
                            @endphp

                            @if($isLocked)
                                <a href="javascript:void(0);" class="episode-btn-style vip-trigger" style="opacity: 0.5;">EP {{ $i }} <i class="fa fa-lock" style="font-size:10px; margin-left:5px;"></i></a>
                            @else
                                <a href="?id={{ $anime->id }}&ep={{ $i }}" 
                                   class="episode-btn-style play-episode {{ $isActiveClass }}" 
                                   data-video="{{ $this_url }}"
                                   data-episode="{{ $i }}"
                                   data-mode="{{ $this_mode }}">
                                    EP {{ $i }}
                                </a>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8 col-md-8">
                    <div class="anime__details__review">
                        <div class="section-title"><h5>Reviews</h5></div>
                        <div id="reviewContainer">
                            @if(isset($reviews) && count($reviews) > 0)
                                @foreach($reviews as $review)
                                    <div class="anime__review__item">
                                        <div class="anime__review__item__pic"><img src="{{ $review->avatar ? asset($review->avatar) : asset('img/anime/review-1.jpg') }}" style="width: 50px; height: 50px; border-radius: 50%;"></div>
                                        <div class="anime__review__item__text"><h6>{{ $review->name }}</h6><p>{{ $review->comment }}</p></div>
                                    </div>
                                @endforeach
                            @else <div class="alert alert-dark text-white">Belum ada ulasan.</div> @endif
                        </div>
                    </div>
                    <div class="anime__details__form">
                        <div class="section-title"><h5>Your Comment</h5></div>
                        @auth <form id="commentForm">@csrf <input type="hidden" name="anime_id" value="{{ $anime->id }}"> <textarea name="comment" placeholder="Tulis komentar..."></textarea> <button type="submit">Kirim</button></form>
                        @else <div class="alert alert-warning">Silakan <a href="/login.html" style="font-weight: bold; color: #e53637;">Login</a> untuk komentar.</div> @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer"><div class="container"><p class="text-center text-white">Copyright &copy; All rights reserved</p></div></footer>
    <div class="search-model"><div class="h-100 d-flex align-items-center justify-content-center flex-column"><div class="search-close-switch"><i class="icon_close"></i></div><form class="search-model-form"><input type="text" id="search-input" placeholder="Cari..."></form></div></div>

    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/player.js') }}"></script>
    <script src="{{ asset('js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('js/mixitup.min.js') }}"></script>
    <script src="{{ asset('js/jquery.slicknav.js') }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-_HnWFmEfaDl1oRr1"></script>
</body>
</html>