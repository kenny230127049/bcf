<?php
require_once 'config/database.php';

$db = getDB();
$kategori_lomba = $db->fetchAll("SELECT * FROM b_kategori_lomba WHERE status = 'aktif' ORDER BY nama");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Lomba - BlueCreativeFestival</title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem 0;
            margin-bottom: 3rem;
        }
        .kategori-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .kategori-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        .kategori-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .kategori-card.selected .text-primary {
            color: white !important;
        }
        .kategori-card.selected .text-muted {
            color: rgba(255,255,255,0.8) !important;
        }
        .btn-daftar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-daftar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .badge-custom {
            font-size: 0.8rem;
            padding: 8px 15px;
            border-radius: 20px;
        }
        .icon-large {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Hero Section -->
        <div class="hero-section text-center text-white">
            <div class="container">
                <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInDown">
                    <i class="fas fa-trophy me-3"></i>BlueCreativeFestival
                </h1>
                <p class="lead mb-4 animate__animated animate__fadeInUp">
                    Pilih lomba yang ingin Anda ikuti dan tunjukkan kreativitas Anda!
                </p>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="d-flex justify-content-center gap-3">
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="fas fa-users me-2"></i>Lomba Kelompok
                            </span>
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="fas fa-user me-2"></i>Lomba Individu
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kategori Lomba -->
        <div class="row">
            <?php foreach ($kategori_lomba as $kategori): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="kategori-card h-100" onclick="selectKategori(<?= $kategori['id'] ?>, '<?= htmlspecialchars($kategori['nama']) ?>', '<?= $kategori['jenis_lomba'] ?? 'individu' ?>', <?= $kategori['max_peserta'] ?? 1 ?>)">
                    <div class="text-center">
                        <i class="<?= htmlspecialchars($kategori['icon'] ?? 'fas fa-trophy') ?> icon-large text-primary"></i>
                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($kategori['nama']) ?></h5>
                        <p class="text-muted mb-3"><?= htmlspecialchars(substr($kategori['deskripsi'], 0, 100)) ?>...</p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge bg-<?= ($kategori['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'success' : 'info' ?> badge-custom">
                                <i class="fas fa-<?= ($kategori['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'users' : 'user' ?> me-1"></i>
                                <?= ucfirst($kategori['jenis_lomba'] ?? 'individu') ?>
                            </span>
                            <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                            <span class="badge bg-secondary badge-custom">
                                Max: <?= $kategori['max_peserta'] ?? 1 ?> orang
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="h5 text-primary fw-bold mb-3">
                            Rp <?= number_format($kategori['biaya'], 0, ',', '.') ?>
                        </div>
                        
                        <button class="btn btn-daftar w-100">
                            <i class="fas fa-arrow-right me-2"></i>Pilih Lomba Ini
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tombol Daftar -->
        <div class="text-center mt-5" id="daftarSection" style="display: none;">
            <div class="card bg-white p-4 shadow">
                <h4 class="mb-3">Lomba yang Dipilih</h4>
                <div id="selectedKategoriInfo" class="mb-4"></div>
                <button class="btn btn-daftar btn-lg" onclick="daftarLomba()">
                    <i class="fas fa-edit me-2"></i>Daftar Sekarang
                </button>
            </div>
        </div>

        <!-- Kembali ke Beranda -->
        <div class="text-center mt-4">
            <a href="index.php" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedKategori = null;

        function selectKategori(id, nama, jenis, maxPeserta) {
            // Remove previous selection
            document.querySelectorAll('.kategori-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            event.currentTarget.classList.add('selected');
            
            selectedKategori = {
                id: id,
                nama: nama,
                jenis: jenis,
                maxPeserta: maxPeserta
            };
            
            // Show daftar section
            document.getElementById('daftarSection').style.display = 'block';
            
            // Update info
            const info = document.getElementById('selectedKategoriInfo');
            info.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nama Lomba:</strong><br>
                        ${nama}
                    </div>
                    <div class="col-md-6">
                        <strong>Jenis:</strong><br>
                        <span class="badge bg-${jenis === 'kelompok' ? 'success' : 'info'}">
                            ${jenis.charAt(0).toUpperCase() + jenis.slice(1)}
                        </span>
                        ${jenis === 'kelompok' ? `<br><small>Maksimal ${maxPeserta} orang</small>` : ''}
                    </div>
                </div>
            `;
            
            // Scroll to daftar section
            document.getElementById('daftarSection').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        function daftarLomba() {
            if (!selectedKategori) {
                alert('Silakan pilih lomba terlebih dahulu!');
                return;
            }
            
            // Redirect to registration page with kategori info
            window.location.href = `daftar.php?kategori_id=${selectedKategori.id}&jenis=${selectedKategori.jenis}&max_peserta=${selectedKategori.maxPeserta}`;
        }
    </script>
</body>
</html>
