<!DOCTYPE html>
<html>
<head>
    <title>Tambah Anime Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('animekulogo1.png') }}">
</head>
<body class="p-5">
    <div class="container">
        <h2>Tambah Anime Baru</h2>
        
        <form action="/admin/store" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Judul Utama <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="Contoh: One Piece">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Judul Jepang</label>
                    <input type="text" name="japan_title" class="form-control" placeholder="Contoh: ワンピース">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Upload Poster/Gambar <span class="text-danger">*</span></label>
                    <input type="file" name="image_file" class="form-control" accept="image/*" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Studio</label>
                    <input type="text" name="studio" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <label>Total Eps</label>
                    <input type="number" name="total_episodes" class="form-control" value="12">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Airing">Airing</option>
                        <option value="Completed">Completed</option>
                        <option value="Upcoming">Upcoming</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Rating</label>
                    <input type="text" name="rating" class="form-control" placeholder="8.5 / 10">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label>Genres</label>
                    <input type="text" name="genres" class="form-control" placeholder="Action, Adventure, Fantasy">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Tags</label>
                    <input type="text" name="tags" class="form-control" placeholder="New, HD, Trending">
                </div>

                <input type="hidden" name="current_ep" value="1 / 12">
                <input type="hidden" name="score" value="N/A">
                <input type="hidden" name="duration" value="24 min/ep">
                <input type="hidden" name="date_aired" value="{{ date('M d, Y') }}">
            </div>

            <hr>
            
            <div class="mb-3">
                <h4>Tambah Link Episode</h4>
                <p class="text-muted">Masukkan link embed (Doodstream/Drive) di sini.</p>
                
                <div id="episode-container">
                    <div class="input-group mb-2">
                        <span class="input-group-text">Episode 1</span>
                        <input type="hidden" name="episode_numbers[]" value="1">
                        <input type="text" name="episode_links[]" class="form-control" placeholder="Link Episode 1...">
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-info text-white" onclick="addEpisodeField()">+ Tambah Baris Episode</button>
            </div>

            <button type="submit" class="btn btn-primary mt-3 btn-lg">Simpan Anime & Episode</button>
            <a href="/admin" class="btn btn-secondary mt-3 btn-lg">Batal</a>
        </form>
    </div>

    <script>
        let epCount = 1;
        function addEpisodeField() {
            epCount++;
            const container = document.getElementById('episode-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <span class="input-group-text">Episode ${epCount}</span>
                <input type="hidden" name="episode_numbers[]" value="${epCount}">
                <input type="text" name="episode_links[]" class="form-control" placeholder="Link Episode ${epCount}...">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Hapus</button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>