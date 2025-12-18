<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('animekulogo1.png') }}">
</head>
<body class="p-5">
    <div class="container">
        <h1>Dashboard Admin Anime</h1>
        
        <div class="d-flex justify-content-between mb-3">
            <a href="/" class="btn btn-secondary">Kembali ke Web Utama</a>
            
            <a href="/admin/create" class="btn btn-success">
                + Tambah Anime Baru
            </a>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Episode</th>
                    <th>Studio</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($anime as $a)
                <tr>
                    <td>{{ $a->id }}</td>
                    <td>{{ $a->title }}</td>
                    <td>{{ $a->current_ep }}</td>
                    <td>{{ $a->studio }}</td>
                    <td>
                        <a href="/admin/edit/{{ $a->id }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="/admin/delete/{{ $a->id }}" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>