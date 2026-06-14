#!/bin/bash
# Script safe deploy tampilan daftar pelanggan

# Backup semua file Blade yang ada
mkdir -p backup_blade
cp *.blade.php backup_blade/

# Temukan file Blade yang ada kata customer/pelanggan
blade_file=$(find . -type f -name "*customer*.blade.php" -o -name "*pelanggan*.blade.php" | head -n 1)

if [ -z "$blade_file" ]; then
  echo "File Blade daftar pelanggan tidak ditemukan!"
  exit 1
fi

echo "Mengedit file Blade: $blade_file"

# Hapus section deskripsi di atas tabel (div alert-info)
sed -i '/<div class="alert-info">/,/<\/div>/d' "$blade_file"

# Ambil tombol Kembali dan hapus dari tempat lama
sed -i '/<button.*Kembali.*<\/button>/{
    h
    d
}' "$blade_file"

# Sisipkan tombol Kembali di header setelah tombol Keluar
sed -i '/<button.*Keluar.*<\/button>/a <button class="btn btn-light">Kembali</button>' "$blade_file"

echo "Tampilan daftar pelanggan berhasil diperbarui. Tombol Kembali sudah dipindahkan ke header."
