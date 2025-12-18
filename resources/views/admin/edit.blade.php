<!DOCTYPE html>
<html>
<head>
    <title>Edit Anime - {{ $anime->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('animekulogo1.png') }}">
</head>
<body class="p-5">
    <div class="container">
        <div class="d-flex justify-content-between mb-4">
            <h2>Edit Anime: {{ $anime->title }}</h2>
            <a href="/admin" class="btn btn-secondary">Kembali</a>
        </div>
        
        <form action="/admin/update/{{ $anime->id }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- METHOD PUT DIHAPUS AGAR TIDAK ERROR --}}

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Judul Utama</label>
                    <input type="text" name="title" class="form-control" value="{{ $anime->title }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Judul Jepang</label>
                    <input type="text" name="japan_title" class="form-control" value="{{ $anime->japan_title }}">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ $anime->description }}</textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Ganti Poster</label>
                    <div class="d-flex align-items-center gap-3">
                        @if($anime->image)
                            <img src="{{ asset($anime->image) }}" style="height: 50px; border-radius: 5px;">
                        @endif
                        <input type="file" name="image_file" class="form-control" accept="image/*">
                    </div>
                    <input type="hidden" name="image" value="{{ $anime->image }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Studio</label>
                    <input type="text" name="studio" class="form-control" value="{{ $anime->studio }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label>Total Eps</label>
                    <input type="number" name="total_episodes" class="form-control" value="{{ $anime->total_episodes }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Airing" {{ $anime->status == 'Airing' ? 'selected' : '' }}>Airing</option>
                        <option value="Completed" {{ $anime->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Upcoming" {{ $anime->status == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Rating</label>
                    <input type="text" name="rating" class="form-control" value="{{ $anime->rating }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Genres</label>
                    <input type="text" name="genres" class="form-control" value="{{ $anime->genres }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Tags</label>
                    <input type="text" name="tags" class="form-control" value="{{ $anime->tags }}">
                </div>
                
                <input type="hidden" name="score" value="{{ $anime->score }}">
                <input type="hidden" name="duration" value="{{ $anime->duration }}">
                <input type="hidden" name="date_aired" value="{{ $anime->date_aired }}">
                <input type="hidden" name="current_ep" value="{{ $anime->current_ep }}">
            </div>

            <hr class="my-4">

            <div class="card p-3 bg-light">
                <h4>Manajemen Episode</h4>
                
                <table class="table table-sm table-bordered bg-white">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Episode</th>
                            <th>Link Video (Drive / File)</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($episodes as $ep)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $ep->episode_number }}</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="{{ $ep->video_link }}" readonly>
                            </td>
                            <td class="text-center">
                                <a href="/admin/delete-episode/{{ $ep->id }}" class="btn btn-danger btn-sm" onclick="return confirm('Hapus episode ini?')">Hapus</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <h5 class="mt-3">Tambah Episode Baru</h5>
                <div id="new-episode-container">
                    </div>
                <button type="button" class="btn btn-success btn-sm mt-2" onclick="addEpisodeRow()">+ Tambah Baris Input</button>
            </div>

            <button type="submit" class="btn btn-warning mt-4 btn-lg">Update Data Anime & Simpan Episode</button>
            <a href="/admin" class="btn btn-secondary mt-4 btn-lg">Batal</a>
        </form>
    </div>

    <script>
        // Logika Javascript untuk menghitung episode selanjutnya
        let lastEp = {{ $episodes->max('episode_number') ?? 0 }};
        
        function addEpisodeRow() {
            lastEp++;
            const container = document.getElementById('new-episode-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <span class="input-group-text bg-success text-white">Episode ${lastEp}</span>
                <input type="hidden" name="episode_numbers[]" value="${lastEp}">
                <input type="text" name="episode_links[]" class="form-control" placeholder="Paste Link Baru disini...">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Batal</button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>