@extends('collector.percobaan.layout')

@section('content')

@php
    $photoUrl = function ($path) {
        if (!$path) return null;
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }
        return asset('storage/'.$path);
    };
@endphp

@php
    $photo = $user->profile_photo_path ?? null;
@endphp

<style id="profile-upload-view-v2">
.profile-page{
    display:flex;
    flex-direction:column;
    gap:16px;
    padding:16px;
}
.profile-hero,
.profile-card{
    border-radius:28px;
    border:1px solid rgba(232,201,122,.28);
    background:
        radial-gradient(circle at 15% 18%, rgba(255,219,120,.10), rgba(255,219,120,0) 28%),
        linear-gradient(180deg, rgba(10,22,55,.94), rgba(5,13,34,.98));
    box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.03),
        0 12px 30px rgba(0,0,0,.16);
    padding:18px;
    color:#f7f2dc;
}
.profile-hero h1{
    margin:0;
    font-size:28px;
    line-height:1.1;
    font-weight:900;
}
.profile-hero p{
    margin:8px 0 0;
    color:#d4d8e8;
    font-size:14px;
    line-height:1.5;
}
.profile-layout{
    display:grid;
    grid-template-columns:140px 1fr;
    gap:16px;
    align-items:start;
}
.profile-photo-shell{
    display:flex;
    flex-direction:column;
    gap:12px;
    align-items:center;
}
.profile-photo-box{
    width:140px;
    height:140px;
    border-radius:28px;
    overflow:hidden;
    border:1px solid rgba(232,201,122,.35);
    background:
        radial-gradient(circle at 30% 20%, rgba(255,220,120,.13), rgba(255,220,120,0) 38%),
        linear-gradient(180deg, rgba(9,20,52,.82), rgba(4,12,34,.94));
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.04),
        0 10px 26px rgba(0,0,0,.18);
}
.profile-photo-box img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}
.profile-photo-fallback{
    width:82px;
    height:82px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(180deg,#f3dd9b,#caa84b);
    color:#2e2305;
    box-shadow:
        inset 0 2px 10px rgba(255,255,255,.35),
        0 10px 22px rgba(0,0,0,.22);
}
.profile-photo-fallback svg{
    width:38px;
    height:38px;
}
.profile-photo-note{
    font-size:12px;
    color:#c7cfdf;
    text-align:center;
    line-height:1.45;
}
.form-grid{
    display:grid;
    gap:14px;
}
.form-group{
    display:grid;
    gap:8px;
}
.form-group label{
    font-weight:800;
    color:#f1e3ad;
    font-size:14px;
}
.form-input,
.form-file{
    width:100%;
    min-height:54px;
    border-radius:18px;
    border:1px solid rgba(232,201,122,.22);
    background:rgba(5,17,42,.78);
    color:#fff;
    padding:0 16px;
    outline:none;
    font-size:16px;
}
.form-file{
    padding:14px 16px;
    min-height:auto;
}
.form-input::placeholder{
    color:#93a1bd;
}
.form-checkbox{
    display:flex;
    align-items:center;
    gap:10px;
    color:#d8dfef;
    font-size:14px;
    font-weight:700;
}
.form-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:6px;
}
.save-btn,
.back-btn{
    min-height:54px;
    border:none;
    border-radius:18px;
    padding:0 24px;
    font-weight:900;
    font-size:17px;
    cursor:pointer;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}
.save-btn{
    background:linear-gradient(180deg,#f3dd9b,#caa84b);
    color:#1e1604;
    box-shadow:0 10px 22px rgba(202,168,75,.24);
}
.back-btn{
    background:rgba(255,255,255,.06);
    color:#f3f4f9;
    border:1px solid rgba(255,255,255,.10);
}
.small-muted{
    margin-top:6px;
    font-size:12px;
    color:#99a6c2;
    line-height:1.5;
}
@media(max-width:700px){
    .profile-layout{
        grid-template-columns:1fr;
    }
    .profile-photo-shell{
        align-items:center;
    }
}
</style>

<div class="profile-page">
    <section class="profile-hero">
        <h1>Profile Kasir</h1>
        <p>Upload foto profile di sini. Foto ini akan tampil di icon kanan atas dan di kotak profile pada dashboard collector.</p>
    </section>

    <section class="profile-card">
        <form id="profileForm" method="POST" action="{{ url('/kasir/profile') }}" enctype="multipart/form-data">
            @csrf

            <div class="profile-layout">
                <div class="profile-photo-shell">
                    <div class="profile-photo-box" id="photoPreviewBox">
                        @if($photo)
                            <img src="{{ $photoUrl($photo) }}" alt="Foto Profile" id="photoPreviewImage">
                        @else
                            <div class="profile-photo-fallback" id="photoFallback">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                                    <circle cx="12" cy="8" r="4"></circle>
                                </svg>
                            </div>
                            <img src="" alt="Preview Foto" id="photoPreviewImage" style="display:none;">
                        @endif
                    </div>
                    <div class="profile-photo-note">
                        JPG / PNG / WEBP<br>Maksimal 2 MB
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama</label>
                        <input class="form-input" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required>
                    </div>

                    @if(isset($user->email))
                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-input" type="email" name="email" value="{{ old('email', $user->email ?? '') }}">
                    </div>
                    @endif

                    <div class="form-group">
                        <label>Upload Foto Profile</label>
                        <input class="form-file" type="file" name="profile_photo" id="profilePhotoInput" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <div class="small-muted">Jika diupload, foto baru otomatis menggantikan foto lama.</div>
                    </div>

                    @if(!empty($user->profile_photo_path))
                    <label class="form-checkbox">
                        <input type="checkbox" name="remove_profile_photo" value="1">
                        Hapus foto profile sekarang
                    </label>
                    @endif

                    <div class="form-group">
                        <label>Password Saat Ini</label>
                        <input class="form-input" type="password" name="current_password" placeholder="Isi hanya jika ingin ganti password">
                    </div>

                    <div class="form-group">
                        <label>Password Baru</label>
                        <input class="form-input" type="password" name="new_password" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input class="form-input" type="password" name="new_password_confirmation" placeholder="Ulangi password baru">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="save-btn">Simpan Profile</button>
                        <a href="{{ url('/kasir') }}" class="back-btn">Kembali</a>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>

<script id="profile-photo-preview-v2">
(function(){
    const input = document.getElementById('profilePhotoInput');
    const img = document.getElementById('photoPreviewImage');
    const fallback = document.getElementById('photoFallback');

    if(!input || !img) return;

    input.addEventListener('change', function(){
        const file = this.files && this.files[0] ? this.files[0] : null;
        if(!file) return;

        const reader = new FileReader();
        reader.onload = function(e){
            img.src = e.target.result;
            img.style.display = 'block';
            if(fallback) fallback.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
})();

</script>

<script id="profile-submit-loading-v1">
(function(){
    const form = document.getElementById('profileForm');
    if(!form) return;

    form.addEventListener('submit', function(){
        const btn = form.querySelector('button[type="submit"]');
        if(btn){
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';
        }
    });
})();
</script>
@endsection

