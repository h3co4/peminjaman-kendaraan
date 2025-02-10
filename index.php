<?php
// Mulai sesi
session_start();
date_default_timezone_set('Asia/Jakarta');
include 'config.php';

if (isset($_POST['login'])) {
    $npk = $_POST['npk'];
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];

    // Periksa CAPTCHA
    if ($captcha === $_SESSION['captcha']) {
        // Mempersiapkan statement untuk mencari NPK di database
        $stmt = $conn->prepare("SELECT * FROM ct_users_hash WHERE npk = ?");

        // Periksa jika query gagal disiapkan
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("s", $npk);

        // Eksekusi statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            // Menggunakan password_verify untuk memeriksa password yang di-hash
            if (password_verify($password, $row['pwd'])) {
                // Menghasilkan OTP acak
                $otp = rand(100000, 999999);
                $expiry_date = date('Y-m-d H:i:s', strtotime('+5 minutes')); // Menentukan masa berlaku OTP

                $sql = "SELECT no_hp FROM hp WHERE npk = ?";
                $stmtNoHp = $connIsd->prepare($sql);
                $stmtNoHp->bind_param("s", $npk);
                $stmtNoHp->execute();
                $resultNoHp = $stmtNoHp->get_result();

                $phone_number = "";
                $show_otp_modal = false; // Default modal tidak ditampilkan

                if ($resultNoHp->num_rows > 0) {
                    // Ambil nomor telepon
                    $rowNoHp = $resultNoHp->fetch_assoc();
                    $phone_number = $rowNoHp['no_hp'];

                    // Mengganti semua digit di antara 4 digit pertama dan 4 digit terakhir dengan *
                    if (strlen($phone_number) > 8) {
                        $masked_phone_number = substr($phone_number, 0, 4) . '****' . substr($phone_number, -4);
                    } else {
                        $masked_phone_number = $phone_number; // Jika panjang nomor telepon kurang dari atau sama dengan 8
                    }

                    // Cek apakah data OTP sudah ada
                    $stmt_check = $connKendaraan->prepare("SELECT * FROM otp_authentication WHERE npk = ? AND phone_number = ?");
                    if (!$stmt_check) {
                        die("Error preparing statement: " . $connKendaraan->error);
                    }

                    // Bind parameter untuk npk dan phone_number
                    $stmt_check->bind_param("ss", $npk, $phone_number);
                    $stmt_check->execute();
                    $result = $stmt_check->get_result();

                    // Jika data sudah ada, lakukan UPDATE
                    if (
                        $result->num_rows > 0
                    ) {
                        // Mengambil data OTP yang ada
                        $stmt_update = $connKendaraan->prepare("UPDATE otp_authentication SET otp = ?, expiry_date = ?, `send` = 2, `use` = 2 , `use_date` = NULL WHERE npk = ? AND phone_number = ?");
                        if (!$stmt_update) {
                            die("Error preparing statement: " . $connKendaraan->error);
                        }

                        // Bind parameter untuk update OTP (pastikan jumlah parameter sesuai)
                        $stmt_update->bind_param(
                            "ssss",
                            $otp,
                            $expiry_date,
                            $npk,
                            $phone_number
                        );
                        $stmt_update->execute();

                        // Menutup stmt_update setelah digunakan
                        $stmt_update->close();
                    } else {
                        // Jika data belum ada, lakukan INSERT
                        $stmt_otp = $connKendaraan->prepare("INSERT INTO otp_authentication (npk, phone_number, otp, expiry_date, `send`, `use`) VALUES (?, ?, ?, ?, 2, 2)");
                        if (!$stmt_otp) {
                            die("Error preparing statement: " . $connKendaraan->error);
                        }

                        // Bind parameter untuk INSERT (pastikan jumlah parameter sesuai)
                        $stmt_otp->bind_param("ssss", $npk, $phone_number, $otp, $expiry_date);
                        $stmt_otp->execute();

                        // Menutup stmt_otp setelah digunakan
                        $stmt_otp->close();
                    }

                    // Menutup stmt_check setelah digunakan
                    $stmt_check->close();

                    $show_otp_modal = true; // Menetapkan agar modal ditampilkan jika nomor telepon ditemukan
                } else {
                    echo "<div class='alert alert-danger'>Gagal mengirim OTP karena tidak dapat menemukan nomor hp</div>";
                    // Modal tidak akan ditampilkan karena $show_otp_modal tetap false
                }

                $stmtNoHp->close();
            } else {
                echo "<div class='alert alert-danger text-center col-4 mx-auto'><strong>Npk dan Password anda tidak sesuai.</strong></div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center col-4 mx-auto'><strong>NPK tidak ditemukan.</strong></div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger text-center col-4 mx-auto'><strong>Captcha anda tidak sesuai.</strong></div>";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GA DEPARTEMENT</title>
    <link href="public/img/kayaba-logo.png" rel="icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="public/bootstrap/css/bootstrap.min.css">

    <link rel="stylesheet" href="public/library/bootstrap-icons/bootstrap-icons.css">

    <!-- Bootstrap-select CSS -->
    <link rel="stylesheet" href="public/library/bootstrap-select/dist/css/bootstrap-select.min.css">

    <!-- jQuery UI CSS (optional) -->
    <link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">

    <!-- Link CSS untuk SweetAlert2 -->
    <link rel="stylesheet" href="public/library/sweetalert2/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="public/library/sweetalert2/dist/sweetalert2.min.js">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


    <!-- JS SweetAlert2 -->
    <script src="public/library/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/bootstrap/css/bootstrap.min.css">
</head>

<body>
    <div class="card col-sm-4 mx-auto mt-5">
        <div class="card mb-0" style="width: 100%;">
            <div class="d-flex justify-content-center mt-5">
                <img src="public/img/kayaba-logo.png" class="card-img-top w-50 " alt="logo kayaba">
            </div>
            <div class="card-body">
                <form action="" method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="npk" class="form-label">NPK</label>
                        <input type="text" name="npk" id="npk" class="form-control" placeholder="Masukkan NPK" autofocus required autocomplete="off" maxlength="6">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required maxlength="50">
                            <span class="input-group-text">
                                <i class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer;"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <img src="captcha.php" alt="Captcha Code" id="captchaImage" width="100">
                            <input type="text" class="form-control ms-2" id="captcha" name="captcha" placeholder="Masukkan kode captcha" maxlength="6" required inputmode="numeric">
                        </div>
                        <small class=" form-text text-muted">Captcha tidak terbaca? Refresh <strong><a href="javascript:void(0);" onclick="refreshCaptcha()">di sini</a></strong></small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="login" name="login" value="login" class="btn btn-danger">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otpModalLabel">Masukkan OTP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="otpForm">
                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP</label>
                            <h6 class="font-italic fw-light text-muted mb-3">Masukkan 6 digit OTP yang telah dikirim melalui WhatsApp User: <?php echo htmlspecialchars($masked_phone_number); ?></h6>
                            </h6>
                            <div id="otp-input" class="d-flex justify-content-between">
                                <input type="text" maxlength="1" class="form-control otp-input text-center" required>
                                <input type="text" maxlength="1" class="form-control otp-input text-center" required>
                                <input type="text" maxlength="1" class="form-control otp-input text-center" required>
                                <input type="text" maxlength="1" class="form-control otp-input text-center" required>
                                <input type="text" maxlength="1" class="form-control otp-input text-center" required>
                                <input type="text" maxlength="1" class="form-control otp-input text-center" required>
                            </div>
                        </div>
                        <input type="hidden" name="npk" id="npk-otp" value="<?php echo $npk; ?>">
                        <input type="hidden" name="otp" id="otp" value="">

                        <!-- Countdown Timer -->
                        <div id="countdown" class="text-danger text-end mb-3">
                            Sisa waktu: <span id="timer">300 detik</span>
                        </div>

                        <!-- OTP Verification Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="verifyOtpBtn">Verifikasi OTP</button>
                        </div>

                        <!-- Resend OTP Message -->
                        <div class="text-center mt-3">
                            <small id="resendOtpMessage" class="d-none d-flex justify-content-center align-items-center">
                                <h6 class="text-secondary mb-0 me-2">Waktu Habis:</h6>
                                <a href="javascript:void(0);" onclick="refreshOtp(<?php echo $npk; ?>)" class="btn btn-link text-danger p-0">
                                    Kirim OTP Baru
                                </a>
                            </small>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .otp-input {
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1.25rem;
            margin-right: 0.5rem;
            border-radius: 0.25rem;
        }

        .otp-input:last-child {
            margin-right: 0;
        }
    </style>

    <script src="public/library/jquery/dist/jquery.min.js"></script>
    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('captcha').addEventListener('input', function(event) {
            // Hanya izinkan angka
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#otpForm").on("submit", function(e) {
                e.preventDefault();

                var npk = $("#npk-otp").val();
                var otp = $("#otp").val();

                console.log(npk);

                $.ajax({
                    url: "verify_otp.php",
                    type: "POST",
                    data: {
                        npk: npk,
                        otp: otp
                    },
                    success: function(response) {
                        var data = JSON.parse(response);

                        // Menampilkan pesan menggunakan SweetAlert2
                        if (data.status === "success") {
                            // Menampilkan pesan sukses dengan redirect setelah 2 detik
                            // Swal.fire({
                            //     icon: 'success',
                            //     title: data.message,
                            //     showConfirmButton: false,
                            //     timer: 2000
                            // }).then(function() {
                            window.location.href = data.redirect_url;
                            // });

                        } else {
                            // Menampilkan pesan error jika OTP tidak valid
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function() {
                        // Menampilkan pesan error jika request gagal
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan!',
                            text: 'Silakan coba lagi.',
                            showConfirmButton: true
                        });
                    }
                });
            });
        });
    </script>

    <script>
        function moveFocus(currentInput, nextInputIndex) {
            // If current input has 1 character, move focus to the next input
            if (currentInput.value.length == 1) {
                const nextInput = document.getElementsByClassName('otp-input')[nextInputIndex];
                if (nextInput) {
                    nextInput.focus();
                }
            }
        }
    </script>

    <script>
        function refreshOtp(npk) {
            Swal.fire({
                title: 'Kirim OTP Baru?',
                text: "Apakah Anda ingin mengirim OTP baru?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'resend_otp.php', // Ganti dengan endpoint PHP yang sesuai
                        type: 'POST',
                        data: {
                            npk: npk
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'OTP Dikirim!',
                                    text: 'OTP baru telah dikirim ke nomor terdaftar Anda.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                $('#resendOtpMessage').addClass('d-none'); // Sembunyikan pesan waktu habis
                                startTimer(); // Mulai ulang timer
                            } else {
                                Swal.fire({
                                    title: 'Gagal Mengirim OTP',
                                    text: response.message || 'Terjadi kesalahan saat mengirim OTP.',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Tidak dapat terhubung ke server.',
                                icon: 'error'
                            });

                            console.error("Status: " + status);
                            console.error("Error: " + error);
                            console.error("Response: " + xhr.responseText);
                        }
                    });
                }
            });
        }

        function startTimer() {
            let timer = 300; // Waktu hitungan mundur dalam detik
            $('#timer').text(timer + ' detik');
            $('#resendOtpMessage').addClass('d-none'); // Sembunyikan pesan timeout saat timer dimulai

            const countdown = setInterval(function() {
                timer--;
                $('#timer').text(timer + ' detik');

                if (timer <= 0) {
                    clearInterval(countdown);
                    $('#resendOtpMessage').removeClass('d-none'); // Tampilkan pesan waktu habis
                }
            }, 1000);
        }

        // Mulai hitungan mundur saat halaman dimuat
        $(document).ready(function() {
            startTimer();
        });
    </script>

    <script>
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpHiddenInput = document.getElementById('otp');
        const countdown = document.getElementById('countdown');
        const resendOtpBtn = document.getElementById('resendOtpBtn');
        let timeLeft = 10; // Set sesuai kebutuhan

        // Fokus otomatis pindah ke kotak berikutnya saat diisi
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                otpHiddenInput.value = Array.from(otpInputs).map(i => i.value).join('');
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === "Backspace" && input.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });


        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function(e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        function refreshCaptcha() {
            document.getElementById('captchaImage').src = 'captcha.php?' + Math.random();
        }

        <?php if (isset($show_otp_modal) && $show_otp_modal): ?>
            $(document).ready(function() {
                $('#otpModal').modal('show');
            });
        <?php endif; ?>
    </script>

    <div class="text-center mt-4">
        <p>&copy; 2024 KayabaIndonesia. All rights reserved.</p>
    </div>

</body>

</html>

<?php
?>