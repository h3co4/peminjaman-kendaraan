        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="public/img/kayaba-logo.png" alt="Logo Kayaba" style="width: 100px;">
            </a>

            <!-- Tombol Toggler untuk perangkat kecil -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Daftar Navigasi -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="dashboard.php" aria-current="page">BERANDA</a>
                    </li>

                    <?php if ($_SESSION['role'] === "karyawan"): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="formpemohon.php">FORM PEMOHON</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] === "ga pool"): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="kelolamobil.php">KELOLA MOBIL</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="datamobil.php">JADWAL MOBIL</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Tombol Logout di bagian kanan -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="#" class="btn btn-danger btn-sm" id="logoutBtn">KELUAR</a>
                    </li>
                </ul>
            </div>
        </div>