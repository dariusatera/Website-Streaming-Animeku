<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Str; 
use App\Models\User;
use Carbon\Carbon; 

class AnimeController extends Controller
{
    // ==========================================
    // --- HALAMAN PUBLIC (VIEW) ---
    // ==========================================

    public function index() {
        return view('index');
    }

    public function login() {
         return view('login');
    }

    public function signup() {
        return view('signup');
    }

    public function search(Request $request) {
        return view('index'); 
    }

    // ==========================================
    // --- API DATA ANIME ---
    // ==========================================

    public function getAnimeData() {
        $rawAnime = DB::table('anime')->get();
        
        $formattedAnime = $rawAnime->map(function ($item) {
            return [
                'id'            => $item->id,
                'title'         => $item->title,
                'japan'         => $item->japan_title,
                'image'         => $item->image,
                'genres'        => explode(', ', $item->genres ?? ""), 
                'description'   => $item->description,
                'totalEpisodes' => $item->total_episodes,
                'videoSource'   => $item->video_source,
                'tags'          => explode(', ', $item->tags ?? ""),
                'ep'            => $item->current_ep,
                'view'          => $item->view_count,
                'type'          => $item->type,
                'studio'        => $item->studio,
                'dateAired'     => $item->date_aired,
                'status'        => $item->status,
                'score'         => $item->score,
                'rating'        => $item->rating,
                'duration'      => $item->duration
            ];
        });

        return response()->json($formattedAnime);
    }

    // ==========================================
    // --- HALAMAN DETAIL & WATCHING ---
    // ==========================================

    public function details(Request $request) {
        $id = (int) $request->query('id');

        if (!$id) {
            return redirect('/');
        }

        $anime = DB::table('anime')->where('id', $id)->first();
        if (!$anime) {
            return redirect('/')->with('error', 'Anime tidak ditemukan.');
        }

        $bookmarkedAnimeIds = [];
        if (Auth::check()) {
            $bookmarkedAnimeIds = DB::table('bookmarks')
                ->where('user_id', Auth::id())
                ->pluck('anime_id')
                ->toArray();
        }

        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->where('reviews.anime_id', $id)
            ->select('reviews.*', 'users.name', 'users.avatar')
            ->orderBy('reviews.created_at', 'desc')
            ->get();

        return view('anime-details', [
            'id' => $id,
            'anime' => $anime,
            'userBookmarks' => $bookmarkedAnimeIds,
            'reviews' => $reviews
        ]);
    }

    public function watching(Request $request) {
        $id = (int) $request->query('id');
        $current_ep = (int) $request->query('ep', 1);

        if (!$id) return redirect('/');

        $anime = DB::table('anime')->where('id', $id)->first();
        if (!$anime) return redirect('/')->with('error', 'Anime tidak ditemukan.');

        // --- LOGIKA SATPAM (LOCK EPISODE 4+) ---
        if ($current_ep > 3) {
            if (!Auth::check()) {
                return redirect()->back()->with('error', 'Episode 4 ke atas khusus member! Silakan Login dulu.');
            }
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->subscription_type == 'Free') { 
                return redirect()->back()->with('error', '🔒 Episode terkunci! Upgrade Premium untuk lanjut nonton.');
            }
        }

        // --- AMBIL SEMUA DATA EPISODE ---
        $episodesFromDB = DB::table('episodes')
                            ->where('anime_id', $id)
                            ->get()
                            ->keyBy('episode_number');

        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->where('reviews.anime_id', $id)
            ->select('reviews.*', 'users.name', 'users.avatar')
            ->orderBy('reviews.created_at', 'desc')
            ->get();

        // --- GENERATE DATA VIDEO (STREAMING & DOWNLOAD) ---
        $videoData = $this->getVideoSource($id, $current_ep, $anime);

        return view('anime-watching', [
            'id' => $id,
            'anime' => $anime,
            'reviews' => $reviews,
            'current_ep' => $current_ep,
            'episodesFromDB' => $episodesFromDB,
            'current_video_data' => $videoData 
        ]);
    }

    // ==========================================
    // --- HELPER & API (LOGIKA VIDEO PINTAR) ---
    // ==========================================

    private function getVideoSource($anime_id, $ep, $anime) {
        $video_url = "";
        $is_iframe = false;
        $is_drive  = false; // Penanda khusus Google Drive

        // 1. Cek Tabel Episodes (Prioritas Utama)
        $episodeData = DB::table('episodes')
                        ->where('anime_id', $anime_id)
                        ->where('episode_number', $ep)
                        ->first();
        
        if ($episodeData && !empty($episodeData->video_link)) {
            $video_url = trim($episodeData->video_link);
        }
        // 2. Fallback: Manual Link di Tabel Anime (Legacy)
        elseif (!empty($anime->video_source)) {
             if (strpos($anime->video_source, ',') !== false) {
                 $links = explode(',', $anime->video_source);
                 if (isset($links[$ep - 1])) $video_url = trim($links[$ep - 1]);
             } elseif ($ep == 1) {
                 $video_url = $anime->video_source;
             }
        }
        
        // 3. Fallback Terakhir: Folder Otomatis
        if (empty($video_url)) {
            $folderName = Str::slug($anime->title);
            $ep_str = str_pad($ep, 2, '0', STR_PAD_LEFT);
            
            if (file_exists(public_path("videos/$folderName/ep-$ep_str.mp4"))) {
                $video_url = asset("videos/$folderName/ep-$ep_str.mp4");
            } else {
                $video_url = asset("videos/$anime_id/ep-$ep_str.mp4");
            }
        } else {
            // Jika bukan http (file lokal), bungkus dengan asset()
            if (strpos($video_url, 'http') === false) {
                $video_url = asset($video_url);
            }
        }

        // 4. Deteksi Tipe Link (Iframe / Drive)
        if (strpos($video_url, 'drive.google.com') !== false) {
            $is_iframe = true;
            $is_drive = true;
        } elseif (strpos($video_url, 'dood')!==false || strpos($video_url, 'tape')!==false || strpos($video_url, 'myvidplay')!==false || strpos($video_url, '/e/')!==false) {
            $is_iframe = true;
        }

        // 5. Generate Link Download Khusus
        $download_url = $video_url;
        
        if ($is_iframe) {
            if ($is_drive) {
                // GOOGLE DRIVE: Ubah preview jadi Direct Download Link
                // Pola regex untuk ambil ID File: /d/FILE_ID/
                if (preg_match('/\/d\/(.*?)\//', $video_url, $matches)) {
                    $fileId = $matches[1];
                    $download_url = "https://drive.google.com/uc?export=download&id=" . $fileId;
                } else {
                    // Fallback jika regex gagal
                    $download_url = str_replace('/preview', '/view?usp=sharing', $video_url);
                }
            } elseif (strpos($video_url, '/e/') !== false) {
                // DOODSTREAM / MYVIDPLAY: Ubah /e/ (embed) jadi /d/ (download)
                $download_url = str_replace('/e/', '/d/', $video_url);
            }
        }

        return [
            'video_url' => $video_url,
            'is_iframe' => $is_iframe,
            'is_drive'  => $is_drive, // Kirim status drive ke view/JS
            'download_url' => $download_url
        ];
    }

    public function getEpisodeApi(Request $request) {
        $id = (int) $request->query('id');
        $ep = (int) $request->query('ep');

        if (!$id || !$ep) return response()->json(['status' => 'error', 'message' => 'Invalid Data']);

        $anime = DB::table('anime')->where('id', $id)->first();
        if (!$anime) return response()->json(['status' => 'error', 'message' => 'Anime not found']);

        // Cek Lock / VIP
        if ($ep > 3) {
            if (!Auth::check()) {
                return response()->json(['status' => 'locked', 'message' => 'Login dulu untuk nonton episode ini!']);
            }
            $user = Auth::user();
            if ($user->subscription_type == 'Free') {
                return response()->json(['status' => 'locked', 'message' => 'Episode terkunci! Upgrade ke VIP.']);
            }
        }

        $videoData = $this->getVideoSource($id, $ep, $anime);

        return response()->json([
            'status' => 'success',
            'video_url' => $videoData['video_url'],
            'is_iframe' => $videoData['is_iframe'],
            'is_drive'  => $videoData['is_drive'],
            'download_url' => $videoData['download_url'],
            'episode' => $ep,
            'poster' => $anime->image ? asset($anime->image) : asset('videos/anime-watch.jpg')
        ]);
    }

    // ==========================================
    // --- LOGIKA REGISTER (DAFTAR) ---
    // ==========================================

    public function processRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'name'     => 'required|min:3',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['status' => 'exists', 'message' => 'Silahkan verifikasi akun Anda.']);
        }

        try {
            DB::beginTransaction();

            $randomNumber = rand(1, 6);
            $randomAvatar = 'img/anime/review-' . $randomNumber . '.jpg';
            $rawOtp = rand(100000, 999999);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'user',
                'avatar'   => $randomAvatar,
                'otp_code' => Hash::make($rawOtp), 
                'otp_expires_at' => Carbon::now()->addMinutes(30)
            ]);

            try {
                Mail::raw("Halo $user->name,\n\nKode Verifikasi: $rawOtp", function ($message) use ($user) {
                    $message->to($user->email)->subject('Verifikasi AnimeKu');
                });
            } catch (\Exception $e) { }

            DB::commit();

            return response()->json([
                'status' => 'verify_needed', 
                'email'  => $user->email,
                'message' => 'Registrasi berhasil! Cek email Anda untuk kode verifikasi.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal Mendaftar: ' . $e->getMessage()]);
        }
    }

    public function verifyAccount(Request $request) {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);

        $user = User::where('email', $request->email)->first();

        if (!$user) return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan.']);
        if (!Hash::check($request->otp, $user->otp_code)) return response()->json(['status' => 'error', 'message' => 'Kode Verifikasi Salah!']);
        if ($user->otp_expires_at && Carbon::now()->greaterThan($user->otp_expires_at)) return response()->json(['status' => 'error', 'message' => 'Kode kadaluarsa.']);

        $user->email_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Akun berhasil diverifikasi! Silakan Login.']);
    }

    // ==========================================
    // --- LOGIKA LOGIN ---
    // ==========================================

    public function processLogin(Request $request)
    {
        $request->validate(['login' => 'required', 'password' => 'required']);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $credentials = [$loginType => $request->login, 'password' => $request->password];

        if (Auth::validate($credentials)) {
            $user = User::where($loginType, $request->login)->first();

            if ($user->email_verified_at == null) {
                return response()->json(['status' => 'verify_needed', 'email' => $user->email, 'message' => 'Akun belum aktif.']);
            }

            Auth::login($user);
            $request->session()->regenerate();
            
            $msg = ($user->role == 'admin') ? 'Login Admin Berhasil!' : 'Login Berhasil!';
            return response()->json(['status' => 'success', 'message' => $msg]);
        }

        return response()->json(['status' => 'error', 'message' => 'Username/Email atau Password salah!']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ==========================================
    // --- USER PROFILE ---
    // ==========================================

    public function profile() {
        if (!Auth::check()) return redirect('/login');

        $user = Auth::user();
        try {
            $favorites = DB::table('bookmarks')
                ->join('anime', 'bookmarks.anime_id', '=', 'anime.id')
                ->where('bookmarks.user_id', $user->id)
                ->select('anime.*')
                ->get();
        } catch (\Exception $e) {
            $favorites = [];
        }

        return view('profile', ['user' => $user, 'favorites' => $favorites]);
    }

    public function updateProfile(Request $request) {
        $user = Auth::user(); 
        if (!$user) return redirect('/login')->with('error', 'Sesi habis.');

        $request->validate([
            'name' => 'required|min:3|max:50',
            'password' => 'nullable|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' 
        ]);

        try {
            $user->name = $request->name;
            if ($request->filled('password')) $user->password = Hash::make($request->password);

            if ($request->hasFile('avatar')) {
                if ($user->avatar && strpos($user->avatar, 'uploads/avatars') !== false) {
                    $oldPath = public_path($user->avatar);
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $file = $request->file('avatar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/avatars'), $filename);
                $user->avatar = 'uploads/avatars/' . $filename;
            }

            $user->save();
            return redirect()->back()->with('success', 'Profil berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Gagal Update: ' . $e->getMessage()]);
        }
    }

    public function deleteAvatar() {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        if ($user->avatar && strpos($user->avatar, 'uploads/avatars') !== false) {
            $filePath = public_path($user->avatar);
            if (file_exists($filePath)) @unlink($filePath);
        }

        $randomNumber = rand(1, 6);
        $user->avatar = 'img/anime/review-' . $randomNumber . '.jpg';
        $user->save();

        return redirect()->back()->with('success', 'Foto profil berhasil dihapus dan di-reset!');
    }

    // ==========================================
    // --- BOOKMARK & COMMENT ---
    // ==========================================

    public function toggleBookmark(Request $request) {
        if (!Auth::check()) return response()->json(['status' => 'error', 'message' => 'Silakan Login Terlebih Dahulu!']);

        $userId = Auth::id();
        $animeId = $request->input('anime_id'); 

        if(!$animeId) return response()->json(['status' => 'error', 'message' => 'ID Anime tidak valid']);

        try {
            $exist = DB::table('bookmarks')
                        ->where('user_id', $userId)
                        ->where('anime_id', $animeId)
                        ->first();

            if ($exist) {
                DB::table('bookmarks')->where('id', $exist->id)->delete();
                return response()->json(['status' => 'removed', 'message' => 'Dihapus dari favorit']);
            } else {
                DB::table('bookmarks')->insert(['user_id' => $userId, 'anime_id' => $animeId, 'created_at' => now()]);
                return response()->json(['status' => 'added', 'message' => 'Ditambahkan ke favorit']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Database Error']);
        }
    }

    public function postComment(Request $request) {
        if (!Auth::check()) return response()->json(['status' => 'error', 'message' => 'Harap login.']);

        $validator = Validator::make($request->all(), [
            'anime_id' => 'required|integer',
            'comment' => 'required|string|max:500'
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'message' => 'Komentar tidak valid.']);

        try {
            DB::table('reviews')->insert([
                'user_id' => Auth::id(),
                'anime_id' => $request->anime_id,
                'comment' => $request->comment,
                'created_at' => now()
            ]);

            $user = Auth::user();
            $avatarUrl = $user->avatar ? asset($user->avatar) : asset('img/anime/review-1.jpg');

            return response()->json([
                'status' => 'success', 
                'message' => 'Komentar berhasil dikirim!',
                'data' => ['name' => $user->name, 'avatar' => $avatarUrl, 'comment' => $request->comment, 'date' => 'Baru saja']
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim komentar.']);
        }
    }

    // ==========================================
    // --- FITUR LUPA PASSWORD (OTP) ---
    // ==========================================

    public function sendOtp(Request $request) {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Email tidak terdaftar!']);

        $otp = rand(100000, 999999);
        DB::table('otp_resets')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'expires_at' => now()->addMinutes(15), 'created_at' => now()]
        );

        Mail::raw("Halo $user->name,\n\nKode OTP reset password kamu adalah: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Kode OTP Reset Password - AnimeKu');
        });

        return response()->json(['status' => 'success', 'message' => 'Kode OTP telah dikirim ke email Anda!']);
    }

    public function resetPassword(Request $request) {
        $request->validate(['email' => 'required|email', 'otp' => 'required|numeric', 'password' => 'required|min:6']);
        $resetData = DB::table('otp_resets')->where('email', $request->email)->where('otp', $request->otp)->first();

        if (!$resetData) return response()->json(['status' => 'error', 'message' => 'Kode OTP salah!']);
        if (now()->greaterThan($resetData->expires_at)) return response()->json(['status' => 'error', 'message' => 'Kode OTP kadaluarsa.']);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        DB::table('otp_resets')->where('email', $request->email)->delete();
        return response()->json(['status' => 'success', 'message' => 'Password berhasil diubah!']);
    }

    // ==========================================
    // --- FITUR ADMIN ---
    // ==========================================

    public function admin() {
        if (!Auth::check() || Auth::user()->role !== 'admin') return redirect('/login');
        return view('admin.index', ['anime' => DB::table('anime')->orderBy('id', 'desc')->get()]);
    }

    public function create() {
        if (!Auth::check() || Auth::user()->role !== 'admin') return redirect('/login');
        return view('admin.create');
    }

    // --- STORE: TAMBAH ANIME BARU (DENGAN MULTI EPISODE) ---
    public function store(Request $request) {
        if (!Auth::check() || Auth::user()->role !== 'admin') return redirect('/login');

        $request->validate(['title' => 'required', 'image_file' => 'required|image']);

        try {
            $folderName = Str::slug($request->title);
            $imagePath = null;

            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path("img/anime/$folderName"), $filename); 
                $imagePath = "img/anime/$folderName/" . $filename;
            }

            $videoPath = $request->video_source; 
            if ($request->hasFile('video_file')) {
                $vidFile = $request->file('video_file');
                $vidName = $vidFile->getClientOriginalName(); 
                $vidFile->move(public_path("videos/$folderName"), $vidName); 
                $videoPath = "videos/$folderName/" . $vidName;
            }

            // Insert Anime
            $animeId = DB::table('anime')->insertGetId([
                'title'          => $request->title,
                'japan_title'    => $request->japan_title,
                'image'          => $imagePath,
                'genres'         => $request->genres,
                'description'    => $request->description,
                'total_episodes' => $request->total_episodes ?? 12,
                'current_ep'     => $request->current_ep,
                'studio'         => $request->studio,
                'status'         => $request->status,
                'score'          => $request->score,
                'rating'         => $request->rating,
                'duration'       => $request->duration,
                'date_aired'     => $request->date_aired,
                'video_source'   => $videoPath,
                'tags'           => $request->tags,
                'type'           => 'TV Series',
                'view_count'     => 0
            ]);

            // Insert Multi Episodes
            if ($request->has('episode_links') && $request->has('episode_numbers')) {
                $links = $request->episode_links;
                $nums = $request->episode_numbers;
                foreach ($links as $index => $link) {
                    if (!empty($link)) {
                        DB::table('episodes')->insert([
                            'anime_id' => $animeId,
                            'episode_number' => $nums[$index],
                            'video_link' => $link
                        ]);
                    }
                }
            }

            return redirect('/admin')->with('success', 'Anime berhasil ditambahkan!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // --- EDIT: TAMPILKAN EPISODE ---
    public function edit($id) {
        if (!Auth::check() || Auth::user()->role !== 'admin') return redirect('/login');
        
        $anime = DB::table('anime')->where('id', $id)->first();
        if(!$anime) return redirect()->back()->with('error', 'Anime tidak ditemukan');

        $episodes = DB::table('episodes')->where('anime_id', $id)->orderBy('episode_number', 'asc')->get();

        return view('admin.edit', ['anime' => $anime, 'episodes' => $episodes]);
    }

    // --- UPDATE: EDIT ANIME (MULTI EPISODE) ---
    public function update(Request $request, $id) {
        if (!Auth::check() || Auth::user()->role !== 'admin') return redirect('/login');

        try {
            $folderName = Str::slug($request->title);
            $updateData = $request->except(['image_file', 'episode_links', 'episode_numbers', '_token', '_method', 'video_file', 'video_source']);

            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path("img/anime/$folderName"), $filename);
                $updateData['image'] = "img/anime/$folderName/" . $filename;
            } else {
                $updateData['image'] = $request->image; 
            }

            if ($request->hasFile('video_file')) {
                $vidFile = $request->file('video_file');
                $vidName = $vidFile->getClientOriginalName();
                $vidFile->move(public_path("videos/$folderName"), $vidName);
                $updateData['video_source'] = "videos/$folderName/" . $vidName;
            } elseif ($request->filled('video_source')) {
                $updateData['video_source'] = $request->video_source;
            }

            DB::table('anime')->where('id', $id)->update($updateData);

            // Update Episodes
            if ($request->has('episode_links') && $request->has('episode_numbers')) {
                $links = $request->episode_links;
                $nums = $request->episode_numbers;
                foreach ($links as $index => $link) {
                    if (!empty($link)) {
                        DB::table('episodes')->updateOrInsert(
                            ['anime_id' => $id, 'episode_number' => $nums[$index]],
                            ['video_link' => $link]
                        );
                    }
                }
            }

            return redirect()->back()->with('success', 'Data berhasil diupdate!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function deleteEpisode($id) {
        if (!Auth::check() || Auth::user()->role !== 'admin') return redirect('/login');
        DB::table('episodes')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Episode dihapus!');
    }

    public function delete($id) {
        if (!Auth::check() || Auth::user()->role !== 'admin') return redirect('/login');
        DB::table('anime')->where('id', $id)->delete();
        DB::table('episodes')->where('anime_id', $id)->delete();
        return redirect()->back()->with('success', 'Data dihapus!');
    }
    
    // ==========================================
    // --- FITUR GENRE ---
    // ==========================================

    public function listGenres(Request $request)
    {
        $genre = $request->query('genre'); 
        $query = DB::table('anime');

        if ($genre) {
            $query->where('genres', 'LIKE', '%' . $genre . '%');
        }

        $results = $query->orderBy('id', 'desc')->get();

        return view('genres', [
            'animeList' => $results,
            'genre'  => $genre
        ]);
    }
}