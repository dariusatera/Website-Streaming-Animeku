"use strict";

// =========================================================
// BAGIAN 1: GLOBAL FUNCTION (PEMBAYARAN / CHECKOUT)
// =========================================================
window.checkout = function(paket, harga) {
    if (typeof Swal === 'undefined') {
        alert("Library SweetAlert Gagal Dimuat! Cek koneksi internet.");
        return;
    }
    
    if (typeof window.snap === 'undefined') {
        Swal.fire('Sistem Belum Siap', 'Tunggu sebentar (Snap.js sedang memuat)...', 'warning');
        return;
    }

    Swal.fire({
        title: 'Memproses...', 
        text: 'Menghubungkan ke gateway pembayaran...',
        allowOutsideClick: false, 
        didOpen: () => { Swal.showLoading() }
    });

    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    if(!csrfToken) {
        Swal.close();
        Swal.fire('Error', 'CSRF Token hilang. Silakan refresh halaman.', 'error');
        return;
    }

    fetch('/payment/process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ paket: paket, harga: harga })
    })
    .then(response => {
        if (response.status === 401) {
            Swal.fire({
                icon: 'warning',
                title: 'Login Diperlukan',
                text: 'Silakan login atau daftar akun untuk membeli paket VIP.',
                confirmButtonText: 'Login Sekarang'
            }).then((result) => { 
                if (result.isConfirmed) window.location.href = '/login.html'; 
            });
            throw new Error("Belum Login");
        }
        if (!response.ok) throw new Error("Gagal memproses request");
        return response.json();
    })
    .then(data => {
        Swal.close();
        if(data && data.snap_token) {
            window.snap.pay(data.snap_token, {
                onSuccess: function(result){
                    Swal.fire('Berhasil!', 'Pembayaran sukses! Akun Anda kini Premium.', 'success')
                    .then(() => { window.location.reload(); });
                },
                onPending: function(result){ 
                    Swal.fire('Menunggu', 'Silakan selesaikan pembayaran Anda.', 'info'); 
                },
                onError: function(result){ 
                    Swal.fire('Gagal', 'Pembayaran gagal atau dibatalkan.', 'error'); 
                },
                onClose: function(){ 
                    Swal.fire('Dibatalkan', 'Anda menutup jendela pembayaran.', 'warning'); 
                }
            });
        } else {
            Swal.fire('Gagal', data.error || 'Terjadi kesalahan saat membuat transaksi.', 'error');
        }
    })
    .catch(error => {
        console.error(error);
        if(error.message !== "Belum Login") Swal.fire('Error', 'Gagal menghubungi server.', 'error');
    });
};

// =========================================================
// BAGIAN 2: LOGIKA UI, MODAL VIP & PLAYER (VANILLA JS)
// =========================================================
document.addEventListener('DOMContentLoaded', function() {
    
    // --- A. MODAL VIP (Buka/Tutup) ---
    const vipOverlay = document.getElementById('vip-overlay');
    
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('#vip-btn') || e.target.closest('.vip-trigger') || e.target.closest('.vip-mobile-fix')) {
            e.preventDefault();
            if (vipOverlay) {
                vipOverlay.style.display = 'flex';
                setTimeout(() => { vipOverlay.classList.add('active'); }, 10);
            }
        }

        if (e.target.closest('.vip-close') || e.target === vipOverlay) {
            if (vipOverlay) {
                vipOverlay.classList.remove('active');
                setTimeout(() => { vipOverlay.style.display = 'none'; }, 300);
            }
        }
    });

    // --- B. LOGIKA PLAYER & GANTI EPISODE (AJAX) ---
    const playerWrapper = document.getElementById('player-wrapper'); 
    const downloadBtn = document.getElementById('customDownloadLink');
    let playerInstance = null; 

    // 1. Inisialisasi awal jika ada video
    if (document.getElementById('player') && typeof Plyr !== 'undefined') {
         playerInstance = new Plyr('#player', { controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'] });
    }

    // 2. Event Listener Tombol Episode
    const episodeButtons = document.querySelectorAll('.play-episode');
    episodeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const animeId = this.getAttribute('data-id') || getParameterByName('id');
            const episodeNum = this.getAttribute('data-ep') || this.getAttribute('data-episode');

            fetch(`/anime/get-episode?id=${animeId}&ep=${episodeNum}`)
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'locked') {
                        if(typeof Swal !== 'undefined') Swal.fire('Terkunci', data.message, 'warning');
                        else alert(data.message);
                        return;
                    }

                    if(data.status === 'success') {
                        // Update Player
                        if (data.is_iframe) {
                            if (playerInstance) { playerInstance.destroy(); playerInstance = null; }
                            playerWrapper.innerHTML = `
                                <div class="iframe-container" style="position: relative; width: 100%; padding-bottom: 56.25%; height: 0; background: #000;">
                                    <iframe src="${data.video_url}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen allow="autoplay" sandbox="allow-scripts allow-same-origin allow-presentation"></iframe>
                                </div>`;
                        } else {
                            if (!document.getElementById('player')) {
                                playerWrapper.innerHTML = `
                                    <video id="player" playsinline controls style="width:100%" poster="${data.poster}">
                                        <source src="${data.video_url}" type="video/mp4" />
                                    </video>`;
                                if(typeof Plyr !== 'undefined') playerInstance = new Plyr('#player', { controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'] });
                            } else {
                                if (playerInstance) playerInstance.source = { type: 'video', sources: [{ src: data.video_url, type: 'video/mp4' }], poster: data.poster };
                            }
                            if(playerInstance) playerInstance.play();
                        }

                        // Update Download Button
                        if(downloadBtn) {
                            downloadBtn.href = data.download_url;
                            if(data.is_iframe && !data.is_drive) {
                                downloadBtn.innerHTML = '<i class="fa fa-external-link"></i> BUKA LINK ASLI';
                                downloadBtn.removeAttribute('download');
                                downloadBtn.target = "_blank";
                            } else {
                                downloadBtn.innerHTML = '<i class="fa fa-download"></i> DOWNLOAD EP ' + data.episode;
                                downloadBtn.setAttribute('download', '');
                                downloadBtn.target = "_self";
                            }
                        }

                        // Update Active Button
                        episodeButtons.forEach(b => {
                            b.classList.remove('active-episode');
                            b.style.background = '#2b2b2b'; 
                            b.style.color = '#b7b7b7';
                        });
                        btn.classList.add('active-episode');
                        btn.style.background = '#e53637'; 
                        btn.style.color = '#fff';
                        
                        // Update Browser URL
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('ep', episodeNum);
                        window.history.pushState({path: currentUrl.toString()}, '', currentUrl.toString());
                    }
                })
                .catch(err => console.error(err));
        });
    });

    function getParameterByName(name) {
        let match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
        return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
    }

    // --- C. FETCH DATA ANIME (Search & Homepage) ---
    if ((document.getElementById("trending-data") || document.getElementById("search-input"))) {
        fetch("/anime-data")
            .then((response) => response.json())
            .then((animeData) => {
                const allAnimeDatabase = [...animeData].filter(
                    (value, index, self) => index === self.findIndex((t) => t.title === value.title)
                );

                function renderAnimeList(dataArray, containerId) {
                    const container = document.getElementById(containerId);
                    if (!container) return;
                    let htmlContent = "";
                    dataArray.forEach((anime) => {
                        let tagsHtml = (anime.tags || []).map(tag => `<li>${tag}</li>`).join('');
                        const link = `/anime-details.html?id=${anime.id}`; 
                        
                        htmlContent += `
                            <div class="col-lg-4 col-md-6 col-sm-6">
                                <div class="product__item">
                                    <div class="product__item__pic set-bg" data-setbg="${anime.image}" style="background-image: url('${anime.image}');">
                                        <div class="ep">${anime.ep || "?"}</div>
                                        <div class="comment"><i class="fa fa-comments"></i> ${anime.view || 0}</div> <div class="view"><i class="fa fa-eye"></i> ${anime.view || 0}</div>
                                    </div>
                                    <div class="product__item__text">
                                        <ul>${tagsHtml}</ul>
                                        <h5><a href="${link}">${anime.title}</a></h5>
                                    </div>
                                </div>
                            </div>`;
                    });
                    container.innerHTML = htmlContent;
                }

                if (document.getElementById("trending-data")) renderAnimeList(allAnimeDatabase.slice(0, 3), "trending-data");
                if (document.getElementById("popular-data")) renderAnimeList(allAnimeDatabase.slice(0, 6), "popular-data");
                if (document.getElementById("recent-data")) renderAnimeList(allAnimeDatabase.slice(0, 6), "recent-data");
                if (document.getElementById("live-data")) renderAnimeList(allAnimeDatabase.slice(0, 6), "live-data");

                const searchInput = document.getElementById("search-input");
                const searchResultContainer = document.getElementById("search-result-container");
                if (searchInput && searchResultContainer) {
                    searchInput.addEventListener("keyup", function (e) {
                        const query = e.target.value.toLowerCase();
                        searchResultContainer.innerHTML = "";
                        if (query.length < 2) {
                            searchResultContainer.innerHTML = '<h3 style="color:white; text-align:center;">Ketik minimal 2 karakter...</h3>';
                            return;
                        }
                        const filteredAnime = allAnimeDatabase.filter((anime) => anime.title.toLowerCase().includes(query));
                        if (filteredAnime.length > 0) {
                            renderAnimeList(filteredAnime, "search-result-container");
                        } else {
                            searchResultContainer.innerHTML = '<h3 style="color:white; text-align:center;">Anime tidak ditemukan :(</h3>';
                        }
                    });
                }
            })
            .catch((error) => console.log("Info: API Anime Data belum siap/gagal load.", error));
    }
});

// =========================================================
// BAGIAN 3: PLUGINS, AUTH, KOMENTAR & FILTER (JQUERY)
// =========================================================
(function ($) {
    $(window).on("load", function () {
        $(".loader").fadeOut();
        $("#preloder").delay(200).fadeOut("slow");
        if ($(".filter__gallery").length > 0) mixitup(".filter__gallery");
    });

    $(".set-bg").each(function () {
        var bg = $(this).data("setbg");
        if (bg) $(this).css("background-image", "url(" + bg + ")");
    });

    $(".search-switch").on("click", function () { $(".search-model").fadeIn(400); });
    $(".search-close-switch").on("click", function () { $(".search-model").fadeOut(400); });
    $(".mobile-menu").slicknav({ prependTo: "#mobile-menu-wrap", allowParentLinks: true });
    $(".hero__slider").owlCarousel({ loop: true, margin: 0, items: 1, dots: true, nav: true, smartSpeed: 1200, autoplay: true });
    $("select").niceSelect();
    $("#scrollToTopButton").click(function () { $("html, body").animate({ scrollTop: 0 }, "slow"); return false; });

    // --- FITUR FILTER ACTIVE CLASS (YANG ANDA MINTA) ---
    $('.filter__controls li').on('click', function() {
        $('.filter__controls li').removeClass('active');
        $(this).addClass('active');
    });

    $(document).ready(function () {
        $.ajaxSetup({ headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") } });

        // Register
        $("#registerForm").on("submit", function (e) {
            e.preventDefault();
            var btn = $(this).find("button");
            var messageBox = $("#registerMessage");
            btn.text("Processing...").prop("disabled", true);
            
            $.ajax({
                url: "/register-process", type: "POST", data: $(this).serialize(),
                success: function (res) {
                    if (res.status === "success") {
                        messageBox.html('<span style="color:green;">' + res.message + " Redirecting...</span>");
                        setTimeout(() => window.location.href = "./login.html", 2000);
                    } else {
                        messageBox.html('<span style="color:red;">' + res.message + "</span>");
                        btn.text("Register").prop("disabled", false);
                    }
                },
                error: function () { messageBox.html('<span style="color:red;">Server Error</span>'); btn.prop("disabled", false); }
            });
        });

        // Login
        $("#loginForm").on("submit", function (e) {
            e.preventDefault();
            var btn = $(this).find("button");
            var messageBox = $("#loginMessage");
            btn.text("Checking...").prop("disabled", true);

            $.ajax({
                url: "/login-process", type: "POST", data: $(this).serialize(),
                success: function (res) {
                    if (res.status === "success") {
                        messageBox.html('<span style="color:green;">' + res.message + "</span>");
                        setTimeout(() => window.location.href = "/", 1500);
                    } else {
                        messageBox.html('<span style="color:red;">' + res.message + "</span>");
                        btn.text("Login").prop("disabled", false);
                    }
                },
                error: function () { messageBox.html('<span style="color:red;">Server Error</span>'); btn.prop("disabled", false); }
            });
        });

        // Komentar
        $('#commentForm').on('submit', function(e) {
            e.preventDefault(); 
            var form = $(this);
            var btn = form.find('button');
            var originalBtnText = btn.html();
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

            $.ajax({
                url: '/anime/comment', type: 'POST', data: form.serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        if(typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Terkirim!', showConfirmButton: false, timer: 1500 });
                        else alert(response.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        if(typeof Swal !== 'undefined') Swal.fire('Gagal', response.message, 'error');
                        else alert(response.message);
                    }
                },
                error: function() { alert('Terjadi kesalahan koneksi.'); },
                complete: function() { btn.prop('disabled', false).html(originalBtnText); }
            });
        });
    });
})(jQuery);